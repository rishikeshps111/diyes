@extends('layouts.app')

@section('title', 'Project Week')

@section('content')
  <div class="page-title">
    <h3>Project Week</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item active">Project Week</li>
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
                  <label for="grade_filter">Grade</label>
                  <select id="grade_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($grades as $grade)
                      <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
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
                <div class="filter-btns-top">
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
              @can('create.project-week')
                <a href="{{ route('project-weeks.create') }}" class="add-btn">Add New</a>
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
                    <select id="projectWeekPerPage" class="form-select shadow-none">
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
                    <label for="projectWeekTableSearch" class="nowrap">Search</label>
                    <input type="text" id="projectWeekTableSearch" class="form-control shadow-none" placeholder="Search...">
                    <form id="projectWeekExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('project-weeks.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('project-weeks.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="projectWeeksTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAllProjectWeeks"></th>
                      <th>SL No</th>
                      <th>Code</th>
                      <th>Project</th>
                      <th>Applicable From</th>
                      <th>Applicable To</th>
                      <th>Academic Year</th>
                      <th>Grade</th>
                      <th>Division</th>
                      <th>Total Periods</th>
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

  <div class="modal fade" id="projectWeekPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <div>
            <h5 class="modal-title" id="projectWeekPreviewTitle">View TimeTable</h5>
            <div class="text-muted small fw-semibold" id="projectWeekPreviewSubtitle"></div>
          </div>
          <div class="d-flex align-items-end gap-2">
            <a href="#" class="btn btn-primary text-decoration-none d-none" id="projectWeekPreviewPdf"
              target="_blank">Download PDF</a>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="table-over">
            <table class="align-middle mb-0 table table-bordered w-100">
              <thead id="projectWeekPreviewHead"></thead>
              <tbody id="projectWeekPreviewBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @include('project-weeks.partials.js')
@endpush
