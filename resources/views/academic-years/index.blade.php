@extends('layouts.app')

@section('title', 'Academic Years')

@section('content')
  <div class="page-title">
    <h3>Academic Years</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Academic Management</li>
        <li class="breadcrumb-item active">Academic Year</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-lg-12 mb-3">
        <div class="collapse" id="filterCollapse">
          <div class="main-table-container">
            <div class="row">
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="academic_year_filter">Academic Year</label>
                  <input type="text" id="academic_year_filter" class="form-control shadow-none"
                    placeholder="Search by academic year">
                </div>
              </div>
              <div class="col-lg-4 mb-3">
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
              @can('create.academic-year')
                <a href="{{ route('academic-years.create') }}" class="add-btn">Add New</a>
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
                    <select id="academicYearPerPage" class="form-select shadow-none">
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
                    <label for="academicYearTableSearch" class="nowrap">Search</label>
                    <input type="text" id="academicYearTableSearch" class="form-control shadow-none"
                      placeholder="Search...">
                    <form id="academicYearExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('academic-years.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('academic-years.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="academicYearsTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>
                        <input type="checkbox" id="selectAllAcademicYears">
                      </th>
                      <th>SL No</th>
                      <th>Code</th>
                      <th>Academic Year</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Status</th>
                      <th>Actions</th>
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
  @include('academic-years.partials.js')
@endpush
