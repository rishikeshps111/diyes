@extends('layouts.app')

@section('title', 'Classrooms')

@section('content')
  <div class="page-title">
    <h3>Classrooms</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item active">Classrooms</li>
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
                  <label for="building_filter">Building</label>
                  <input type="text" id="building_filter" class="form-control shadow-none"
                    placeholder="Search by building">
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="floor_filter">Floor</label>
                  <input type="text" id="floor_filter" class="form-control shadow-none"
                    placeholder="Search by floor">
                </div>
              </div>
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="room_type_filter">Room Type</label>
                  <select id="room_type_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($roomTypes as $roomType)
                      <option value="{{ $roomType }}">{{ $roomType }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
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
              <div class="col-lg-4 mb-3">
                <div class="o-f-inp">
                  <label for="capacity_filter">Capacity</label>
                  <input type="number" id="capacity_filter" class="form-control shadow-none"
                    placeholder="Filter by capacity" min="1">
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
              @can('create.classroom')
                <a href="{{ route('classrooms.create') }}" class="add-btn">Add New</a>
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
                    <select id="classroomPerPage" class="form-select shadow-none">
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
                    <label for="classroomTableSearch" class="nowrap">Search</label>
                    <input type="text" id="classroomTableSearch" class="form-control shadow-none"
                      placeholder="Search...">
                    <form id="classroomExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('classrooms.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('classrooms.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="classroomsTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>
                        <input type="checkbox" id="selectAllClassrooms">
                      </th>
                      <th>SL No</th>
                      <th>Code</th>
                      <th>Room Name</th>
                      <th>Building</th>
                      <th>Room Type</th>
                      <th>Capacity</th>
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
  @include('classrooms.partials.js')
@endpush
