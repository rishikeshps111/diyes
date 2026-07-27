@extends('layouts.app')

@section('content')

<div class="card">

<div class="card-header">

Edit Leave Type

</div>

<div class="card-body">

<form action="{{ route('leave-types.update', $leaveType->id) }}" method="POST">

@csrf

<div class="mb-3">

<label>Leave Name</label>

<input type="text" name="leave_name" class="form-control" value="{{ old('leave_name', $leaveType->leave_name) }}">

</div>

<div class="mb-3">

<label>Code</label>

<input type="text"
       name="code"
       class="form-control"
       value="{{ old('code', $leaveType->code) }}">

</div>

<div class="mb-3">

<label>Total Days in a Year</label>

<input type="number"
       name="total_days"
       class="form-control" value="{{ old('total_days', $leaveType->total_days) }}">

</div>

<div class="mb-3">

<label>LOP</label>

<select name="is_lop"
        class="form-control">

<option value="0" @selected(old('is_lop', $leaveType->is_lop) == '0')>No</option>

<option value="1" @selected(old('is_lop', $leaveType->is_lop) == '1')>Yes</option>

</select>

</div>

<div class="mb-3">

<label>Role</label>

<select name="role_id"
        class="form-control">

<option >Select</option>
@foreach($roles as $role)
<option value="{{$role->id}}" @selected(old('role_id', $leaveType->role_id) == $role->id )>{{$role->name}}</option>
@endforeach

</select>

</div>

<div class="mb-3">

<label>Designation</label>

<select name="designation_id"
        class="form-control">

<option >Select</option>
@foreach($designation as $desi)
<option value="{{$desi->id}}" @selected(old('designation_id', $leaveType->designation_id) == $desi->id )>{{$desi->designation_name}}</option>
@endforeach

</select>

</div>

<div class="mb-3">

<label>Status</label>

<select name="status"
        class="form-control">

<option value="1" @selected(old('status', $leaveType->status) == '1')>Active</option>

<option value="0" @selected(old('status', $leaveType->status) == '0')>Inactive</option>

</select>

</div>

<button class="btn btn-success">

Save

</button>

</form>

</div>

</div>

@endsection