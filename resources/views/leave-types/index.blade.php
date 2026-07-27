@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
  <div class="page-title">
    <h3>Leave Types</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Teacher Management</li>
        <li class="breadcrumb-item active">Leave Types</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    

    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        <div class="row">
          <div class="col-lg-12">
            <div class="btn-flex">
                <a href="{{ route('leave-types.create') }}" class="add-btn">Add New</a>
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
                <table id="leaveTypeTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>SL No</th>
                      <th>Leave Name</th>
                    <!--<th>Code</th>-->
                    <th>Total Days</th>
                    <th>LOP</th>
                    <th>Status</th>
                    <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                      @foreach($leaveTypes as $leave)

            <tr>

                <td>{{ $loop->iteration }}</td>

                <td>{{ $leave->leave_name }}</td>

                <!--<td>{{ $leave->code }}</td>-->

                <td>{{ $leave->total_days }}</td>

                <td>

                    {{ $leave->is_lop ? 'Yes' : 'No' }}

                </td>

                <td>

                    {{ $leave->status ? 'Active' : 'Inactive' }}

                </td>

                <td>

                    <a href="{{ route('leave-types.edit',$leave->id) }}"
                       class="btn btn-warning btn-sm">

                        <i class="fa-solid fa-pen-to-square"></i>

                    </a>

                    <form action="{{ route('leave-types.destroy',$leave->id) }}"
                          method="POST"
                          style="display:inline">

                        @csrf

                        @method('DELETE')

                        <button class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete?')">

                            <i class="fa-solid fa-trash"></i>

                        </button>

                    </form>

                </td>

            </tr>

            @endforeach
                  </tbody>
                </table>
                {{ $leaveTypes->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection

@push('scripts')
  @include('leave-types.partials.js')
@endpush
