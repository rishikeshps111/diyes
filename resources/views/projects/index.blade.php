@extends('layouts.app')

@section('title', 'Projects')

@section('content')
  <div class="page-title">
    <h3>Projects</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Project Management</li>
        <li class="breadcrumb-item active">Projects</li>
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
                  <label for="project_category_filter">Category</label>
                  <select id="project_category_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($categories as $category)
                      <option value="{{ $category->id }}">{{ $category->title }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-4 mb-3">
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
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="status_filter">Status</label>
                  <select id="status_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($statuses as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
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
              @can('create.project')
                <a href="{{ route('projects.create') }}" class="add-btn">Add New</a>
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
                    <select id="projectPerPage" class="form-select shadow-none">
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
                    <label for="projectTableSearch" class="nowrap">Search</label>
                    <input type="text" id="projectTableSearch" class="form-control shadow-none"
                      placeholder="Code, title, category, class, subject, teacher or venue">
                    <form id="projectExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('projects.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('projects.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="projectsTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAllProjects"></th>
                      <th>SL No</th>
                      <th>Project Code</th>
                      <th>Project Title</th>
                      <th>Category</th>
                      <th>Duration</th>
                      <th>Classes</th>
                      <th>Subjects</th>
                      <th>Allocated Teachers</th>
                      <th>Venue</th>
                      <th>Created Date</th>
                      <th>Timetable Replacement</th>
                      <th>Status</th>
                      <th>Actions</th>
                      <th class="d-none">Created Raw</th>
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

  <div class="modal fade" id="projectStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="projectStatusForm">
        <div class="modal-header">
          <h5 class="modal-title">Change Project Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="o-f-inp">
            <label for="project_status_modal">Status <span class="text-danger">*</span></label>
            <select id="project_status_modal" class="form-select shadow-none" required>
              @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="projectStatusSubmit" class="btn btn-success"
            data-loading-text="Updating...">Update</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  @include('projects.partials.js')
@endpush