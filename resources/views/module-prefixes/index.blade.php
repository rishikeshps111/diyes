@extends('layouts.app')

@section('title', 'Module Prefixes')

@section('content')
  <div class="page-title">
    <h3>Module Prefixes</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item active">Module Prefixes</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        <div class="row">
          <div class="col-lg-12">
            <div class="mt-3 table-container">
              <div class="row justify-content-end">
                <div class="col-lg-5">
                  <div class="entry-select">
                    <p>Showing</p>
                    <select id="modulePrefixPerPage" class="form-select shadow-none">
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
                    <label for="modulePrefixTableSearch" class="nowrap">Search</label>
                    <input type="text" id="modulePrefixTableSearch" class="form-control shadow-none"
                      placeholder="Search...">
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="modulePrefixesTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Module</th>
                      <th>Prefix</th>
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
  @include('module-prefixes.partials.js')
@endpush
