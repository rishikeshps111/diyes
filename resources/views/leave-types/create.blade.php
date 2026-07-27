@extends('layouts.app')

@section('content')

<div class="card">

<div class="card-header">

Add Leave Type

</div>

<div class="card-body">

<form action="{{ route('leave-types.store') }}" method="POST">

@csrf

<div class="mb-3">

<label>Leave Name</label>

<input type="text"
       name="leave_name"
       class="form-control"
       required>

</div>

<div class="mb-3">

<label>Code</label>

<input type="text"
       name="code"
       class="form-control" value="{{ $code ?? '' }}" readonly >

</div>

<div class="mb-3">

<label>Total Days in Year</label>

<input type="number"
       name="total_days"
       class="form-control">

</div>

<div class="mb-3">

<label>Role</label>

<select name="role"
        class="form-control">

<option >Select</option>
@foreach($roles as $role)
<option value="{{$role->id}}">{{$role->name}}</option>
@endforeach

</select>

</div>

<div class="mb-3">

<label>Designation</label>

<select name="role"
        class="form-control">

<option >Select</option>
@foreach($designation as $desi)
<option value="{{$desi->id}}">{{$desi->designation_name}}</option>
@endforeach

</select>

</div>

<div class="mb-3">

<label>LOP</label>

<select name="is_lop"
        class="form-control">

<option value="1">Yes</option>
<option value="0">No</option>


</select>

</div>

<div class="mb-3">

<label>Status</label>

<select name="status"
        class="form-control">

<option value="1">Active</option>

<option value="0">Inactive</option>

</select>

</div>

<button class="btn btn-success">

Save

</button>

</form>

</div>

</div>

@endsection