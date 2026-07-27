@extends('layouts.app')

@section('content')

 <div class="page-title">
            <h3>Manage Leave</h3>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.html">Home</a></li>
                    <li class="breadcrumb-item ">Leave Management</li>
                    <li class="breadcrumb-item active">Manage Leave</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard ">
            <div class="row">

                <div class="col-xl-12 mb-3">
                    <div class="main-table-container mb-3">
                        <!-- <h5 class="title-w-sec">Personal Information</h5>
                        <hr> -->
                        <form action="{{ route('leave-applications.update',$app->id) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">Application No <span class="text-danger">*</span></label>
                                    <input type="text" name="application_no" class="form-control shadow-none" value="{{ $app->application_no }}" readonly>
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">Applied Date <span class="text-danger">*</span></label>
                                    <input type="date" name="applied_date"  value="{{ $app->applied_date }}" class="form-control shadow-none" readonly>
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label>Teacher </label>
                                    <select name="teacher_id" id="" class="form-select shadow-none">
                                        <option value="">--- Select ---</option>
                                        @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" @selected(old('teacher_id', $app->teacher_id) === $teacher->id)>{{ $teacher->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">Leave Type <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" class="form-select shadow-none" >
                                        <option value="">Select Leave Type</option>
                                        @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" @selected(old('leave_type_id', $app->leave_type_id) === $type->id )>{{ $type->leave_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">From Date <span class="text-danger">*</span></label>
                                    <input type="date" name="from_date" id="from_date" value="{{$app->from_date}}" class="form-control shadow-none">
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">To Date <span class="text-danger">*</span></label>
                                    <input type="date" name="to_date" id="to_date" value="{{$app->to_date}}" class="form-control shadow-none">
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">Total Days <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control shadow-none" value="{{$app->days}}" id="days">
                                </div>
                                <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">Reason<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control shadow-none" name="reason" value="{{$app->reason}}">
                                </div>
                                
                                   <div class="col-lg-4 o-f-inp mb-3">
                                    <label for="">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select shadow-none">
                                        <option value="">---Select---</option>
                                        <option value="Pending"  @selected(old('status', $app->status) === 'Pending' )>Pending </option>
                                        <option value="Approved" @selected(old('status', $app->status) === 'Approved' )>Approved </option>
                                        <option value="Rejected" @selected(old('status', $app->status) === 'Rejected' )>Rejected </option>

                                    </select>
                                </div>
                                
                                
                                <div class="col-lg-12 d-flex justify-content-center align-items-center">
                                    <div class="btn-flex">
                                        <input type="submit" class="submit-btn" value="Submit">
            
                                    </div>
                                </div>
                                
                            </div>

                        </form>

                    </div>
                    

                </div>

            </div>
        </section>


@endsection

@push('scripts')

<script>

$('#from_date,#to_date').change(function(){

let from=new Date($('#from_date').val());

let to=new Date($('#to_date').val());

if($('#from_date').val()!=''
&& $('#to_date').val()!='')
{
let days=(to-from)/(1000*60*60*24)+1;

$('#days').val(days);
}

});

</script>

@endpush