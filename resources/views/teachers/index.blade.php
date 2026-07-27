@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
  <div class="page-title">
    <h3>Teachers</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Teacher Management</li>
        <li class="breadcrumb-item active">Teachers</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-lg-12 mb-3">
        <div class="collapse" id="filterCollapse">
          <div class="main-table-container">
            <div class="row">
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="department_filter">Department</label>
                  <select id="department_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($departments as $department)
                      <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              {{-- <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="designation_filter">Designation</label>
                  <select id="designation_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($designations as $designation)
                      <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div> --}}
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="status_filter">Status</label>
                  <select id="status_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($statuses as $status)
                      <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="gender_filter">Gender</label>
                  <select id="gender_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($genders as $gender)
                      <option value="{{ $gender }}">{{ $gender }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="date_of_joining_filter">Date of Joining</label>
                  <input type="date" id="date_of_joining_filter" class="form-control shadow-none">
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="qualification_filter">Qualification</label>
                  <input type="text" id="qualification_filter" class="form-control shadow-none"
                    placeholder="Search by qualification">
                </div>
              </div>
              <div class="col-lg-12">
                <div class="filter-btns-top">
                  <button type="button" id="resetFilters" class="reset-btn border-0" data-loading-text="Resetting...">Reset</button>
                  <button type="button" id="applyFilters" class="search-btn" data-loading-text="Searching...">Search</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        <div class="row">
          <div class="col-lg-12">
            <div class="btn-flex">
              <a class="add-btn bg-filter" data-bs-toggle="collapse" href="#filterCollapse" role="button"
                aria-expanded="false" aria-controls="filterCollapse">Filters</a>
              @can('create.teacher')
                <a href="{{ route('teachers.create') }}" class="add-btn">Add New</a>
              @endcan
              @can('delete.teacher')
                <button type="button" id="bulkDeleteTeachers" class="add-btn border-0"
                  data-delete-url="{{ route('teachers.bulk-delete') }}" data-loading-text="Deleting..."
                  style="background-color: #dc3545; border-color: #dc3545;">Bulk Delete</button>
              @endcan
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
                  <div class="table-search">
                    <label for="teacherTableSearch" class="nowrap">Search</label>
                    <input type="text" id="teacherTableSearch" class="form-control shadow-none"
                      placeholder="Name, employee code, email or phone">
                    <form id="teacherExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('teachers.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('teachers.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="teachersTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAllTeachers"></th>
                      <th>SL No</th>
                      <th>Employee Code</th>
                      <th>Name</th>
                      <th>Department</th>
                      {{-- <th>Designation</th> --}}
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Date of Joining</th>
                      <th>Status</th>
                      <th>Verification Status</th>
                      <th>Action</th>
                      <th class="d-none">Created At</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection

@push('scripts')
  @include('teachers.partials.js')
@endpush
