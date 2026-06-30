@extends('layouts.app')

@section('title', $grade->exists ? 'Edit Grade' : 'Add Grade')

@section('content')
  <div class="page-title">
    <h3>Grades</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Academic Management</li>
        <li class="breadcrumb-item"><a href="{{ route('grades.index') }}">Grades</a></li>
        <li class="breadcrumb-item active">{{ $grade->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="gradeForm"
          action="{{ $grade->exists ? route('grades.update', $grade) : route('grades.store') }}">
          @csrf
          @if ($grade->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $grade->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="grade">Grade <span class="text-danger">*</span></label>
                <input type="text" name="grade" id="grade"
                  class="form-control shadow-none @error('grade') is-invalid @enderror"
                  value="{{ old('grade', $grade->grade) }}">
                @error('grade')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="capacity">Capacity <span class="text-danger">*</span></label>
                <input type="number" name="capacity" id="capacity"
                  class="form-control shadow-none @error('capacity') is-invalid @enderror"
                  value="{{ old('capacity', $grade->capacity) }}" min="1">
                @error('capacity')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                <select name="academic_year_id" id="academic_year_id"
                  class="form-select shadow-none @error('academic_year_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $grade->academic_year_id) == $academicYear->id)>
                      {{ $academicYear->academic_year }}
                    </option>
                  @endforeach
                </select>
                @error('academic_year_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $grade->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $grade->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('grades.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="gradeSubmitBtn" class="submit-btn"
                data-loading-text="{{ $grade->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $grade->exists ? 'Update' : 'Submit' }}
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
      const gradeForm = document.getElementById('gradeForm');
      const submitButton = document.getElementById('gradeSubmitBtn');

      if (!gradeForm || !submitButton) {
        return;
      }

      gradeForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush