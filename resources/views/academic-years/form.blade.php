@extends('layouts.app')

@section('title', $academicYear->exists ? 'Edit Academic Year' : 'Add Academic Year')

@section('content')
  <div class="page-title">
    <h3>Academic Years</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Academic Management</li>
        <li class="breadcrumb-item"><a href="{{ route('academic-years.index') }}">Academic Year</a></li>
        <li class="breadcrumb-item active">{{ $academicYear->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="academicYearForm"
          action="{{ $academicYear->exists ? route('academic-years.update', $academicYear) : route('academic-years.store') }}">
          @csrf
          @if ($academicYear->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $academicYear->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="academic_year">Academic Year <span class="text-danger">*</span></label>
                <input type="text" name="academic_year" id="academic_year"
                  class="form-control shadow-none @error('academic_year') is-invalid @enderror"
                  value="{{ old('academic_year', $academicYear->academic_year) }}">
                @error('academic_year')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date"
                  class="form-control shadow-none @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date', $academicYear->start_date?->format('Y-m-d')) }}">
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date"
                  class="form-control shadow-none @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date', $academicYear->end_date?->format('Y-m-d')) }}">
                @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $academicYear->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $academicYear->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description"
                  class="form-control shadow-none @error('description') is-invalid @enderror">{{ old('description', $academicYear->description) }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('academic-years.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="academicYearSubmitBtn" class="submit-btn"
                data-loading-text="{{ $academicYear->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $academicYear->exists ? 'Update' : 'Submit' }}
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
      const academicYearForm = document.getElementById('academicYearForm');
      const submitButton = document.getElementById('academicYearSubmitBtn');

      if (!academicYearForm || !submitButton) {
        return;
      }

      academicYearForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
