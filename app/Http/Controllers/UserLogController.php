<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Models\UserLog;
use App\Models\ActivityLog;
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

class UserLogController extends Controller 
{
  
  public function index(Request $request)
{
    $query = UserLog::latest();

    if($request->filled('user'))
    {
        $query->where('user_name','like','%'.$request->user.'%');
    }

    if($request->filled('module'))
    {
        $query->where('module',$request->module);
    }

    if($request->filled('action'))
    {
        $query->where('action',$request->action);
    }

    if($request->filled('from_date'))
    {
        $query->whereDate(
            'created_at',
            '>=',
            $request->from_date
        );
    }

    if($request->filled('to_date'))
    {
        $query->whereDate(
            'created_at',
            '<=',
            $request->to_date
        );
    }

    $logs = $query->paginate(20);

    return view(
        'user-logs.index',
        compact('logs')
    );
}  

public function show(UserLog $userLog)
{
    return view(
        'user-logs.show',
        compact('userLog')
    );
}

public function destroy(UserLog $userLog)
{
    $userLog->delete();

    return back()->with(
        'success',
        'Log deleted successfully.'
    );
}


 public function activityLog(Request $request)
    {
        $query = ActivityLog::latest();

        if($request->filled('user_id')){
            $query->where('user_id',$request->user_id);
        }

        if($request->filled('module')){
            $query->where('module',$request->module);
        }

        if($request->filled('action')){
            $query->where('action',$request->action);
        }

        if($request->filled('from_date')){
            $query->whereDate('created_at','>=',$request->from_date);
        }

        if($request->filled('to_date')){
            $query->whereDate('created_at','<=',$request->to_date);
        }

        $logs = $query->paginate(20);

        return view('user-logs.activity-logs',compact('logs'));
    }

    public function activityShow( $id)
    {
        $activityLog=ActivityLog::findorFail($id);
        return view('user-logs.activity-show',compact('activityLog'));
    }
    
    
}