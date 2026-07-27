@extends('layouts.app')

@section('content')

<div class="card">

<div class="card-header">

Leave Details

</div>

<div class="card-body">

<table class="table">

<tr>

<th>Teacher</th>

<td>

{{ $leave->teacher->name ?? '-' }}

</td>

</tr>

<tr>

<th>Leave Type</th>

<td>

{{ $leave->leaveType->leave_name ?? '-' }}

</td>

</tr>

<tr>

<th>From Date</th>

<td>

{{ $leave->from_date }}

</td>

</tr>

<tr>

<th>To Date</th>

<td>

{{ $leave->to_date }}

</td>

</tr>

<tr>

<th>Total Days</th>

<td>

{{ $leave->days }}

</td>

</tr>

<tr>

<th>Reason</th>

<td>

{{ $leave->reason }}

</td>

</tr>

<tr>

<th>Status</th>

<td>

{{ $leave->status }}

</td>

</tr>

</table>

@if($leave->status=='Pending')

<div class="row">

<div class="col-md-6">

<form
method="POST"
action="{{ route('admin.leave-applications.approve',$leave->id) }}">

@csrf

<textarea
name="remarks"
class="form-control"
placeholder="Remarks"></textarea>

<button
class="btn btn-success mt-2">

Approve

</button>

</form>

</div>

<div class="col-md-6">

<form
method="POST"
action="{{ route('admin.leave-applications.reject',$leave->id) }}">

@csrf

<textarea
name="remarks"
class="form-control"
placeholder="Reason"></textarea>

<button
class="btn btn-danger mt-2">

Reject

</button>

</form>

</div>

</div>

@endif

</div>

</div>

@endsection