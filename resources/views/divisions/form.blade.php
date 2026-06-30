@extends('layouts.app')

@section('title', $division->exists ? 'Edit Division' : 'Add Division')

@section('content')
  <div class="page-title">
    <h3>Divisions</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Academic Management</li>
        <li class="breadcrumb-item"><a href="{{ route('divisions.index') }}">Divisions</a></li>
        <li class="breadcrumb-item active">{{ $division->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="divisionForm"
          action="{{ $division->exists ? route('divisions.update', $division) : route('divisions.store') }}">
          @csrf
          @if ($division->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $division->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="division">Division <span class="text-danger">*</span></label>
                <input type="text" name="division" id="division"
                  class="form-control shadow-none @error('division') is-invalid @enderror"
                  value="{{ old('division', $division->division) }}">
                @error('division')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="grade_id">Grade <span class="text-danger">*</span></label>
                <select name="grade_id" id="grade_id" class="form-select shadow-none @error('grade_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(old('grade_id', $division->grade_id) == $grade->id)>
                      {{ $grade->grade }} - {{ $grade->academicYear?->academic_year }}
                    </option>
                  @endforeach
                </select>
                @error('grade_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="capacity">Capacity <span class="text-danger">*</span></label>
                <input type="number" name="capacity" id="capacity"
                  class="form-control shadow-none @error('capacity') is-invalid @enderror"
                  value="{{ old('capacity', $division->capacity) }}" min="1">
                @error('capacity')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="class_teacher">Class Teacher</label>
                <input type="text" name="class_teacher" id="class_teacher"
                  class="form-control shadow-none @error('class_teacher') is-invalid @enderror"
                  value="{{ old('class_teacher', $division->class_teacher) }}">
                @error('class_teacher')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="room_number">Room Number</label>
                <input type="number" name="room_number" id="room_number"
                  class="form-control shadow-none @error('room_number') is-invalid @enderror"
                  value="{{ old('room_number', $division->room_number) }}" min="1">
                @error('room_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $division->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $division->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('divisions.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="divisionSubmitBtn" class="submit-btn"
                data-loading-text="{{ $division->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $division->exists ? 'Update' : 'Submit' }}
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
      const divisionForm = document.getElementById('divisionForm');
      const submitButton = document.getElementById('divisionSubmitBtn');

      if (!divisionForm || !submitButton) {
        return;
      }

      divisionForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
