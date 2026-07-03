@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
  <div class="page-title">
    <h3>Roles & Permissions</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">User Management</li>
        <li class="breadcrumb-item active">Roles & Permissions</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        <div class="row">
          <div class="col-lg-12">
            <div class="btn-flex">
              @can('create.role')
                <a href="{{ route('roles.create') }}" class="add-btn">Add New</a>
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
                    <select id="rolePerPage" class="form-select shadow-none">
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
                    <label for="roleTableSearch" class="nowrap">Search</label>
                    <input type="text" id="roleTableSearch" class="form-control shadow-none"
                      placeholder="Search by role name">
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="rolesTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>SL No</th>
                      <th>Role Name</th>
                      <th>Number of User</th>
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
@endsection

@push('scripts')
  @include('roles.partials.js')
@endpush
