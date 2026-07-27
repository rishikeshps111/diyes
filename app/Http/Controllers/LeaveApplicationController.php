<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\TeacherLeaveBalance;
use App\Models\Teacher;
use App\Models\ModulePrefix;
use App\Services\TeacherService;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveApplicationController extends Controller 
{
public function index(Request $request)
{
    $query = LeaveApplication::with(['teacher', 'leaveType']);

    if ($request->filled('teacher_id')) {
        $query->where('teacher_id', $request->teacher_id);
    }

    if ($request->filled('leave_type_id')) {
        $query->where('leave_type_id', $request->leave_type_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from_date')) {
        $query->whereDate('from_date', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('to_date', '<=', $request->to_date);
    }

    $leaves = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();

    $teachers = Teacher::where('status', 1)->get();
    $leaveTypes = LeaveType::where('status', 1)->get();

    return view('leave-applications.index', compact(
        'leaves',
        'teachers',
        'leaveTypes'
    ));
}

public function create()
{
    $teachers = Teacher::where('status', 1)->get();
    $leaveTypes = LeaveType::where('status', 1)->get();
    $prefix = ModulePrefix::where('module', 'leave_application')->first();

    $lastLeave = LeaveApplication::latest('id')->first();

    $nextNumber = $lastLeave ? $lastLeave->id + 1 : 1;

    $applicationNo = $prefix->prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    $appliedDate = Carbon::today()->format('Y-m-d');

    return view('leave-applications.create', compact('teachers', 'leaveTypes','applicationNo','appliedDate'));
}

public function store(Request $request)
{
    $request->validate([

        'teacher_id'=>'required|exists:teachers,id',

        'leave_type_id'=>'required|exists:leave_types,id',

        'from_date'=>'required|date',

        'to_date'=>'required|date|after_or_equal:from_date',

        'reason'=>'required'

    ]);

    DB::beginTransaction();

    try{

        $days = Carbon::parse($request->from_date)
            ->diffInDays(
                Carbon::parse($request->to_date)
            ) + 1;
            
        $prefix = ModulePrefix::where('module', 'leave_application')->first();

        $lastLeave = LeaveApplication::latest('id')->first();
        
        $nextNumber = $lastLeave ? $lastLeave->id + 1 : 1;
        
        $applicationNo = $prefix->prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $leave = LeaveApplication::create([
            
            'application_no' => $applicationNo,

            'applied_date' => now()->toDateString(),

            'teacher_id'=>$request->teacher_id,

            'leave_type_id'=>$request->leave_type_id,

            'from_date'=>$request->from_date,

            'to_date'=>$request->to_date,

            'days'=>$days,

            'reason'=>$request->reason,

            'status'=>$request->status,

            'approved_by'=>$request->status=='Approved'
                ? auth()->id()
                : null,

            'approved_at'=>$request->status=='Approved'
                ? now()
                : null,

        ]);

        if($request->status=='Approved')
        {
            $balance = TeacherLeaveBalance::where(
                'teacher_id',
                $request->teacher_id
            )
            ->where(
                'leave_type_id',
                $request->leave_type_id
            )
            ->first();

            if($balance)
            {
                $balance->used_days += $days;

                $balance->remaining_days -= $days;

                $balance->save();
            }
        }

        ActivityLogService::log(
            'Leave',
            'Create',
            $leave->id,
            'Leave created by Admin'
        );

        DB::commit();

        return redirect()
            ->route('leave-applications.index')
            ->with(
                'success',
                'Leave application added successfully.'
            );

    }catch(\Exception $e){

        DB::rollBack();

        return back()
            ->withInput()
            ->with(
                'error',
                $e->getMessage()
            );
    }
}

public function edit($id)
{
    $app=LeaveApplication::findorFail($id);
    $teachers = Teacher::where('status', 1)->get();
    $leaveTypes = LeaveType::where('status', 1)->get();
   
    return view('leave-applications.edit', compact('teachers', 'leaveTypes','app'));
}

public function update(Request $request, LeaveApplication $app)
{
    $request->validate([
        'teacher_id'     => 'required|exists:teachers,id',
        'leave_type_id'  => 'required|exists:leave_types,id',
        'from_date'      => 'required|date',
        'to_date'        => 'required|date|after_or_equal:from_date',
        'reason'         => 'required|string|max:500',
        'status'         => 'required|in:Pending,Approved,Rejected',
    ]);

    DB::beginTransaction();

    try {

        $days = Carbon::parse($request->from_date)
            ->diffInDays(Carbon::parse($request->to_date)) + 1;

        // If status changed from Pending/Rejected to Approved
        if ($app->status != 'Approved' && $request->status == 'Approved') {

            $balance = TeacherLeaveBalance::where('teacher_id', $request->teacher_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->first();

            if ($balance) {

                if ($balance->remaining_days < $days) {

                    return back()
                        ->withInput()
                        ->with('error', 'Insufficient leave balance.');
                }

                $balance->used_days += $days;
                $balance->remaining_days -= $days;
                $balance->save();
            }
        }

        $app->update([

            'teacher_id'     => $request->teacher_id,

            'leave_type_id'  => $request->leave_type_id,

            'from_date'      => $request->from_date,

            'to_date'        => $request->to_date,

            'days'           => $days,

            'reason'         => $request->reason,

            'status'         => $request->status,

            'approved_by'    => $request->status == 'Approved'
                                    ? auth()->id()
                                    : null,

            'approved_at'    => $request->status == 'Approved'
                                    ? now()
                                    : null,

        ]);

       ActivityLogService::log(
            'Leave',
            'Update',
            $app->id,
            'Leave application updated.'
        );

        DB::commit();

        return redirect()
            ->route('leave-applications.index')
            ->with('success', 'Leave application updated successfully.');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

public function show(LeaveApplication $leave)
{
    $leave->load([
        'teacher',
        'leaveType'
    ]);

    return view(
        'leave-applications.show',
        compact('leave')
    );
}

public function approve(Request $request, LeaveApplication $leave)
{
    if($leave->status!='Pending')
    {
        return back()->with(
            'error',
            'Already processed.'
        );
    }

    DB::transaction(function () use ($leave,$request){

        $balance = TeacherLeaveBalance::where(
            'teacher_id',
            $leave->teacher_id
        )
        ->where(
            'leave_type_id',
            $leave->leave_type_id
        )
        ->lockForUpdate()
        ->first();

        $leaveType = LeaveType::find(
            $leave->leave_type_id
        );

        if(!$leaveType->is_lop){

            $balance->used_days += $leave->days;

            $balance->remaining_days -= $leave->days;

            $balance->save();
        }

        $leave->update([

            'status'=>'Approved',

            'approved_by'=>auth()->id(),

            'approved_at'=>now(),

            'remarks'=>$request->remarks

        ]);

    });

    return back()->with(
        'success',
        'Leave Approved Successfully.'
    );
}

public function reject(Request $request, LeaveApplication $leave)
{
    if($leave->status!='Pending')
    {
        return back()->with(
            'error',
            'Already processed.'
        );
    }

    $leave->update([

        'status'=>'Rejected',

        'approved_by'=>auth()->id(),

        'approved_at'=>now(),

        'remarks'=>$request->remarks

    ]);

    return back()->with(
        'success',
        'Leave Rejected Successfully.'
    );
}

  
}