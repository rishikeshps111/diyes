@extends('layouts.app')

@section('title','Apply Leave')

@section('content')

<div class="container-fluid">

<div class="card shadow">

<div class="card-header">

<h4>

Apply Leave

</h4>

</div>

<div class="card-body">

<form action="{{ route('teacher.leave.store') }}" method="POST">

@csrf

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Leave Type

</label>

<select name="leave_type_id"
        id="leave_type"
        class="form-control"
        required>

<option value="">Select Leave</option>

@foreach($leaveTypes as $leave)

<option value="{{ $leave->id }}">

{{ $leave->leave_name }}

</option>

@endforeach

</select>

@error('leave_type_id')

<div class="text-danger">

{{ $message }}

</div>

@enderror

</div>

<div class="col-md-6 mb-3">

<label>

Remaining Balance

</label>

<input type="text"
       id="remaining"
       class="form-control"
       readonly>

</div>

<div class="col-md-6 mb-3">

<label>

From Date

</label>

<input type="date"
       name="from_date"
       id="from_date"
       class="form-control"
       required>

</div>

<div class="col-md-6 mb-3">

<label>

To Date

</label>

<input type="date"
       name="to_date"
       id="to_date"
       class="form-control"
       required>

</div>

<div class="col-md-6 mb-3">

<label>

Total Days

</label>

<input type="text"
       id="days"
       class="form-control"
       readonly>

</div>

<div class="col-md-12 mb-3">

<label>

Reason

</label>

<textarea
name="reason"
rows="5"
class="form-control"
required></textarea>

</div>

</div>

<div class="text-end">

<a href="{{ route('teacher.leave.index') }}"
class="btn btn-secondary">

Back

</a>

<button class="btn btn-primary">

Submit Leave

</button>

</div>

</form>

</div>

</div>

</div>

@endsection

@section('scripts')

<script>

$('#from_date,#to_date').change(function(){

let from=new Date($('#from_date').val());

let to=new Date($('#to_date').val());

if($('#from_date').val()!='' && $('#to_date').val()!='')
{
let diff=(to-from)/(1000*60*60*24)+1;

$('#days').val(diff);
}

});

$('#leave_type').change(function(){

var id=$(this).val();

$.ajax({

url:"{{ url('teacher/get-leave-balance') }}/"+id,

success:function(res){

$('#remaining').val(res.remaining_days);

}

});

});

</script>

@endsection