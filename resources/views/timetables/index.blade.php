@extends('layouts.app')

@section('title', 'Regular Timetable')

@push('styles')
  <style>
    .preview-table tr.break td {
      background: #fff7ed;
      color: #9a3412;
      font-weight: 700;
      text-align: center;
    }

    .preview-table tr.lunch td {
      background: #ecfdf5;
      color: #047857;
      font-weight: 700;
      text-align: center;
    }

    .preview-cell-title {
      color: #111827;
      display: block;
      font-weight: 700;
    }

    .preview-cell-meta {
      color: #64748b;
      display: block;
      font-size: 12px;
      line-height: 1.45;
      margin-top: 2px;
    }

    .timetable-preview-subtitle {
      color: #64748b;
      font-size: 13px;
      font-weight: 600;
      margin-top: 4px;
    }
  </style>
@endpush

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
                  <label for="timetable_category_filter">Timetable Category</label>
                  <select id="timetable_category_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($timetableCategories as $timetableCategory)
                      <option value="{{ $timetableCategory->id }}">{{ $timetableCategory->title }}</option>
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
                  <button type="button" id="resetFilters" class="reset-btn border-0"
                    data-loading-text="Resetting...">Reset</button>
                  <button type="button" id="applyFilters" class="search-btn"
                    data-loading-text="Searching...">Search</button>
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

  <div class="modal fade" id="timetablePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <div>
            <h5 class="modal-title" id="timetablePreviewTitle">View TimeTable</h5>
            <div class="timetable-preview-subtitle" id="timetablePreviewSubtitle"></div>
          </div>
          <div class="d-flex align-items-end gap-2">
            <a href="#" class="btn btn-primary text-decoration-none d-none" id="timetablePreviewPdf"
              target="_blank">Download PDF</a>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="table-over">
            <table class="align-middle mb-0 table table-bordered preview-table w-100">
              <thead id="timetablePreviewHead"></thead>
              <tbody id="timetablePreviewBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @include('timetables.partials.js')
@endpush