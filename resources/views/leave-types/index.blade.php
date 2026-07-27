@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
  <div class="page-title">
    <h3>Leave Types</h3>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item">Leave Management</li><li class="breadcrumb-item active">Leave Types</li></ol></nav>
  </div>

  <section class="section dashboard">
    <div class="row"><div class="col-lg-12 mb-3"><div class="collapse" id="filterCollapse"><div class="main-table-container"><div class="row">
      <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="leave_type_filter">Leave Type</label><select id="leave_type_filter" class="form-select shadow-none"><option value="">--- Select ---</option>@foreach($leaveTypes as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div></div>
      <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="applicable_for_filter">Applicable For</label><select id="applicable_for_filter" class="form-select shadow-none"><option value="">--- Select ---</option>@foreach($applicableForOptions as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div></div>
      <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="status_filter">Status</label><select id="status_filter" class="form-select shadow-none"><option value="">--- Select ---</option><option value="1">Active</option><option value="0">Inactive</option></select></div></div>
      <div class="col-lg-12"><div class="filter-btns-top"><button type="button" id="resetFilters" class="reset-btn border-0">Reset</button><button type="button" id="applyFilters" class="search-btn">Search</button></div></div>
    </div></div></div></div></div>

    <div class="main-table-container">
      <div class="btn-flex">
        <a class="add-btn bg-filter" data-bs-toggle="collapse" href="#filterCollapse">Filters</a>
        @can('create.leave-type')<a href="{{ route('leave-types.create') }}" class="add-btn">Add New</a>@endcan
      </div>
      <div class="mt-3 table-container">
        <div class="row justify-content-end">
          <div class="col-lg-5"><div class="entry-select"><p>Showing</p><select id="leaveTypePerPage" class="form-select shadow-none"><option>10</option><option>25</option><option>50</option><option>100</option></select><p>Entries</p></div></div>
          <div class="col-lg-7"><div class="table-search"><label for="leaveTypeTableSearch">Search</label><input id="leaveTypeTableSearch" class="form-control shadow-none" placeholder="Code, name, role or description"><form id="leaveTypeExportForm" method="POST" class="d-inline-flex flex-shrink-0">@csrf<button type="button" class="exp-btn" data-export-url="{{ route('leave-types.export.excel') }}">Export Excel</button><button type="button" class="exp-btn" data-export-url="{{ route('leave-types.export.pdf') }}">Export PDF</button></form></div></div>
        </div>
        <div class="table-over"><table id="leaveTypesTable" class="align-middle mb-0 table table-custom mt-3 w-100"><thead><tr>
          <th><input type="checkbox" id="selectAllLeaveTypes"></th><th>SL No</th><th>Code</th><th>Name</th><th>Type</th><th>Max / Year</th><th>Carry Forward</th><th>Applicable For</th><th>Gender</th><th>Max / Request</th><th>Status</th><th>Actions</th><th class="d-none">Created</th>
        </tr></thead></table></div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  @include('leave-types.partials.js')
@endpush
