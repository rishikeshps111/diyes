@extends('layouts.app')

@section('title', $specialEvent->exists ? 'Edit Special Event' : 'Add Special Event')

@php
  $selectedStaff = collect(old('staff_coordinator_ids', $specialEvent->exists ? $specialEvent->staffCoordinators->pluck('id')->all() : []))->map(fn($id) => (string) $id);
  $selectedTeachers = collect(old('teacher_coordinator_ids', $specialEvent->exists ? $specialEvent->teacherCoordinators->pluck('id')->all() : []))->map(fn($id) => (string) $id);
  $selectedParticipants = collect(old('participants', $specialEvent->participants ?? []))->map(fn($item) => (string) $item);
  $selectedGrades = collect(old('grade_ids', $specialEvent->exists ? $specialEvent->grades->pluck('id')->all() : []))->map(fn($id) => (string) $id);
  $selectedDivisions = collect(old('division_ids', $specialEvent->exists ? $specialEvent->divisions->pluck('id')->all() : []))->map(fn($id) => (string) $id);
  $oldTimings = collect(old('timings', $specialEvent->exists ? $specialEvent->timings->map(fn($timing) => [
    'day_number' => $timing->day_number,
    'event_date' => $timing->event_date?->toDateString(),
    'day_label' => $timing->day_label,
    'start_time' => substr((string) $timing->start_time, 0, 5),
    'end_time' => substr((string) $timing->end_time, 0, 5),
  ])->all() : []));
@endphp

