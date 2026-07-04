@extends('layouts.app')

@section('title', $subject->exists ? 'Edit Subject' : 'Add Subject')

@section('content')
  <div class="page-title">
    <h3>Subjects</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Academic Management</li>
        <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Subjects</a></li>
        <li class="breadcrumb-item active">{{ $subject->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="subjectForm"
          action="{{ $subject->exists ? route('subjects.update', $subject) : route('subjects.store') }}">
          @csrf
          @if ($subject->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="subject_code">Subject Code</label>
                <input type="text" id="subject_code" class="form-control shadow-none" value="{{ $subject->subject_code }}"
                  disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="subject_name">Subject Name <span class="text-danger">*</span></label>
                <input type="text" name="subject_name" id="subject_name"
                  class="form-control shadow-none @error('subject_name') is-invalid @enderror"
                  value="{{ old('subject_name', $subject->subject_name) }}">
                @error('subject_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="grade_id">Grade <span class="text-danger">*</span></label>
                <select name="grade_id" id="grade_id"
                  class="form-select shadow-none @error('grade_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(old('grade_id', $subject->grade_id) == $grade->id)>
                      {{ $grade->grade }}{{ $grade->academicYear ? ' - ' . $grade->academicYear->academic_year : '' }}
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
                  <option value="1" @selected((string) old('is_active', (int) $subject->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $subject->is_active) === '0')>Inactive
                  </option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 mb-3 o-f-inp">
                <label for="">Priority <span class="text-danger">*</span></label>
                <div class="radio-labels @error('priority') is-invalid @enderror">
                  @foreach ($priorities as $priorityValue => $priorityLabel)
                    <label for="priority_{{ $priorityValue }}">
                      <input type="radio" name="priority" id="priority_{{ $priorityValue }}"
                        value="{{ $priorityValue }}" @checked(old('priority', $subject->priority) === $priorityValue)>
                      {{ $priorityLabel }}
                    </label>
                  @endforeach
                </div>
                @error('priority')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 mb-3 o-f-inp">
                <div class="radio-labels mt-0">
                  <label for="is_praticals">
                    <input type="checkbox" name="is_praticals" id="is_praticals" value="1"
                      @checked((bool) old('is_praticals', $subject->is_praticals))>
                    Lab Required
                  </label>
                </div>
                @error('is_praticals')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('subjects.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="subjectSubmitBtn" class="submit-btn"
                data-loading-text="{{ $subject->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $subject->exists ? 'Update' : 'Submit' }}
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
      const subjectForm = document.getElementById('subjectForm');
      const submitButton = document.getElementById('subjectSubmitBtn');

      if (!subjectForm || !submitButton) {
        return;
      }

      subjectForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
