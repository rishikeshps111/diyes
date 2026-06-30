@extends('layouts.app')

@section('title', $designation->exists ? 'Edit Designation' : 'Add Designation')

@section('content')
  <div class="page-title">
    <h3>Designations</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('designations.index') }}">Designations</a></li>
        <li class="breadcrumb-item active">{{ $designation->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="designationForm"
          action="{{ $designation->exists ? route('designations.update', $designation) : route('designations.store') }}">
          @csrf
          @if ($designation->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $designation->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="designation_name">Designation Name <span class="text-danger">*</span></label>
                <input type="text" name="designation_name" id="designation_name"
                  class="form-control shadow-none @error('designation_name') is-invalid @enderror"
                  value="{{ old('designation_name', $designation->designation_name) }}">
                @error('designation_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_id">Department <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id"
                  class="form-select shadow-none @error('department_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id', $designation->department_id) == $department->id)>
                      {{ $department->department_name }}
                    </option>
                  @endforeach
                </select>
                @error('department_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="grade_id">Grade <span class="text-danger">*</span></label>
                <select name="grade_id" id="grade_id" class="form-select shadow-none @error('grade_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(old('grade_id', $designation->grade_id) == $grade->id)>
                      {{ $grade->grade }}{{ $grade->academicYear ? ' - '.$grade->academicYear->academic_year : '' }}
                    </option>
                  @endforeach
                </select>
                @error('grade_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $designation->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $designation->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description"
                  class="form-control shadow-none @error('description') is-invalid @enderror">{{ old('description', $designation->description) }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('designations.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="designationSubmitBtn" class="submit-btn"
                data-loading-text="{{ $designation->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $designation->exists ? 'Update' : 'Submit' }}
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
      const designationForm = document.getElementById('designationForm');
      const submitButton = document.getElementById('designationSubmitBtn');

      if (!designationForm || !submitButton) {
        return;
      }

      designationForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
