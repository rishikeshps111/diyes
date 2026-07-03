@extends('layouts.app')

@section('title', $role->exists ? 'Edit Role' : 'Add Role')

@section('content')
  <div class="page-title">
    <h3>Roles & Permissions</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">User Management</li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles & Permissions</a></li>
        <li class="breadcrumb-item active">{{ $role->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="roleForm"
          action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}">
          @csrf
          @if ($role->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="name">Role Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name"
                  class="form-control shadow-none @error('name') is-invalid @enderror"
                  value="{{ old('name', $role->name) }}">
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="main-table-container mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
              <h5 class="mb-0">Permissions</h5>
              <div>
                <button type="button" id="selectAllPermissions" class="btn btn-sm btn-outline-primary">Select All</button>
                <button type="button" id="clearAllPermissions" class="btn btn-sm btn-outline-danger">Clear</button>
              </div>
            </div>

            @error('permissions')
              <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror
            @error('permissions.*')
              <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror

            <div class="row">
              @foreach ($permissionGroups as $groupName => $permissions)
                <div class="col-lg-12 mb-3">
                  <div class="border rounded p-3 h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                      <h6 class="fw-semibold mb-0">{{ $groupName }}</h6>
                      <div>
                        <button type="button" class="btn btn-sm btn-outline-primary permission-group-select"
                          data-group="{{ Str::slug($groupName) }}">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-danger permission-group-clear"
                          data-group="{{ Str::slug($groupName) }}">Clear</button>
                      </div>
                    </div>

                    <div class="row">
                      @foreach ($permissions as $permission)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                          <div class="form-check mb-0">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                              class="form-check-input permission-check @error('permissions') is-invalid @enderror"
                              data-group="{{ Str::slug($groupName) }}"
                              id="permission_{{ Str::slug($permission->name) }}"
                              @checked(collect(old('permissions', $selectedPermissions))->contains($permission->name))>
                            <label class="form-check-label" for="permission_{{ Str::slug($permission->name) }}">
                              {{ ucwords(str_replace(['.', '-'], ' ', $permission->name)) }}
                            </label>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('roles.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="roleSubmitBtn" class="submit-btn"
                data-loading-text="{{ $role->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $role->exists ? 'Update' : 'Submit' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const roleForm = document.getElementById('roleForm');
      const submitButton = document.getElementById('roleSubmitBtn');
      const permissionChecks = Array.from(document.querySelectorAll('.permission-check'));

      document.querySelectorAll('.permission-group-select').forEach(function (button) {
        button.addEventListener('click', function () {
          permissionChecks.forEach(function (check) {
            if (check.dataset.group === button.dataset.group) {
              check.checked = true;
            }
          });
        });
      });

      document.querySelectorAll('.permission-group-clear').forEach(function (button) {
        button.addEventListener('click', function () {
          permissionChecks.forEach(function (check) {
            if (check.dataset.group === button.dataset.group) {
              check.checked = false;
            }
          });
        });
      });

      document.getElementById('selectAllPermissions')?.addEventListener('click', function () {
        permissionChecks.forEach(function (check) {
          check.checked = true;
        });
      });

      document.getElementById('clearAllPermissions')?.addEventListener('click', function () {
        permissionChecks.forEach(function (check) {
          check.checked = false;
        });
      });

      if (!roleForm || !submitButton) {
        return;
      }

      roleForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
