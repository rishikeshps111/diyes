@extends('layouts.app')

@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
  <div class="page-title">
    <h3>Users</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">User Management</li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item active">{{ $user->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="userForm"
          action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}"
          enctype="multipart/form-data">
          @csrf
          @if ($user->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <h5 class="mb-3">User Details</h5>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="employee_code">Employee ID</label>
                <input type="text" id="employee_code" class="form-control shadow-none"
                  value="{{ $user->employee_code }}" disabled>
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="username">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" id="username"
                  class="form-control shadow-none @error('username') is-invalid @enderror"
                  value="{{ old('username', $user->username) }}">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="name">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name"
                  class="form-control shadow-none @error('name') is-invalid @enderror"
                  value="{{ old('name', $user->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email"
                  class="form-control shadow-none @error('email') is-invalid @enderror"
                  value="{{ old('email', $user->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="phone">Phone <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" name="phone_country_code" class="form-control shadow-none teacher-phone-code-input"
                    value="{{ old('phone_country_code', $user->phone_country_code ?: '+91') }}" readonly>
                  <input type="text" name="phone" id="phone"
                    class="form-control shadow-none @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $user->phone) }}">
                  @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_id">Department <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id"
                  class="form-select shadow-none @error('department_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>
                      {{ $department->department_name }}
                    </option>
                  @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="designation_id">Designation <span class="text-danger">*</span></label>
                <select name="designation_id" id="designation_id"
                  class="form-select shadow-none @error('designation_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($designations as $designation)
                    <option value="{{ $designation->id }}" @selected(old('designation_id', $user->designation_id) == $designation->id)>
                      {{ $designation->designation_name }}
                    </option>
                  @endforeach
                </select>
                @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="role_id">Role <span class="text-danger">*</span></label>
                <select name="role_id" id="role_id" class="form-select shadow-none @error('role_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                      {{ ucfirst($role->name) }}
                    </option>
                  @endforeach
                </select>
                @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="password">Password @unless($user->exists)<span class="text-danger">*</span>@endunless</label>
                <div class="input-group">
                  <input type="password" name="password" id="password"
                    class="form-control shadow-none @error('password') is-invalid @enderror">
                  <button type="button" class="btn btn-outline-secondary password-eye-btn" data-password-toggle="#password">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                  @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="password_confirmation">Confirm Password @unless($user->exists)<span class="text-danger">*</span>@endunless</label>
                <div class="input-group">
                  <input type="password" name="password_confirmation" id="password_confirmation"
                    class="form-control shadow-none">
                  <button type="button" class="btn btn-outline-secondary password-eye-btn"
                    data-password-toggle="#password_confirmation">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="profile_image">Profile Photo</label>
                <input type="file" name="profile_image" id="profile_image"
                  class="form-control shadow-none @error('profile_image') is-invalid @enderror"
                  accept="image/png,image/jpeg,image/jpg,image/webp">
                @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $user->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label>Two-Factor Authentication</label>
                <div class="d-flex align-items-center gap-2 mt-2">
                  <input type="hidden" name="is_two_factor_enabled" value="0">
                  <input type="checkbox" name="is_two_factor_enabled" value="1" id="is_two_factor_enabled" class="toggle-btn"
                    @checked((bool) old('is_two_factor_enabled', $user->is_two_factor_enabled))>
                  <span>Enabled</span>
                </div>
                @error('is_two_factor_enabled')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label>Image Preview</label>
                <div>
                  <img id="profileImagePreview" src="{{ $user->profileImageUrl() ?: asset('assets/img/user.png') }}"
                    alt="Profile image preview" class="teacher-image-preview">
                </div>
              </div>

              <div class="col-lg-12 o-f-inp mb-3">
                <label for="remarks">Remarks</label>
                <textarea name="remarks" id="remarks"
                  class="form-control shadow-none @error('remarks') is-invalid @enderror">{{ old('remarks', $user->remarks) }}</textarea>
                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('users.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="userSubmitBtn" class="submit-btn"
                data-loading-text="{{ $user->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $user->exists ? 'Update' : 'Submit' }}
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
      const userForm = document.getElementById('userForm');
      const submitButton = document.getElementById('userSubmitBtn');
      const profileImageInput = document.getElementById('profile_image');
      const profileImagePreview = document.getElementById('profileImagePreview');

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#department_id, #designation_id, #role_id, #is_active').select2({
          width: '100%',
          placeholder: '--- Select ---',
          allowClear: true
        });
      }

      document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
          const input = document.querySelector(button.dataset.passwordToggle);
          const icon = button.querySelector('i');
          input.type = input.type === 'password' ? 'text' : 'password';
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        });
      });

      profileImageInput.addEventListener('change', function () {
        const file = profileImageInput.files && profileImageInput.files[0];

        if (!file) {
          return;
        }

        profileImagePreview.src = URL.createObjectURL(file);
        profileImagePreview.onload = function () {
          URL.revokeObjectURL(profileImagePreview.src);
        };
      });

      userForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
