@extends('layouts.app')

@section('title', 'Users')

@section('content')
  <div class="page-title">
    <h3>Users</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">User Management</li>
        <li class="breadcrumb-item active">Users</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-lg-12 mb-3">
        <div class="collapse" id="filterCollapse">
          <div class="main-table-container">
            <div class="row">
              <div class="col-lg-3 mb-3">
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
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="role_filter">Role</label>
                  <select id="role_filter" class="form-select shadow-none">
                    <option value="">--- Select ---</option>
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-3 mb-3">
                <div class="o-f-inp">
                  <label for="last_login_filter">Last Login Date</label>
                  <input type="date" id="last_login_filter" class="form-control shadow-none">
                </div>
              </div>
              <div class="col-lg-3 mb-3">
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
              @can('create.user')
                <a href="{{ route('users.create') }}" class="add-btn">Add New</a>
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
                    <select id="userPerPage" class="form-select shadow-none">
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
                    <label for="userTableSearch" class="nowrap">Search</label>
                    <input type="text" id="userTableSearch" class="form-control shadow-none"
                      placeholder="Employee ID, username, name, email or phone">
                    <form id="userExportForm" method="POST" class="d-inline-flex flex-shrink-0">
                      @csrf
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('users.export.excel') }}">Export Excel</button>
                      <button type="button" class="exp-btn" data-loading-text="Exporting..."
                        data-export-url="{{ route('users.export.pdf') }}">Export PDF</button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="table-over">
                <table id="usersTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAllUsers"></th>
                      <th>SL No</th>
                      <th>Employee ID</th>
                      <th>Username</th>
                      <th>Name</th>
                      <th>Role</th>
                      <th>Department</th>
                      <th>Last Login</th>
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

  <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="resetPasswordForm">
        <div class="modal-header">
          <h5 class="modal-title">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Enter a new password for <strong id="resetPasswordUserName"></strong>.</p>
          <div class="o-f-inp mb-3">
            <label for="reset_password">New Password</label>
            <div class="input-group">
              <input type="password" id="reset_password" name="password" class="form-control shadow-none" required>
              <button type="button" class="btn btn-outline-secondary password-eye-btn"
                data-password-toggle="#reset_password">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="o-f-inp mb-0">
            <label for="reset_password_confirmation">Confirm Password</label>
            <div class="input-group">
              <input type="password" id="reset_password_confirmation" name="password_confirmation"
                class="form-control shadow-none" required>
              <button type="button" class="btn btn-outline-secondary password-eye-btn"
                data-password-toggle="#reset_password_confirmation">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="resetPasswordSubmit"
            data-loading-text="Resetting...">Reset</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  @include('users.partials.js')
@endpush