@extends('layouts.app')

@section('title', 'Special Events')

@section('content')
  <div class="page-title">
    <h3>Special Events</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item active">Special Events</li>
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
                  <label for="event_type_filter">Event Type</label>
                  <select id="event_type_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($eventTypes as $eventType)
                      <option value="{{ $eventType->id }}">{{ $eventType->title }}</option>
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
              @can('create.special-event')
                <a href="{{ route('special-events.create') }}" class="add-btn">Add New</a>
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
                    <select id="specialEventPerPage" class="form-select shadow-none">
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
                    <label for="specialEventTableSearch" class="nowrap">Search</label>
                    <input type="text" id="specialEventTableSearch" class="form-control shadow-none"
                      placeholder="Code, title, type, coordinator or class">
                    <form id="specialEventExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('special-events.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('special-events.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="specialEventsTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAllSpecialEvents"></th>
                      <th>SL No</th>
                      <th>Code</th>
                      <th>Title</th>
                      <th>Event Start Date</th>
                      <th>End Date</th>
                      <th>Coordinator</th>
                      <th>Applicable Classes</th>
                      <th>Status</th>
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

  <div class="modal fade" id="specialEventMailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" id="specialEventMailForm">
        <div class="modal-header">
          <h5 class="modal-title">Send Special Event Mail</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-12 o-f-inp mb-3">
              <label for="mail_subject">Subject <span class="text-danger">*</span></label>
              <input type="text" id="mail_subject" name="subject" class="form-control shadow-none">
            </div>
            <div class="col-lg-12 o-f-inp mb-3">
              <label for="mail_description">Description <span class="text-danger">*</span></label>
              <textarea id="mail_description" name="description" rows="4" class="form-control shadow-none"></textarea>
            </div>
            <div class="col-lg-12 o-f-inp mb-3">
              <label for="mail_emails">Mail To <span class="text-danger">*</span></label>
              <textarea id="mail_emails" name="emails" rows="3" class="form-control shadow-none"
                placeholder="Enter up to 10 email addresses, separated by comma, space, or new line"></textarea>
              <small class="text-muted">Maximum 10 email addresses.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="specialEventMailSubmit" class="btn btn-success" data-loading-text="Sending...">Send Mail</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  @include('special-events.partials.js')
@endpush
