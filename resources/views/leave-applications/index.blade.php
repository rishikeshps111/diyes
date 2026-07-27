@extends('layouts.app')

@section('title', 'Leave Applications')

@section('content')

 <div class="page-title">
    <h3>Leave Applications</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Teacher Management</li>
        <li class="breadcrumb-item active">Leave Applications</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
                <div class="col-lg-12 mb-3">

                    <div class="collapse" id="filterCollapse">


                        <div class="main-table-container">
                            <form method="GET" action="{{ route('leave-applications.index') }}">
                            <div class="row">

                                <div class="col-lg-4 mb-3">
                                    <div class="o-f-inp">
                                        <label>Leave Type </label>
                                        <select name="leave_type_id" id="" class="form-select shadow-none">
                                            <option value="">--- Select ---</option>
                                            @foreach($leaveTypes as $leave)
                                                <option value="{{$leave->id}}" {{ request('leave_type_id') == $leave->id ? 'selected' : '' }}>{{$leave->leave_name}}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <div class="o-f-inp">
                                        <label>Teacher </label>
                                        <select name="teacher_id" id="" class="form-select shadow-none">
                                            <option value="">--- Select ---</option>
                                            @foreach($teachers as $teacher)
                                            <option value="{{$teacher->id}}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{$teacher->name}}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <div class="o-f-inp">
                                            <label>From Date</label>
                                            <input type="date"
                                                   name="from_date"
                                                   value="{{ request('from_date') }}"
                                                   class="form-control shadow-none">
                                        </div>
                                    </div>
                            
                                    <div class="col-lg-4 mb-3">
                                        <div class="o-f-inp">
                                            <label>To Date</label>
                                            <input type="date"
                                                   name="to_date"
                                                   value="{{ request('to_date') }}"
                                                   class="form-control shadow-none">
                                        </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <div class="o-f-inp">
                                        <label for="">Status</label>
                                        <select name="status" id="status" class="form-select shadow-none">
                                            <option value="">--- Select ---</option>
                                            <option value="Pending" {{ request('status')=='Pending'?'selected':'' }}>Pending</option>
                                            <option value="Approved" {{ request('status')=='Approved'?'selected':'' }}>Approved</option>
                                            <option value="Rejected" {{ request('status')=='Rejected'?'selected':'' }}>Rejected</option>

                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="filter-btns-top ">
                                        <a href="{{ route('leave-applications.index') }}" class="reset-btn">Reset</a>
                                        <button type="submit" class="search-btn"> Search</button>


                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        <div class="row">
          <div class="col-lg-12">
            <div class="btn-flex">
                <a class="add-btn bg-filter" data-bs-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="false" aria-controls="collapseExample">Filters</a>
                <a href="{{route('leave-applications.create')}}" class="add-btn">Add New</a>
            </div>
          </div>
        </div>
        
        <div class="row">
            
            
          <div class="col-lg-12">
            <div class="mt-3 table-container">
              <div class="row justify-content-end">
                <div class="col-lg-5">
                  <div class="entry-select">
                    <p>Showing</p>
                    <select id="teacherPerPage" class="form-select shadow-none">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                    </select>
                    <p>Entries</p>
                  </div>
                </div>
                <div class="col-lg-7">
                  <!--<div class="table-search">-->
                  <!--  <label for="teacherTableSearch" class="nowrap">Search</label>-->
                  <!--  <input type="text" id="teacherTableSearch" class="form-control shadow-none"-->
                  <!--    placeholder="Name, employee code, email or phone">-->
                  <!--  <form id="teacherExportForm" method="POST" class="d-inline-flex flex-shrink-0">-->
                  <!--    @csrf-->
                  <!--    <button type="button" class="exp-btn" data-loading-text="Exporting..."-->
                  <!--      data-export-url="{{ route('teachers.export.excel') }}">Export Excel</button>-->
                  <!--    <button type="button" class="exp-btn" data-loading-text="Exporting..."-->
                  <!--      data-export-url="{{ route('teachers.export.pdf') }}">Export PDF</button>-->
                  <!--  </form>-->
                  <!--</div>-->
                </div>
              </div>

              <div class="table-over">
                <table id="leaveTypeTable" class="align-middle mb-0 table table-custom mt-3">
                  <thead>
                    <tr>
                       <th>#</th>
                       <th>Application No</th>
                       <th>Applied Date</th>
                        <th>Teacher</th>

                        <th>Leave Type</th>
                        
                        <th>From</th>
                        
                        <th>To</th>
                        
                        <th>Days</th>
                        
                        <th>Status</th>
                        
                        <th>Action</th>

                    </tr>
                  </thead>
                  <tbody>
                    @forelse($leaves as $leave)

                    <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$leave->application_no}}</td>
                    <td>{{$leave->applied_date}}</td>
                    <td>
                    
                    {{ $leave->teacher->name ?? '' }}
                    
                    </td>
                    
                    <td>
                    
                    {{ $leave->leaveType->leave_name ?? '' }}
                    
                    </td>
                    
                    <td>
                    
                    {{ $leave->from_date }}
                    
                    </td>
                    
                    <td>
                    
                    {{ $leave->to_date }}
                    
                    </td>
                    
                    <td>
                    
                    {{ $leave->days }}
                    
                    </td>
                    
                    <td>
                    
                    {{ $leave->status }}
                    
                    </td>
                    
                    <td>
                         <div class="action-btns">
                            <a href="{{route('leave-applications.edit',$leave->id)}}" class="btn-edit"> <i
                                    class="fa-solid fa-pen-to-square"></i></a>
                            <div class="dropdown">
                                <button class="dropdown-toggle tgle-cs-btns" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dromenu-cs">
                                    <li><a class="dropdown-item" href="{{ route('leave-applications.show',$leave->id) }}">View </a></li>
                                     @if($leave->status == 'Pending')
                                        <li>
                                            <form id="approve-form-{{ $leave->id }}"
                                                  action="{{ route('leave-applications.approve', $leave->id) }}"
                                                  method="POST">
                                                @csrf
                                                <button type="button"
                                                        class="dropdown-item text-success"
                                                        onclick="approveLeave({{ $leave->id }})">
                                                    Approve
                                                </button>
                                            </form>
                                        </li>
                                        
                                        <li>
                                            <form id="reject-form-{{ $leave->id }}"
                                                  action="{{ route('leave-applications.reject', $leave->id) }}"
                                                  method="POST">
                                                @csrf
                                                <button type="button"
                                                        class="dropdown-item text-danger"
                                                        onclick="rejectLeave({{ $leave->id }})">
                                                    Reject
                                                </button>
                                            </form>
                                        </li>
                                    @else
                                
                                        <li>
                                            <span class="dropdown-item text-muted">
                                                {{ $leave->status }}
                                            </span>
                                        </li>
                                
                                    @endif



                                </ul>
                            </div>



                            <a href="#!" class=" btn-delete"> <i
                                    class="fa-solid fa-trash"></i></a>

                        </div>
                    
                    
                    </td>
                    
                    </tr>
                    @empty

                    <tr>

                        <td colspan="9" class="text-center">

                            No Leave Applications Found

                        </td>

                    </tr>

                    @endforelse
                  </tbody>
                </table>
                {{ $leaves->links() }}
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </section>
        

@endsection
@push('scripts')
<script>
function approveLeave(id) {
    Swal.fire({
        title: 'Approve Leave?',
        text: 'Are you sure you want to approve this leave application?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Yes, Approve'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('approve-form-' + id).submit();
        }
    });
}

function rejectLeave(id) {
    Swal.fire({
        title: 'Reject Leave?',
        text: 'Are you sure you want to reject this leave application?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Reject'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('reject-form-' + id).submit();
        }
    });
}
</script>
@endpush