@push('styles')
  <style>
    .file-preview-list {
      display: grid;
      gap: 8px;
      margin-top: 8px;
    }

    .file-preview-item {
      align-items: center;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      display: flex;
      gap: 10px;
      padding: 8px 10px;
    }

    .file-preview-item img {
      border-radius: 6px;
      height: 42px;
      object-fit: cover;
      width: 56px;
    }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Special Events</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('special-events.index') }}">Special Events</a></li>
        <li class="breadcrumb-item active">{{ $specialEvent->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <form method="POST" enctype="multipart/form-data" id="specialEventForm"
      action="{{ $specialEvent->exists ? route('special-events.update', $specialEvent) : route('special-events.store') }}">
      @csrf
      @if ($specialEvent->exists)
        @method('PUT')
      @endif

      <div class="main-table-container mb-3">
        <h5 class="title-w-sec">Event Details</h5>
        <hr>
        <div class="row">
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="event_code">Event Code</label>
            <input type="text" id="event_code" class="form-control shadow-none" value="{{ $specialEvent->event_code }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="event_title">Event Title <span class="text-danger">*</span></label>
            <input type="text" name="event_title" id="event_title"
              class="form-control shadow-none @error('event_title') is-invalid @enderror"
              value="{{ old('event_title', $specialEvent->event_title) }}">
            @error('event_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="event_type_id">Event Type <span class="text-danger">*</span></label>
            <select name="event_type_id" id="event_type_id"
              class="form-select shadow-none @error('event_type_id') is-invalid @enderror">
              <option value="">--- Select ---</option>
              @foreach ($eventTypes as $eventType)
                <option value="{{ $eventType->id }}" @selected(old('event_type_id', $specialEvent->event_type_id) == $eventType->id)>
                  {{ $eventType->title }}
                </option>
              @endforeach
            </select>
            @error('event_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
            <select name="academic_year_id" id="academic_year_id"
              class="form-select shadow-none @error('academic_year_id') is-invalid @enderror">
              <option value="">--- Select ---</option>
              @foreach ($academicYears as $academicYear)
                <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $specialEvent->academic_year_id) == $academicYear->id)>
                  {{ $academicYear->academic_year }}
                </option>
              @endforeach
            </select>
            @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="event_start_date">Event Start Date <span class="text-danger">*</span></label>
            <input type="date" name="event_start_date" id="event_start_date"
              class="form-control shadow-none @error('event_start_date') is-invalid @enderror"
              min="{{ now()->toDateString() }}"
              value="{{ old('event_start_date', $specialEvent->event_start_date?->toDateString()) }}">
            @error('event_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="event_end_date">Event End Date <span class="text-danger">*</span></label>
            <input type="date" name="event_end_date" id="event_end_date"
              class="form-control shadow-none @error('event_end_date') is-invalid @enderror"
              min="{{ now()->toDateString() }}"
              value="{{ old('event_end_date', $specialEvent->event_end_date?->toDateString()) }}">
            @error('event_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="days">Days</label>
            <input type="text" id="days" class="form-control shadow-none" value="{{ old('days', $specialEvent->days) }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="media_coverable">Media Coverable <span class="text-danger">*</span></label>
            <select name="media_coverable" id="media_coverable"
              class="form-select shadow-none @error('media_coverable') is-invalid @enderror">
              <option value="1" @selected((string) old('media_coverable', (int) $specialEvent->media_coverable) === '1')>Yes</option>
              <option value="0" @selected((string) old('media_coverable', (int) $specialEvent->media_coverable) === '0')>No</option>
            </select>
            @error('media_coverable')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select shadow-none @error('status') is-invalid @enderror">
              @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $specialEvent->status ?: 'draft') === $value)>{{ $label }}</option>
              @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="main-table-container mb-3">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
          <h5 class="title-w-sec mb-0">Timing</h5>
          <button type="button" class="add-btn border-0" id="addTimingBtn">Add Timing</button>
        </div>
        <hr>
        @error('timings')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        <div class="table-over">
          <table class="table table-bordered align-middle mb-0">
            <thead>
              <tr>
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="timingRows"></tbody>
          </table>
        </div>
      </div>

      <div class="main-table-container mb-3">
        <h5 class="title-w-sec">Organization</h5>
        <hr>
        <div class="row">
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="venue">Venue</label>
            <input type="text" name="venue" id="venue" class="form-control shadow-none @error('venue') is-invalid @enderror"
              value="{{ old('venue', $specialEvent->venue) }}">
            @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="organized_by">Organized By</label>
            <input type="text" name="organized_by" id="organized_by"
              class="form-control shadow-none @error('organized_by') is-invalid @enderror"
              value="{{ old('organized_by', $specialEvent->organized_by) }}">
            @error('organized_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="incharge">Incharge</label>
            <input type="text" name="incharge" id="incharge" class="form-control shadow-none @error('incharge') is-invalid @enderror"
              value="{{ old('incharge', $specialEvent->incharge) }}">
            @error('incharge')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="contact_no">Contact No</label>
            <input type="text" name="contact_no" id="contact_no"
              class="form-control shadow-none @error('contact_no') is-invalid @enderror"
              value="{{ old('contact_no', $specialEvent->contact_no) }}">
            @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="staff_coordinator_ids">Event Coordinator - Staff</label>
            <select name="staff_coordinator_ids[]" id="staff_coordinator_ids"
              class="form-select shadow-none @error('staff_coordinator_ids') is-invalid @enderror" multiple>
              @foreach ($staff as $staffMember)
                <option value="{{ $staffMember->id }}" @selected($selectedStaff->contains((string) $staffMember->id))>
                  {{ $staffMember->name }}{{ $staffMember->employee_code ? ' ('.$staffMember->employee_code.')' : '' }}
                </option>
              @endforeach
            </select>
            @error('staff_coordinator_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="teacher_coordinator_ids">Event Coordinator - Teachers</label>
            <select name="teacher_coordinator_ids[]" id="teacher_coordinator_ids"
              class="form-select shadow-none @error('teacher_coordinator_ids') is-invalid @enderror" multiple>
              @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected($selectedTeachers->contains((string) $teacher->id))>
                  {{ $teacher->name }}{{ $teacher->employee_id ? ' ('.$teacher->employee_id.')' : '' }}
                </option>
              @endforeach
            </select>
            @error('teacher_coordinator_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="main-table-container mb-3">
        <h5 class="title-w-sec">Participation</h5>
        <hr>
        <div class="row">
          <div class="col-lg-12 o-f-inp mb-3">
            <label>Event Participation <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3 mt-2">
              @foreach ($participants as $value => $label)
                <label class="d-inline-flex align-items-center gap-2">
                  <input type="checkbox" name="participants[]" value="{{ $value }}" @checked($selectedParticipants->contains($value))>
                  {{ $label }}
                </label>
              @endforeach
            </div>
            @error('participants')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="grade_ids">Students Applicable For</label>
            <select name="grade_ids[]" id="grade_ids"
              class="form-select shadow-none @error('grade_ids') is-invalid @enderror" multiple>
              @foreach ($grades as $grade)
                <option value="{{ $grade->id }}" @selected($selectedGrades->contains((string) $grade->id))>
                  {{ $grade->grade }}{{ $grade->academicYear ? ' - '.$grade->academicYear->academic_year : '' }}
                </option>
              @endforeach
            </select>
            @error('grade_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="division_ids">Divisions</label>
            <select name="division_ids[]" id="division_ids"
              class="form-select shadow-none @error('division_ids') is-invalid @enderror" multiple>
              @foreach ($divisions as $division)
                <option value="{{ $division->id }}" data-grade-id="{{ $division->grade_id }}" @selected($selectedDivisions->contains((string) $division->id))>
                  {{ $division->grade?->grade ? $division->grade->grade.' - ' : '' }}{{ $division->division }}
                </option>
              @endforeach
            </select>
            @error('division_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="outside_candidates">Outside Candidates <span class="text-danger">*</span></label>
            <select name="outside_candidates" id="outside_candidates"
              class="form-select shadow-none @error('outside_candidates') is-invalid @enderror">
              <option value="1" @selected((string) old('outside_candidates', (int) $specialEvent->outside_candidates) === '1')>Yes</option>
              <option value="0" @selected((string) old('outside_candidates', (int) $specialEvent->outside_candidates) === '0')>No</option>
            </select>
            @error('outside_candidates')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="main-table-container mb-3">
        <h5 class="title-w-sec">Content & Files</h5>
        <hr>
        <div class="row">
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="objective">Objective</label>
            <input type="text" name="objective" id="objective"
              class="form-control shadow-none @error('objective') is-invalid @enderror"
              value="{{ old('objective', $specialEvent->objective) }}">
            @error('objective')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="banner_image">Banner Image</label>
            <input type="file" name="banner_image" id="banner_image"
              class="form-control shadow-none @error('banner_image') is-invalid @enderror" accept="image/*">
            @error('banner_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div id="bannerPreview" class="file-preview-list">
              @if ($specialEvent->bannerUrl())
                <div class="file-preview-item"><img src="{{ $specialEvent->bannerUrl() }}" alt="Banner"><span>Current banner</span></div>
              @endif
            </div>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="attachments">Attachments</label>
            <input type="file" name="attachments[]" id="attachments"
              class="form-control shadow-none @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror" multiple>
            @error('attachments')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('attachments.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            <div id="attachmentPreview" class="file-preview-list">
              @foreach ($specialEvent->attachments ?? [] as $attachment)
                <div class="file-preview-item">
                  <span class="fw-semibold">{{ $attachment->file_name }}</span>
                  <a href="{{ $attachment->fileUrl() }}" target="_blank">View</a>
                </div>
              @endforeach
            </div>
          </div>
          <div class="col-lg-12 o-f-inp mb-3">
            <label for="event_details">Event Details</label>
            <textarea name="event_details" id="event_details" rows="4"
              class="form-control shadow-none @error('event_details') is-invalid @enderror">{{ old('event_details', $specialEvent->event_details) }}</textarea>
            @error('event_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="col-lg-12 d-flex justify-content-center align-items-center mb-4">
        <div class="btn-flex">
          <a href="{{ route('special-events.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" id="specialEventSubmitBtn" class="submit-btn"
            data-loading-text="{{ $specialEvent->exists ? 'Updating...' : 'Submitting...' }}">
            {{ $specialEvent->exists ? 'Update' : 'Submit' }}
          </button>
        </div>
      </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('specialEventForm');
      const submitButton = document.getElementById('specialEventSubmitBtn');
      const startDate = document.getElementById('event_start_date');
      const endDate = document.getElementById('event_end_date');
      const daysInput = document.getElementById('days');
      const timingRows = document.getElementById('timingRows');
      const addTimingButton = document.getElementById('addTimingBtn');
      const gradeSelect = document.getElementById('grade_ids');
      const divisionSelect = document.getElementById('division_ids');
      const savedTimings = @json($oldTimings->values());
      const initialDivisionOptions = Array.from(divisionSelect.options).map(function (option) {
        return { value: option.value, text: option.textContent, gradeId: option.dataset.gradeId || '', selected: option.selected };
      });
      let timingIndex = 0;
      let dayOptions = [];
      let isInitialDivisionFilter = true;

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#event_type_id, #academic_year_id, #media_coverable, #status, #staff_coordinator_ids, #teacher_coordinator_ids, #grade_ids, #division_ids, #outside_candidates').select2({
          width: '100%',
          placeholder: '--- Select ---',
          allowClear: true
        });

        jQuery(gradeSelect).on('change', filterDivisions);
      }

      startDate.addEventListener('change', rebuildDays);
      endDate.addEventListener('change', rebuildDays);
      addTimingButton.addEventListener('click', function () {
        addTimingRow();
      });
      document.getElementById('banner_image').addEventListener('change', previewBanner);
      document.getElementById('attachments').addEventListener('change', previewAttachments);
      form.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });

      rebuildDays(savedTimings.length ? savedTimings : null);
      syncEndDateMin();
      filterDivisions();

      function rebuildDays(existingRows) {
        syncEndDateMin();
        dayOptions = buildDayOptions();
        daysInput.value = dayOptions.length || '';
        timingRows.innerHTML = '';
        timingIndex = 0;

        const rows = Array.isArray(existingRows) && existingRows.length ? existingRows : dayOptions.map(function (day) {
          return {
            day_number: day.dayNumber,
            event_date: day.date,
            day_label: day.label,
            start_time: '',
            end_time: ''
          };
        });

        rows.forEach(addTimingRow);
      }

      function syncEndDateMin() {
        endDate.min = startDate.value || @json(now()->toDateString());

        if (endDate.value && startDate.value && endDate.value < startDate.value) {
          endDate.value = '';
        }
      }

      function buildDayOptions() {
        if (!startDate.value || !endDate.value) {
          return [];
        }

        const start = new Date(startDate.value + 'T00:00:00');
        const end = new Date(endDate.value + 'T00:00:00');

        if (end < start) {
          endDate.value = '';
          return [];
        }

        const days = [];
        const formatter = new Intl.DateTimeFormat('en-US', { weekday: 'short' });

        for (let current = new Date(start), index = 1; current <= end; current.setDate(current.getDate() + 1), index++) {
          const date = current.toISOString().slice(0, 10);
          const suffix = ordinal(current.getDate());
          const label = 'Day ' + index + ' : ' + formatter.format(current).toUpperCase() + ' ' + current.getDate() + suffix;
          days.push({ dayNumber: index, date: date, label: label });
        }

        return days;
      }

      function addTimingRow(row) {
        if (!dayOptions.length && !(row && row.event_date)) {
          Swal.fire('Select Dates', 'Select event start and end date before adding timings.', 'warning');
          return;
        }

        const selectedDay = dayOptions.find(function (day) {
          return Number(day.dayNumber) === Number(row?.day_number);
        }) || dayOptions[0] || { dayNumber: row.day_number, date: row.event_date, label: row.day_label };

        const currentIndex = timingIndex++;
        const tr = document.createElement('tr');
        tr.innerHTML =
          '<td>' +
            '<select class="form-select shadow-none timing-day-select">' +
              dayOptions.map(function (day) {
                return '<option value="' + day.dayNumber + '" data-date="' + day.date + '" data-label="' + escapeHtml(day.label) + '" ' +
                  (Number(day.dayNumber) === Number(selectedDay.dayNumber) ? 'selected' : '') + '>' + escapeHtml(day.label) + '</option>';
              }).join('') +
            '</select>' +
            '<input type="hidden" name="timings[' + currentIndex + '][day_number]" value="' + selectedDay.dayNumber + '">' +
            '<input type="hidden" name="timings[' + currentIndex + '][event_date]" value="' + selectedDay.date + '">' +
            '<input type="hidden" name="timings[' + currentIndex + '][day_label]" value="' + escapeHtml(selectedDay.label) + '">' +
          '</td>' +
          '<td><input type="time" name="timings[' + currentIndex + '][start_time]" class="form-control shadow-none" value="' + escapeHtml(row?.start_time || '') + '"></td>' +
          '<td><input type="time" name="timings[' + currentIndex + '][end_time]" class="form-control shadow-none" value="' + escapeHtml(row?.end_time || '') + '"></td>' +
          '<td><button type="button" class="btn btn-danger btn-sm timing-remove-btn">Remove</button></td>';

        timingRows.appendChild(tr);
        syncTimingHiddenFields(tr);

        tr.querySelector('.timing-day-select').addEventListener('change', function () {
          syncTimingHiddenFields(tr);
        });
        tr.querySelector('.timing-remove-btn').addEventListener('click', function () {
          tr.remove();
        });
      }

      function syncTimingHiddenFields(row) {
        const selected = row.querySelector('.timing-day-select').selectedOptions[0];
        row.querySelector('[name$="[day_number]"]').value = selected.value;
        row.querySelector('[name$="[event_date]"]').value = selected.dataset.date;
        row.querySelector('[name$="[day_label]"]').value = selected.dataset.label;
      }

      function filterDivisions() {
        const selectedGradeIds = Array.from(gradeSelect.selectedOptions).map(function (option) {
          return option.value;
        });
        const selectedDivisionIds = Array.from(divisionSelect.selectedOptions).map(function (option) {
          return option.value;
        });
        const source = selectedGradeIds.length
          ? initialDivisionOptions.filter(function (option) { return selectedGradeIds.includes(option.gradeId); })
          : initialDivisionOptions;

        divisionSelect.innerHTML = source.map(function (option) {
          return '<option value="' + option.value + '" data-grade-id="' + option.gradeId + '" ' +
            (selectedDivisionIds.includes(option.value) || (isInitialDivisionFilter && option.selected) ? 'selected' : '') + '>' + escapeHtml(option.text) + '</option>';
        }).join('');
        isInitialDivisionFilter = false;

        if (window.jQuery && jQuery.fn.select2) {
          jQuery(divisionSelect).trigger('change.select2');
        }
      }

      function previewBanner(event) {
        const preview = document.getElementById('bannerPreview');
        const file = event.target.files[0];
        preview.innerHTML = '';

        if (!file) {
          return;
        }

        preview.innerHTML = '<div class="file-preview-item"><img src="' + URL.createObjectURL(file) + '" alt="Banner preview"><span>' + escapeHtml(file.name) + '</span></div>';
      }

      function previewAttachments(event) {
        const preview = document.getElementById('attachmentPreview');
        preview.innerHTML = Array.from(event.target.files).map(function (file) {
          const isImage = file.type.startsWith('image/');
          return '<div class="file-preview-item">' +
            (isImage ? '<img src="' + URL.createObjectURL(file) + '" alt="' + escapeHtml(file.name) + '">' : '<i class="fa-solid fa-file"></i>') +
            '<span>' + escapeHtml(file.name) + '</span></div>';
        }).join('');
      }

      function ordinal(number) {
        if ([11, 12, 13].includes(number % 100)) {
          return 'th';
        }
        return { 1: 'st', 2: 'nd', 3: 'rd' }[number % 10] || 'th';
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }
    });
  </script>
@endpush
