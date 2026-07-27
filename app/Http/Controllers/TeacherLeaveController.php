<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\TeacherLeaveBalance;
use App\Services\TeacherService;
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

class TeacherLeaveController extends Controller 
{
    
    public function index()
{
    $leaves = LeaveApplication::with('leaveType')
        ->where('teacher_id',auth()->id())
        ->latest()
        ->paginate(10);

    return view('teachers.leave.index',compact('leaves'));
}

public function create()
{
    $leaveTypes = LeaveType::where('status',1)->get();

    return view('teachers.leave.create',compact('leaveTypes'));
}

public function store(Request $request)
{
    $request->validate([
        'leave_type_id'=>'required',
        'from_date'=>'required|date',
        'to_date'=>'required|date|after_or_equal:from_date',
        'reason'=>'required'
    ]);

    $days = Carbon::parse($request->from_date)
        ->diffInDays($request->to_date)+1;

    $balance = TeacherLeaveBalance::where(
        'teacher_id',
        auth()->id()
    )
    ->where('leave_type_id',$request->leave_type_id)
    ->first();

    if(!$balance){
        return back()->with('error','Leave balance not found.');
    }

    $leaveType = LeaveType::find($request->leave_type_id);

    if(!$leaveType->is_lop &&
        $days > $balance->remaining_days){

        return back()->with(
            'error',
            'Only '.$balance->remaining_days.' leave(s) remaining.'
        );
    }

    LeaveApplication::create([

        'teacher_id'=>auth()->id(),

        'leave_type_id'=>$request->leave_type_id,

        'from_date'=>$request->from_date,

        'to_date'=>$request->to_date,

        'days'=>$days,

        'reason'=>$request->reason,

        'status'=>'Pending'

    ]);

    return redirect()
        ->route('teacher.leave.index')
        ->with('success','Leave applied successfully.');
}

public function cancel(LeaveApplication $leave)
{
    if($leave->status!='Pending')
    {
        return back()->with('error',
            'Only pending leave can be cancelled.');
    }

    $leave->update([
        'status'=>'Cancelled'
    ]);

    return back()->with(
        'success',
        'Leave cancelled.'
    );
}

public function getLeaveBalance($leaveType)
{
    $balance = TeacherLeaveBalance::where('teacher_id', auth()->id())
        ->where('leave_type_id', $leaveType)
        ->first();

    return response()->json([
        'remaining_days' => $balance?->remaining_days ?? 0,
    ]);
}
    
    
}