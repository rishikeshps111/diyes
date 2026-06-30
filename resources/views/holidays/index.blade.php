@extends('layouts.app')

@section('title', 'Holidays')

@section('content')
  <div class="page-title">
    <h3>Holidays</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item active">Holidays</li>
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
                  <label for="branch_filter">Branch</label>
                  <input type="text" id="branch_filter" class="form-control shadow-none"
                    placeholder="Search by branch">
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="academic_year_filter">Academic Year</label>
                  <select id="academic_year_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($academicYears as $academicYear)
                      <option value="{{ $academicYear->id }}">{{ $academicYear->academic_year }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="holiday_type_filter">Holiday Type</label>
                  <select id="holiday_type_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($holidayTypes as $holidayType)
                      <option value="{{ $holidayType }}">{{ $holidayType }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="month_filter">Month</label>
                  <select id="month_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($months as $monthValue => $monthName)
                      <option value="{{ $monthValue }}">{{ $monthName }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="date_from_filter">Date From</label>
                  <input type="date" id="date_from_filter" class="form-control shadow-none">
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="date_to_filter">Date To</label>
                  <input type="date" id="date_to_filter" class="form-control shadow-none">
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
              @can('create.holiday')
                <a href="{{ route('holidays.create') }}" class="add-btn">Add New</a>
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
                    <select id="holidayPerPage" class="form-select shadow-none">
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
                    <label for="holidayTableSearch" class="nowrap">Search</label>
                    <input type="text" id="holidayTableSearch" class="form-control shadow-none"
                      placeholder="Search...">
                    <form id="holidayExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('holidays.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('holidays.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="holidaysTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>
                        <input type="checkbox" id="selectAllHolidays">
                      </th>
                      <th>SL No</th>
                      <th>Code</th>
                      <th>Holiday</th>
                      <th>Holiday Type</th>
                      <th>Date</th>
                      <th>Branch</th>
                      <th>Applicable Classes</th>
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
  @include('holidays.partials.js')
@endpush
