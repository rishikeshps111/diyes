<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Models\LeaveType;
use Spatie\Permission\Models\Role;
use App\Models\Designation;
use App\Models\ModulePrefix;
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

class LeaveTypeController extends Controller 
{
    
     public function index()
    {
        $leaveTypes = LeaveType::latest()->paginate(10);

        return view('leave-types.index', compact('leaveTypes'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
         $prefix = ModulePrefix::where('module', 'leave_type')->first();

        $lastLeave = LeaveType::latest('id')->first();
    
        $nextNumber = $lastLeave ? $lastLeave->id + 1 : 1;
    
        $code = $prefix->prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $roles=Role::where('name', '!=', 'admin')->get();
        $designation=Designation::where('is_active',1)->get();
        return view('leave-types.create',compact('code','roles','designation'));
    }

    /**
     * Store leave type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_name' => 'required|max:100|unique:leave_types,leave_name',
            'code'        => 'required|max:20|unique:leave_types,code',
            'total_days'  => 'required|integer|min:0',
            'is_lop'      => 'required|boolean',
            'status'      => 'required|boolean',
            'role_id' => 'required',
            'desgnation_id' =>'required',
        ]);

        LeaveType::create($request->all());

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave Type Created Successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(LeaveType $leaveType)
    {
        $roles=Role::where('name', '!=', 'admin')->get();
        $designation=Designation::where('is_active',1)->get();
        return view('leave-types.edit', compact('leaveType','roles','designation'));
    }

    /**
     * Update leave type.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'leave_name' => 'required|max:100|unique:leave_types,leave_name,' . $leaveType->id,
            'code'        => 'required|max:20|unique:leave_types,code,' . $leaveType->id,
            'total_days'  => 'required|integer|min:0',
            'is_lop'      => 'required|boolean',
            'status'      => 'required|boolean',
            'role_id' => 'required',
            'desgnation_id' =>'required',
        ]);

        $leaveType->update($request->all());

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave Type Updated Successfully.');
    }

    /**
     * Delete leave type.
     */
    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();

        return redirect()
            ->route('leave-types.index')
            ->with('success', 'Leave Type Deleted Successfully.');
    }
    
    
    
}