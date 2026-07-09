@extends('layouts.app')

@section('title', 'Generate Project Week TimeTable')

@php
  $periodEntries = $entries->where('entry_type', 'period');
  $breakEntries = $entries->whereIn('entry_type', ['short_break', 'lunch_break']);
  $days = $periodEntries->pluck('day')->unique()->values();
  $totalPeriods = max(1, (int) $timetable->total_periods_per_day);
  $createdByName = $projectWeek->creator?->name ?? '-';
  $createdTime = $projectWeek->created_at?->format('d M Y h:i A') ?? '-';
  $oldEntries = collect(old('entries', []))->keyBy(fn($entry) => (int) ($entry['timetable_entry_id'] ?? 0));
  $isGenerated = $projectWeek->entries->isNotEmpty();
@endphp

@push('styles')
  <style>
    .project-period-cell {
      min-width: 220px;
    }

    .project-period-cell.is-selected {
      background: #ecfdf5 !important;
      box-shadow: inset 0 0 0 2px rgba(64, 152, 16, .35);
    }

    .project-period-cell .subject-title {
      color: #111827;
      display: block;
      font-weight: 700;
    }

    .project-period-cell .teacher-meta {
      color: #64748b;
      display: block;
      font-size: 12px;
      line-height: 1.45;
      margin-top: 2px;
    }

    .project-period-check {
      align-items: center;
      display: inline-flex;
      font-size: 12px;
      font-weight: 700;
      gap: 6px;
      margin-top: 8px;
    }

    .project-teacher-field {
      margin-top: 8px;
    }

    .project-teacher-field .form-select {
      font-size: 12px;
      min-width: 190px;
      padding-bottom: 4px;
      padding-top: 4px;
    }

    .break-row td {
      background: #fff7ed;
      color: #9a3412;
      font-weight: 700;
      text-align: center;
    }

    .lunch-row td {
      background: #ecfdf5;
      color: #047857;
      font-weight: 700;
      text-align: center;
    }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Generate Project Week TimeTable</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('project-weeks.index') }}">Project Week</a></li>
        <li class="breadcrumb-item active">Generate</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <form method="POST" action="{{ route('project-weeks.generate.store', $projectWeek) }}" id="projectWeekGenerateForm">
      @csrf

      <div class="main-table-container mb-3">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
          <h5 class="title-w-sec mb-0">Project Week Details</h5>
          <a href="{{ route('project-weeks.index') }}" class="reset-btn text-decoration-none">Back</a>
        </div>
        <hr>
        <div class="row">
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Applicable From</label>
            <input type="text" class="form-control shadow-none" value="{{ $projectWeek->applicable_from?->format('d M Y') ?? '-' }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Applicable To</label>
            <input type="text" class="form-control shadow-none" value="{{ $projectWeek->applicable_to?->format('d M Y') ?? '-' }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Project</label>
            <input type="text" class="form-control shadow-none" value="{{ $projectWeek->project?->project_title ?? '-' }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Grade</label>
            <input type="text" class="form-control shadow-none" value="{{ $projectWeek->grade?->grade ?? '-' }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Division</label>
            <input type="text" class="form-control shadow-none" value="{{ $projectWeek->divisions->pluck('division')->implode(', ') ?: '-' }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Project Periods / Day</label>
            <input type="text" class="form-control shadow-none" value="{{ $projectWeek->total_periods }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Created By</label>
            <input type="text" class="form-control shadow-none" value="{{ $createdByName }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label>Created Time</label>
            <input type="text" class="form-control shadow-none" value="{{ $createdTime }}" disabled>
          </div>
        </div>
      </div>

      <div class="main-table-container mb-3">
        <h5 class="title-w-sec">Time Table</h5>
        <hr>
        @error('entries')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        @if (session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="table-over">
          <table class="align-middle mb-0 table table-bordered w-100">
            <thead>
              <tr>
                <th>Period</th>
                <th>Time</th>
                @foreach ($days as $day)
                  <th>{{ $day }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @for ($period = 1; $period <= $totalPeriods; $period++)
                <tr>
                  <th>Period {{ $period }}</th>
                  @php
                    $periodTimeEntry = $periodEntries->first(fn($entry) => (int) $entry->period_no === $period && $entry->start_time && $entry->end_time);
                  @endphp
                  <td>{{ $periodTimeEntry ? substr((string) $periodTimeEntry->start_time, 0, 5).' - '.substr((string) $periodTimeEntry->end_time, 0, 5) : '-' }}</td>
                  @foreach ($days as $day)
                    @php
                      $entry = $periodEntries->first(fn($item) => $item->day === $day && (int) $item->period_no === $period);
                      $saved = $entry ? $savedProjectEntries->get($entry->id) : null;
                      $oldEntry = $entry ? $oldEntries->get($entry->id) : null;
                      $isChecked = $entry && ($oldEntry || $saved);
                      $fieldIndex = $entry?->id;
                      $selectedTeacherIds = collect($oldEntry['teacher_ids'] ?? array_filter([
                        $oldEntry['teacher_1_id'] ?? $saved?->teacher_1_id,
                        $oldEntry['teacher_2_id'] ?? $saved?->teacher_2_id,
                      ]))->map(fn($id) => (string) $id);
                    @endphp
                    @if ($entry)
                      <td class="project-period-cell {{ $isChecked ? 'is-selected' : '' }}" data-day="{{ $day }}">
                        <span class="subject-title">{{ $entry->subject?->subject_name ?? '-' }}</span>
                        <span class="teacher-meta">
                          @php
                            $sourceTeachers = collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter();
                          @endphp
                          {{ $sourceTeachers->isNotEmpty() ? $sourceTeachers->implode(', ') : '-' }}
                        </span>
                        <label class="project-period-check">
                          <input type="checkbox" class="project-period-toggle" name="entries[{{ $fieldIndex }}][timetable_entry_id]"
                            value="{{ $entry->id }}" @checked($isChecked)>
                          Project Period
                        </label>
                        <div class="project-teacher-field">
                          <select name="entries[{{ $fieldIndex }}][teacher_ids][]" multiple
                            class="form-select form-select-sm shadow-none project-teacher-select"
                            data-required-when-selected="true" @disabled(! $isChecked)>
                            @foreach ($teachers as $teacher)
                              <option value="{{ $teacher->id }}" @selected($selectedTeacherIds->contains((string) $teacher->id))>
                                {{ $teacher->name }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                        @error("entries.$fieldIndex.teacher_ids")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @error("entries.$fieldIndex.teacher_ids.*")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                      </td>
                    @else
                      <td>-</td>
                    @endif
                  @endforeach
                </tr>

                @foreach (['short_break', 'lunch_break'] as $breakType)
                  @php
                    $break = $breakEntries->first(fn($item) => (int) $item->period_no === $period && $item->entry_type === $breakType);
                  @endphp
                  @if ($break)
                    <tr class="{{ $breakType === 'lunch_break' ? 'lunch-row' : 'break-row' }}">
                      <td colspan="{{ $days->count() + 2 }}">
                        {{ $breakType === 'lunch_break' ? 'Lunch Break' : 'Break' }}
                        ({{ $break->duration_minutes }} mins) - {{ substr((string) $break->start_time, 0, 5) }} - {{ substr((string) $break->end_time, 0, 5) }}
                      </td>
                    </tr>
                  @endif
                @endforeach
              @endfor
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-center gap-2 mb-4">
        <div class="btn-flex">
          <a href="{{ route('project-weeks.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" style="width: 195px !important;" class="submit-btn" id="generateProjectWeekBtn" data-loading-text="Saving...">
            {{ $isGenerated ? 'Regenerate Time Table' : 'Generate Time Table' }}
          </button>
        </div>
      </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('projectWeekGenerateForm');
      const submitButton = document.getElementById('generateProjectWeekBtn');
      const maxPerDay = @json((int) $projectWeek->total_periods);

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('.project-teacher-select').select2({
          width: '100%',
          placeholder: 'Teachers',
          maximumSelectionLength: 2
        });
      }

      document.querySelectorAll('.project-period-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
          const cell = checkbox.closest('.project-period-cell');

          if (checkbox.checked && !canSelectForDay(cell.dataset.day, checkbox)) {
            checkbox.checked = false;
            Swal.fire('Project Period Limit', 'You can select only ' + maxPerDay + ' project period(s) for each day.', 'warning');
            return;
          }

          cell.classList.toggle('is-selected', checkbox.checked);
          syncTeacherFields(cell, checkbox.checked);
        });
      });

      form.addEventListener('submit', function (event) {
        const selected = Array.from(document.querySelectorAll('.project-period-toggle:checked'));

        if (!selected.length) {
          event.preventDefault();
          Swal.fire('No Project Periods', 'Select at least one project period before generating.', 'warning');
          return;
        }

        const missingTeacher = selected.find(function (checkbox) {
          const teachers = checkbox.closest('.project-period-cell').querySelector('[data-required-when-selected="true"]');

          return !teachers || !teachers.selectedOptions.length;
        });

        if (missingTeacher) {
          event.preventDefault();
          Swal.fire('Assigned Teachers Required', 'Select at least one teacher for every selected project period.', 'warning');
          return;
        }

        const invalidDay = selected.find(function (checkbox) {
          return !canSelectForDay(checkbox.closest('.project-period-cell').dataset.day, null);
        });

        if (invalidDay) {
          event.preventDefault();
          Swal.fire('Project Period Limit', 'You can select only ' + maxPerDay + ' project period(s) for each day.', 'warning');
          return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          (submitButton.dataset.loadingText || 'Saving...');
      });

      function canSelectForDay(day, currentCheckbox) {
        const checkedForDay = Array.from(document.querySelectorAll('.project-period-cell[data-day="' + cssEscape(day) + '"] .project-period-toggle:checked'));

        if (currentCheckbox && currentCheckbox.checked && !checkedForDay.includes(currentCheckbox)) {
          checkedForDay.push(currentCheckbox);
        }

        return checkedForDay.length <= maxPerDay;
      }

      function syncTeacherFields(cell, isEnabled) {
        cell.querySelectorAll('.project-teacher-select').forEach(function (select) {
          select.disabled = !isEnabled;

          if (!isEnabled) {
            Array.from(select.options).forEach(function (option) {
              option.selected = false;
            });
          }

          if (window.jQuery && jQuery.fn.select2) {
            jQuery(select).trigger('change.select2');
          }
        });
      }

      function cssEscape(value) {
        if (window.CSS && CSS.escape) {
          return CSS.escape(value);
        }

        return String(value).replace(/"/g, '\\"');
      }
    });
  </script>
@endpush
