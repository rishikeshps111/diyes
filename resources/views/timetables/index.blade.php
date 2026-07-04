@extends('layouts.app')

@section('title', 'Regular Timetable')

@section('content')
  <div class="page-title">
    <h3>Regular Timetable</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item active">Regular Timetable</li>
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
                  <label for="academic_year_filter">Academic Year</label>
                  <select id="academic_year_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($academicYears as $academicYear)
                      <option value="{{ $academicYear->id }}">{{ $academicYear->academic_year }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="grade_filter">Class</label>
                  <select id="grade_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($grades as $grade)
                      <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="division_filter">Division</label>
                  <select id="division_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($divisions as $division)
                      <option value="{{ $division->id }}">{{ $division->division }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="timetable_type_filter">Timetable Type</label>
                  <select id="timetable_type_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($timetableTypes as $timetableType)
                      <option value="{{ $timetableType->id }}">{{ $timetableType->title }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="status_filter">Status</label>
                  <select id="status_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($statuses as $statusValue => $statusLabel)
                      <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                    @endforeach
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
              @can('create.timetable')
                <a href="{{ route('timetables.create') }}" class="add-btn">Add New</a>
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
                    <select id="timetablePerPage" class="form-select shadow-none">
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
                    <label for="timetableTableSearch" class="nowrap">Search</label>
                    <input type="text" id="timetableTableSearch" class="form-control shadow-none" placeholder="Search...">
                    <form id="timetableExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('timetables.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('timetables.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="timetablesTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAllTimetables"></th>
                      <th>SL No</th>
                      <th>Time Table Name</th>
                      <th>Academic Year</th>
                      <th>Grade</th>
                      <th>Division</th>
                      <th>Total Periods</th>
                      <th>Application From</th>
                      <th>Application To</th>
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
  @include('timetables.partials.js')
@endpush
