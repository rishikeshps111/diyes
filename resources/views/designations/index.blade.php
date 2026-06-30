@extends('layouts.app')

@section('title', 'Designations')

@section('content')
  <div class="page-title">
    <h3>Designations</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item active">Designations</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-lg-12 mb-3">
        <div class="collapse" id="filterCollapse">
          <div class="main-table-container">
            <div class="row">
              <div class="col-lg-3 mb-3">
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
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="designation_name_filter">Designation</label>
                  <input type="text" id="designation_name_filter" class="form-control shadow-none"
                    placeholder="Search by designation">
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="grade_filter">Grade</label>
                  <select id="grade_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($grades as $grade)
                      <option value="{{ $grade->id }}">
                        {{ $grade->grade }}{{ $grade->academicYear ? ' - '.$grade->academicYear->academic_year : '' }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="status_filter">Status</label>
                  <select id="status_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="filter-btns-top ">
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
              @can('create.designation')
                <a href="{{ route('designations.create') }}" class="add-btn">Add New</a>
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
                    <select id="designationPerPage" class="form-select shadow-none">
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
                    <label for="designationTableSearch" class="nowrap">Search</label>
                    <input type="text" id="designationTableSearch" class="form-control shadow-none"
                      placeholder="Search...">
                    <form id="designationExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('designations.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('designations.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="designationsTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>
                        <input type="checkbox" id="selectAllDesignations">
                      </th>
                      <th>SL No</th>
                      <th>Code</th>
                      <th>Designation</th>
                      <th>Department</th>
                      <th>Grade</th>
                      <th>Status</th>
                      <th>Actions</th>
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
  @include('designations.partials.js')
@endpush
