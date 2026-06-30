@extends('layouts.app')

@section('title', $department->exists ? 'Edit Department' : 'Add Department')

@section('content')
  <div class="page-title">
    <h3>Departments</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
        <li class="breadcrumb-item active">{{ $department->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="departmentForm"
          action="{{ $department->exists ? route('departments.update', $department) : route('departments.store') }}">
          @csrf
          @if ($department->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_code">Department Code</label>
                <input type="text" id="department_code" class="form-control shadow-none"
                  value="{{ $department->department_code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_name">Department Name <span class="text-danger">*</span></label>
                <input type="text" name="department_name" id="department_name"
                  class="form-control shadow-none @error('department_name') is-invalid @enderror"
                  value="{{ old('department_name', $department->department_name) }}">
                @error('department_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_head">Department Head</label>
                <input type="text" name="department_head" id="department_head"
                  class="form-control shadow-none @error('department_head') is-invalid @enderror"
                  value="{{ old('department_head', $department->department_head) }}">
                @error('department_head')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description"
                  class="form-control shadow-none @error('description') is-invalid @enderror">{{ old('description', $department->description) }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="teacher_count">Teacher Count <span class="text-danger">*</span></label>
                <input type="number" name="teacher_count" id="teacher_count"
                  class="form-control shadow-none @error('teacher_count') is-invalid @enderror"
                  value="{{ old('teacher_count', $department->teacher_count) }}" min="0">
                @error('teacher_count')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="display_order">Display Order <span class="text-danger">*</span></label>
                <input type="number" name="display_order" id="display_order"
                  class="form-control shadow-none @error('display_order') is-invalid @enderror"
                  value="{{ old('display_order', $department->display_order) }}" min="0">
                @error('display_order')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $department->is_active) === '1')>Active
                  </option>
                  <option value="0" @selected((string) old('is_active', (int) $department->is_active) === '0')>Inactive
                  </option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('departments.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="departmentSubmitBtn" class="submit-btn"
                data-loading-text="{{ $department->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $department->exists ? 'Update' : 'Submit' }}
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
      const departmentForm = document.getElementById('departmentForm');
      const submitButton = document.getElementById('departmentSubmitBtn');

      if (!departmentForm || !submitButton) {
        return;
      }

      departmentForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
