@extends('layouts.app')

@section('title', $holiday->exists ? 'Edit Holiday' : 'Add Holiday')

@section('content')
  <div class="page-title">
    <h3>Holidays</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li>
        <li class="breadcrumb-item active">{{ $holiday->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="holidayForm"
          action="{{ $holiday->exists ? route('holidays.update', $holiday) : route('holidays.store') }}">
          @csrf
          @if ($holiday->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $holiday->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="holiday_name">Holiday Name <span class="text-danger">*</span></label>
                <input type="text" name="holiday_name" id="holiday_name"
                  class="form-control shadow-none @error('holiday_name') is-invalid @enderror"
                  value="{{ old('holiday_name', $holiday->holiday_name) }}">
                @error('holiday_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="holiday_type">Holiday Type <span class="text-danger">*</span></label>
                <select name="holiday_type" id="holiday_type"
                  class="form-select shadow-none @error('holiday_type') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($holidayTypes as $holidayType)
                    <option value="{{ $holidayType }}" @selected(old('holiday_type', $holiday->holiday_type) === $holidayType)>
                      {{ $holidayType }}
                    </option>
                  @endforeach
                </select>
                @error('holiday_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                <select name="academic_year_id" id="academic_year_id"
                  class="form-select shadow-none @error('academic_year_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $holiday->academic_year_id) == $academicYear->id)>
                      {{ $academicYear->academic_year }}
                    </option>
                  @endforeach
                </select>
                @error('academic_year_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="holiday_date">Holiday Date <span class="text-danger">*</span></label>
                <input type="date" name="holiday_date" id="holiday_date"
                  class="form-control shadow-none @error('holiday_date') is-invalid @enderror"
                  value="{{ old('holiday_date', $holiday->holiday_date?->format('Y-m-d')) }}">
                @error('holiday_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date"
                  class="form-control shadow-none @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date', $holiday->start_date?->format('Y-m-d')) }}">
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date"
                  class="form-control shadow-none @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date', $holiday->end_date?->format('Y-m-d')) }}">
                @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="applicable_branch">Applicable Branch</label>
                <input type="text" name="applicable_branch" id="applicable_branch"
                  class="form-control shadow-none @error('applicable_branch') is-invalid @enderror"
                  value="{{ old('applicable_branch', $holiday->applicable_branch) }}">
                @error('applicable_branch')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="applicable_classes">Applicable Classes</label>
                <select name="applicable_classes" id="applicable_classes"
                  class="form-select shadow-none @error('applicable_classes') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($applicableClasses as $applicableClass)
                    <option value="{{ $applicableClass }}" @selected(old('applicable_classes', $holiday->applicable_classes) === $applicableClass)>
                      {{ $applicableClass }}
                    </option>
                  @endforeach
                </select>
                @error('applicable_classes')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $holiday->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $holiday->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description"
                  class="form-control shadow-none @error('description') is-invalid @enderror">{{ old('description', $holiday->description) }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('holidays.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="holidaySubmitBtn" class="submit-btn"
                data-loading-text="{{ $holiday->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $holiday->exists ? 'Update' : 'Submit' }}
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
      const holidayForm = document.getElementById('holidayForm');
      const submitButton = document.getElementById('holidaySubmitBtn');

      if (!holidayForm || !submitButton) {
        return;
      }

      holidayForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
