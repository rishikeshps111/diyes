@extends('layouts.app')

@section('title', 'Training Schedule')

@section('content')
  <div class="page-title">
    <h3>Training Schedule</h3>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Timetable Management</li>
      <li class="breadcrumb-item active">Training Schedule</li>
    </ol></nav>
  </div>

  <section class="section dashboard">
    <div class="row"><div class="col-lg-12 mb-3">
      <div class="collapse" id="filterCollapse">
        <div class="main-table-container"><div class="row">
          <div class="col-lg-4 mb-3"><div class="o-f-inp">
            <label for="status_filter">Status</label>
            <select id="status_filter" class="form-select shadow-none">
              <option value="">--- Select ---</option>
              @foreach ($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>
          </div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp">
            <label for="category_filter">Category</label>
            <select id="category_filter" class="form-select shadow-none">
              <option value="">--- Select ---</option>
              @foreach ($trainerCategories as $category)<option value="{{ $category->id }}">{{ $category->title }}</option>@endforeach
            </select>
          </div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp">
            <label for="type_filter">Type</label>
            <select id="type_filter" class="form-select shadow-none">
              <option value="">--- Select ---</option>
              @foreach ($trainerTypes as $type)<option value="{{ $type->id }}">{{ $type->title }}</option>@endforeach
            </select>
          </div></div>
          <div class="col-lg-12"><div class="filter-btns-top">
            <button type="button" id="resetFilters" class="reset-btn border-0" data-loading-text="Resetting...">Reset</button>
            <button type="button" id="applyFilters" class="search-btn" data-loading-text="Searching...">Search</button>
          </div></div>
        </div></div>
      </div>
    </div></div>

    <div class="col-lg-12 mb-3"><div class="main-table-container">
      <div class="row"><div class="col-lg-12"><div class="btn-flex">
        <a class="add-btn bg-filter" data-bs-toggle="collapse" href="#filterCollapse" role="button">Filters</a>
        @can('create.training-schedule')
          <a href="{{ route('training-schedules.create') }}" class="add-btn">Add New</a>
        @endcan
      </div></div></div>

      <div class="row"><div class="col-lg-12"><div class="mt-3 table-container">
        <div class="row justify-content-end">
          <div class="col-lg-5"><div class="entry-select">
            <p>Showing</p>
            <select id="trainingSchedulePerPage" class="form-select shadow-none">
              <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
            </select>
            <p>Entries</p>
          </div></div>
          <div class="col-lg-7"><div class="table-search">
            <label for="trainingScheduleTableSearch" class="nowrap">Search</label>
            <input type="text" id="trainingScheduleTableSearch" class="form-control shadow-none" placeholder="Search...">
            <div class="d-inline-flex flex-shrink-0">
              <button type="button" class="exp-btn" data-loading-text="Exporting..." data-export-url="{{ route('training-schedules.export.excel') }}">Export Excel</button>
              <button type="button" class="exp-btn" data-loading-text="Exporting..." data-export-url="{{ route('training-schedules.export.pdf') }}">Export PDF</button>
            </div>
          </div></div>
        </div>

        <div class="table-over">
          <table id="trainingSchedulesTable" class="align-middle mb-0 table table-custom mt-3 w-100">
            <thead><tr>
              <th><input type="checkbox" id="selectAllTrainingSchedules"></th>
              <th>SL No</th><th>Title</th><th>Category</th><th>Type</th><th>Start Date</th><th>End Date</th>
              <th>Status</th><th>Actions</th><th class="d-none">Created At</th>
            </tr></thead>
          </table>
        </div>
      </div></div></div>
    </div></div>
  </section>
@endsection

@push('scripts')
  @include('training-schedules.partials.js')
@endpush
