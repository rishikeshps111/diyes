@extends('layouts.app')

@section('title', 'Generate TimeTable')

@php
  $totalPeriods = max(1, (int) $timetable->total_periods_per_day);
  $savedEntries = old('entries', $entries
    ->where('entry_type', 'period')
    ->map(fn($entry) => [
      'day' => $entry->day,
      'period_no' => $entry->period_no,
      'subject_id' => $entry->subject_id,
      'teacher_ids' => array_values(array_filter([$entry->teacher_1_id, $entry->teacher_2_id])),
      'start_time' => substr((string) $entry->start_time, 0, 5),
      'end_time' => substr((string) $entry->end_time, 0, 5),
    ])
    ->values()
    ->all());
  $initialEntries = [];
  $shortBreakPeriods = $entries->where('entry_type', 'short_break')->pluck('period_no')->unique()->values();
  $lunchBreakPeriod = $entries->where('entry_type', 'lunch_break')->pluck('period_no')->unique()->first();
  $previewDays = old('working_days', []);
  $shortBreakAfterPeriod = old('short_break_after_period', $shortBreakPeriods->get(0));
  $lunchBreakAfterPeriod = old('lunch_break_after_period', $lunchBreakPeriod);
  $shortBreakAfterLunchPeriod = old('short_break_after_lunch_period', $shortBreakPeriods->get(1));
  $createdByName = $timetable->preparedBy?->name ?? '-';
  $createdTime = $timetable->prepared_at?->format('d M Y h:i A') ?? '-';
  $isGenerated = $entries->isNotEmpty();
@endphp

@push('styles')
  <style>
    .timetable-generate-card {
      background: #fff;
      border: 1px solid #edf0f5;
      border-radius: 8px;
      box-shadow: 0 10px 26px rgba(31, 45, 61, 0.06);
      padding: 20px;
    }

    .generate-section-title {
      align-items: center;
      display: flex;
      gap: 12px;
      justify-content: space-between;
      margin-bottom: 14px;
    }

    .generate-section-title h5 {
      color: #09650d;
      font-size: 17px;
      font-weight: 600;
      margin: 0;
    }

    .weekday-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .weekday-pills label {
      align-items: center;
      background: #f8fafc;
      border: 1px solid #dce3ec;
      border-radius: 999px;
      color: #334155;
      cursor: pointer;
      display: inline-flex;
      font-size: 13px;
      font-weight: 600;
      gap: 7px;
      padding: 8px 12px;
    }

    .entry-table select,
    .entry-table input {
      min-width: 130px;
    }

    .teacher-select {
      min-width: 230px !important;
    }

    .duration-field {
      background: #f8fafc !important;
      font-weight: 700;
      text-align: center;
    }

    .row-action-btn {
      align-items: center;
      border: 0;
      border-radius: 7px;
      display: inline-flex;
      height: 34px;
      justify-content: center;
      width: 34px;
    }

    .duplicate-entry-btn {
      background: #e8f2ff;
      color: #0d6efd;
    }

    .delete-entry-btn {
      background: #ffecec;
      color: #dc3545;
    }

    .preview-table tr.break td {
      background: #fff7ed;
      color: #9a3412;
      font-weight: 700;
      text-align: center;
    }

    .entry-table tr.lunch td,
    .preview-table tr.lunch td {
      background: #ecfdf5;
      color: #047857;
      font-weight: 700;
      text-align: center;
    }

    .preview-table th {
      background: #f8fafc;
      color: #334155;
      font-size: 13px;
      white-space: nowrap;
    }

    .preview-cell-title {
      color: #111827;
      display: block;
      font-weight: 700;
    }

    .preview-cell-meta {
      color: #64748b;
      display: block;
      font-size: 12px;
      line-height: 1.45;
      margin-top: 2px;
    }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Generate TimeTable</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('timetables.index') }}">Regular Timetable</a></li>
        <li class="breadcrumb-item active">Generate</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <form method="POST" action="{{ route('timetables.generate.store', $timetable) }}" id="generateTimetableForm">
      @csrf
      <div id="savedEntriesFields"></div>

      <div class="timetable-generate-card mb-3">
        <div class="generate-section-title">
          <h5>Break Details</h5>
          <a href="{{ route('timetables.index') }}" class="reset-btn text-decoration-none">Back</a>
        </div>
        <hr>
        <div class="row">
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="short_break_after_period">Short Break After Period <span class="text-danger">*</span></label>
            <select name="short_break_after_period" id="short_break_after_period"
              class="form-select shadow-none @error('short_break_after_period') is-invalid @enderror" required>
              <option value="">--- Select ---</option>
              @for ($period = 1; $period <= $totalPeriods; $period++)
                <option value="{{ $period }}" @selected((string) $shortBreakAfterPeriod === (string) $period)>Period
                  {{ $period }}
                </option>
              @endfor
            </select>
            @error('short_break_after_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="lunch_break_after_period">Lunch Break After Period <span class="text-danger">*</span></label>
            <select name="lunch_break_after_period" id="lunch_break_after_period"
              class="form-select shadow-none @error('lunch_break_after_period') is-invalid @enderror" required>
              <option value="">--- Select ---</option>
              @for ($period = 1; $period <= $totalPeriods; $period++)
                <option value="{{ $period }}" @selected((string) $lunchBreakAfterPeriod === (string) $period)>Period
                  {{ $period }}
                </option>
              @endfor
            </select>
            @error('lunch_break_after_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="short_break_after_lunch_period">Short Break After Lunch After Period <span
                class="text-danger">*</span></label>
            <select name="short_break_after_lunch_period" id="short_break_after_lunch_period"
              class="form-select shadow-none @error('short_break_after_lunch_period') is-invalid @enderror" required>
              <option value="">--- Select ---</option>
              @for ($period = 1; $period <= $totalPeriods; $period++)
                <option value="{{ $period }}" @selected((string) $shortBreakAfterLunchPeriod === (string) $period)>Period
                  {{ $period }}
                </option>
              @endfor
            </select>
            @error('short_break_after_lunch_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="created_by">Created By</label>
            <input type="text" id="created_by" class="form-control shadow-none" value="{{ $createdByName }}" disabled>
          </div>
          <div class="col-lg-4 o-f-inp mb-3">
            <label for="created_time">Created Time</label>
            <input type="text" id="created_time" class="form-control shadow-none" value="{{ $createdTime }}" disabled>
          </div>
        </div>
      </div>

      <div class="timetable-generate-card mb-3">
        <div class="generate-section-title">
          <h5>Select Working Days</h5>
        </div>
        <hr>
        <div class="weekday-pills" id="workingDays">
          @foreach ($days as $day)
            <label>
              <input type="checkbox" name="working_days[]" value="{{ $day }}" @checked(in_array($day, $previewDays, true))>
              {{ $day }}
            </label>
          @endforeach
        </div>
        @error('working_days')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
      </div>

      <div class="timetable-generate-card mb-3">
        <div class="generate-section-title">
          <h5>Time Table Details</h5>
        </div>
        <hr>
        @error('entries')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        <div class="table-over">
          <table class="align-middle mb-0 table table-custom w-100 entry-table">
            <thead>
              <tr>
                <th>SL No</th>
                <th>Period</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="entryRows">
              @foreach ($initialEntries as $index => $entry)
                <tr class="entry-row" data-period="{{ $entry['period_no'] ?? $loop->iteration }}">
                  <td class="row-number">{{ $loop->iteration }}</td>
                  <td>
                    <select class="form-select shadow-none entry-input period-select" data-field="period_no">
                      @for ($period = 1; $period <= $totalPeriods; $period++)
                        <option value="{{ $period }}" @selected((string) ($entry['period_no'] ?? '') === (string) $period)>
                          Period {{ $period }}</option>
                      @endfor
                    </select>
                  </td>
                  <td>
                    <select class="form-select shadow-none entry-input subject-select" data-field="subject_id">
                      <option value="">Select</option>
                      @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(($entry['subject_id'] ?? '') == $subject->id)>
                          {{ $subject->subject_name }}
                        </option>
                      @endforeach
                    </select>
                    @error("entries.{$index}.subject_id")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                  </td>
                  <td>
                    <select class="form-select shadow-none entry-input teacher-select" data-field="teacher_ids" multiple>
                      @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(in_array($teacher->id, $entry['teacher_ids'] ?? [], false))>
                          {{ $teacher->name }}
                        </option>
                      @endforeach
                    </select>
                    @error("entries.{$index}.teacher_ids")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                  </td>
                  <td>
                    <input type="time" class="form-control shadow-none entry-input time-input" data-field="start_time"
                      value="{{ $entry['start_time'] ?? '' }}">
                    @error("entries.{$index}.start_time")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                  </td>
                  <td>
                    <input type="time" class="form-control shadow-none entry-input time-input" data-field="end_time"
                      value="{{ $entry['end_time'] ?? '' }}">
                    @error("entries.{$index}.end_time")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                  </td>
                  <td>
                    <input type="text" class="form-control shadow-none duration-field" value="" readonly>
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                      <button type="button" class="row-action-btn duplicate-entry-btn" title="Duplicate">
                        <i class="fa-regular fa-copy"></i>
                      </button>
                      <button type="button" class="row-action-btn delete-entry-btn" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="button" class="add-btn border-0" id="addPeriodRow">
            <i class="fa-solid fa-plus me-1"></i> Add Row
          </button>
          <button type="button" class="search-btn border-0" id="refreshPreview">
            Save
          </button>
          <button type="button" class="reset-btn border-0" id="fullReset">
            Full Reset
          </button>
        </div>
      </div>

      <div class="timetable-generate-card mb-3">
        <div class="generate-section-title">
          <h5>Preview Time Table</h5>
        </div>
        <hr>
        <div class="table-over">
          <table class="align-middle mb-0 table table-bordered preview-table w-100">
            <thead id="previewHead"></thead>
            <tbody id="previewBody"></tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-center gap-2 mb-4">
        <div class="btn-flex">
          <a href="{{ route('timetables.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" class="submit-btn timetable-submit-btn" data-loading-text="Saving..."
            style="width: 195px !important;">{{ $isGenerated ? 'Regenerate Time Table' : 'Generate Time Table' }}</button>
        </div>
      </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('generateTimetableForm');
      const entryRows = document.getElementById('entryRows');
      const savedEntriesFields = document.getElementById('savedEntriesFields');
      const addPeriodRow = document.getElementById('addPeriodRow');
      const refreshPreview = document.getElementById('refreshPreview');
      const fullReset = document.getElementById('fullReset');
      const previewHead = document.getElementById('previewHead');
      const previewBody = document.getElementById('previewBody');
      const totalPeriods = @json($totalPeriods);
      const breakMinutes = @json((int) $timetable->short_break_minutes);
      const lunchMinutes = @json((int) $timetable->lunch_break_minutes);
      const shortBreakAfterLunchMinutes = @json((int) $timetable->short_break_after_lunch_minutes);
      const subjects = @json($subjects->map(fn($subject) => ['id' => $subject->id, 'name' => $subject->subject_name, 'color' => $subject->color ?? '#ffffff'])->values());
      const teachers = @json($teachers->map(fn($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->values());
      let stagedEntries = @json($savedEntries);

      function rowInputs(row) {
        return Array.from(row.querySelectorAll('[data-field]'));
      }

      function selectedOptions(select) {
        return Array.from(select.selectedOptions || []);
      }

      function getSelectText(select) {
        return selectedOptions(select).length ? selectedOptions(select)[0].text.trim() : '';
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function calculateDuration(start, end) {
        if (!start || !end) {
          return '';
        }

        const [startHour, startMinute] = start.split(':').map(Number);
        const [endHour, endMinute] = end.split(':').map(Number);
        const duration = (endHour * 60 + endMinute) - (startHour * 60 + startMinute);

        return duration > 0 ? duration : '';
      }

      function addMinutes(time, minutes) {
        if (!time || !minutes) {
          return '';
        }

        const [hour, minute] = time.split(':').map(Number);
        const total = hour * 60 + minute + minutes;
        const nextHour = String(Math.floor(total / 60) % 24).padStart(2, '0');
        const nextMinute = String(total % 60).padStart(2, '0');

        return nextHour + ':' + nextMinute;
      }

      function syncDuration(row) {
        const start = row.querySelector('[data-field="start_time"]').value;
        const end = row.querySelector('[data-field="end_time"]').value;
        const duration = calculateDuration(start, end);

        row.querySelector('.duration-field').value = duration ? duration + ' Min' : '';
      }

      function subjectOptions(selectedValue) {
        return '<option value="">Select</option>' + subjects.map(function (subject) {
          return '<option value="' + subject.id + '"' + (String(selectedValue || '') === String(subject.id) ? ' selected' : '') + '>' + subject.name + '</option>';
        }).join('');
      }

      function teacherOptions(selectedValues) {
        const selected = (selectedValues || []).map(String);

        return teachers.map(function (teacher) {
          return '<option value="' + teacher.id + '"' + (selected.includes(String(teacher.id)) ? ' selected' : '') + '>' + teacher.name + '</option>';
        }).join('');
      }

      function periodOptions(selectedValue) {
        let options = '';

        for (let period = 1; period <= totalPeriods; period++) {
          options += '<option value="' + period + '"' + (String(selectedValue || '') === String(period) ? ' selected' : '') + '>Period ' + period + '</option>';
        }

        return options;
      }

      function selectedWorkingDays() {
        return Array.from(document.querySelectorAll('#workingDays input:checked')).map(function (input) {
          return input.value;
        });
      }

      function subjectName(subjectId) {
        const subject = subjects.find(function (item) {
          return String(item.id) === String(subjectId);
        });

        return subject ? subject.name : '';
      }

      function subjectColor(subjectId) {
        const subject = subjects.find(function (item) {
          return String(item.id) === String(subjectId);
        });

        return subject ? subject.color : '#ffffff';
      }

      function teacherNames(teacherIds) {
        return (teacherIds || []).map(function (teacherId) {
          const teacher = teachers.find(function (item) {
            return String(item.id) === String(teacherId);
          });

          return teacher ? teacher.name : '';
        }).filter(Boolean);
      }

      function normalizeStagedEntries() {
        stagedEntries = stagedEntries.map(function (entry) {
          return {
            day: entry.day,
            period: Number(entry.period || entry.period_no || 0),
            subjectId: entry.subjectId || entry.subject_id || '',
            subject: entry.subject || subjectName(entry.subjectId || entry.subject_id),
            color: entry.color || subjectColor(entry.subjectId || entry.subject_id),
            teacherIds: (entry.teacherIds || entry.teacher_ids || []).map(String),
            teachers: entry.teachers || teacherNames(entry.teacherIds || entry.teacher_ids || []),
            startTime: entry.startTime || entry.start_time || '',
            endTime: entry.endTime || entry.end_time || '',
            duration: entry.duration || ''
          };
        }).filter(function (entry) {
          return entry.day && entry.period;
        });
      }

      function collectDraftEntries() {
        return Array.from(entryRows.querySelectorAll('.entry-row')).map(function (row) {
          const subject = row.querySelector('[data-field="subject_id"]');
          const teacherSelect = row.querySelector('[data-field="teacher_ids"]');

          return {
            period: Number(row.querySelector('[data-field="period_no"]').value || 0),
            subject: getSelectText(subject) === 'Select' ? '' : getSelectText(subject),
            subjectId: subject.value,
            color: subjectColor(subject.value),
            teachers: selectedOptions(teacherSelect).map(function (option) {
              return option.text.trim();
            }),
            teacherIds: selectedOptions(teacherSelect).map(function (option) {
              return option.value;
            }),
            startTime: row.querySelector('[data-field="start_time"]').value,
            endTime: row.querySelector('[data-field="end_time"]').value,
            duration: row.querySelector('.duration-field').value,
          };
        }).filter(function (entry) {
          return entry.period;
        });
      }

      function usedPeriods() {
        return new Set(collectDraftEntries().map(function (entry) {
          return String(entry.period);
        }));
      }

      function nextAvailablePeriod(preferredPeriod) {
        const used = usedPeriods();

        if (preferredPeriod && preferredPeriod <= totalPeriods && !used.has(String(preferredPeriod))) {
          return preferredPeriod;
        }

        for (let period = 1; period <= totalPeriods; period++) {
          if (!used.has(String(period))) {
            return period;
          }
        }

        return null;
      }

      function breakLabel(type, afterPeriod, entries) {
        if (!afterPeriod) {
          return '';
        }

        const periodEntry = entries.find(function (entry) {
          return entry.period === Number(afterPeriod) && entry.endTime;
        });
        const minutes = type === 'lunch' ? lunchMinutes : (type === 'break_after_lunch' ? shortBreakAfterLunchMinutes : breakMinutes);
        const title = type === 'lunch' ? 'Lunch Break' : 'Break';

        if (!periodEntry) {
          return title + ' (' + minutes + ' mins)';
        }

        const endTime = addMinutes(periodEntry.endTime, minutes);

        return title + ' (' + minutes + ' mins) - ' + periodEntry.endTime + ' - ' + endTime;
      }

      function reindexRows() {
        Array.from(entryRows.querySelectorAll('.entry-row')).forEach(function (row, index) {
          row.querySelector('.row-number').textContent = index + 1;

          rowInputs(row).forEach(function (input) {
            input.removeAttribute('name');
          });

          syncDuration(row);
        });
      }

      function renderHiddenEntries() {
        savedEntriesFields.innerHTML = '';

        stagedEntries.forEach(function (entry, index) {
          const fields = {
            day: entry.day,
            period_no: entry.period,
            subject_id: entry.subjectId,
            start_time: entry.startTime,
            end_time: entry.endTime
          };

          Object.keys(fields).forEach(function (field) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'entries[' + index + '][' + field + ']';
            input.value = fields[field];
            savedEntriesFields.appendChild(input);
          });

          entry.teacherIds.forEach(function (teacherId) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'entries[' + index + '][teacher_ids][]';
            input.value = teacherId;
            savedEntriesFields.appendChild(input);
          });
        });
      }

      function updatePreview() {
        const previewDays = Array.from(new Set(stagedEntries.map(function (entry) {
          return entry.day;
        })));
        const entries = stagedEntries;
        const shortBreakAfter = document.getElementById('short_break_after_period').value;
        const lunchBreakAfter = document.getElementById('lunch_break_after_period').value;
        const shortBreakAfterLunch = document.getElementById('short_break_after_lunch_period').value;
        const colspan = previewDays.length + 2;

        previewHead.innerHTML = '<tr><th>Period</th><th>Time</th>' + previewDays.map(function (day) {
          return '<th>' + day + '</th>';
        }).join('') + '</tr>';

        if (!previewDays.length || !entries.length) {
          previewBody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center text-muted">Select working days and fill timetable details to see preview.</td></tr>';
          return;
        }

        let html = '';

        for (let period = 1; period <= totalPeriods; period++) {
          const entry = entries.find(function (item) {
            return item.period === period;
          });
          const timeText = entry && entry.startTime && entry.endTime ? entry.startTime + ' - ' + entry.endTime : '-';
          html += '<tr><th>Period ' + period + '</th><td>' + timeText + '</td>' +
            previewDays.map(function (day) {
              const dayEntry = entries.find(function (item) {
                return item.period === period && item.day === day;
              });

              if (!dayEntry) {
                return '<td>-</td>';
              }

              const dayTeachers = dayEntry.teachers.map(function (teacher, index) {
                return 'T' + (index + 1) + ': ' + teacher;
              }).join('<br>');

              return '<td style="background-color: ' + escapeHtml(dayEntry.color || '#ffffff') + ';"><span class="preview-cell-title">' + (dayEntry.subject || 'Subject not selected') + '</span><span class="preview-cell-meta">' + (dayTeachers || 'Teacher not selected') + '</span></td>';
            }).join('') + '</tr>';

          if (String(shortBreakAfter) === String(period)) {
            const label = breakLabel('break', shortBreakAfter, entries);
            html += label ? '<tr class="break"><td colspan="' + colspan + '">' + label + '</td></tr>' : '';
          }

          if (String(lunchBreakAfter) === String(period)) {
            const label = breakLabel('lunch', lunchBreakAfter, entries);
            html += label ? '<tr class="lunch"><td colspan="' + colspan + '">' + label + '</td></tr>' : '';
          }

          if (String(shortBreakAfterLunch) === String(period)) {
            const label = breakLabel('break_after_lunch', shortBreakAfterLunch, entries);
            html += label ? '<tr class="break"><td colspan="' + colspan + '">' + label + '</td></tr>' : '';
          }
        }

        previewBody.innerHTML = html;
      }

      function createRow(data) {
        const row = document.createElement('tr');
        row.className = 'entry-row';
        row.innerHTML =
          '<td class="row-number"></td>' +
          '<td><select class="form-select shadow-none entry-input period-select" data-field="period_no">' + periodOptions(data.period_no) + '</select></td>' +
          '<td><select class="form-select shadow-none entry-input subject-select" data-field="subject_id">' + subjectOptions(data.subject_id) + '</select></td>' +
          '<td><select class="form-select shadow-none entry-input teacher-select" data-field="teacher_ids" multiple>' + teacherOptions(data.teacher_ids) + '</select></td>' +
          '<td><input type="time" class="form-control shadow-none entry-input time-input" data-field="start_time" value="' + (data.start_time || '') + '"></td>' +
          '<td><input type="time" class="form-control shadow-none entry-input time-input" data-field="end_time" value="' + (data.end_time || '') + '"></td>' +
          '<td><input type="text" class="form-control shadow-none duration-field" value="" readonly></td>' +
          '<td><div class="d-flex gap-2"><button type="button" class="row-action-btn duplicate-entry-btn" title="Duplicate"><i class="fa-regular fa-copy"></i></button><button type="button" class="row-action-btn delete-entry-btn" title="Delete"><i class="fa-solid fa-trash"></i></button></div></td>';

        bindRow(row);

        return row;
      }

      function initTeacherSelect(row) {
        if (window.jQuery && jQuery.fn.select2) {
          jQuery(row.querySelector('.teacher-select')).select2({
            width: '100%',
            placeholder: 'Select teacher',
            maximumSelectionLength: 2
          });
        }
      }

      function addRow(data) {
        const row = createRow(data);
        entryRows.appendChild(row);
        initTeacherSelect(row);
        reindexRows();
      }

      function clearDraft() {
        if (window.jQuery && jQuery.fn.select2) {
          jQuery(entryRows.querySelectorAll('.teacher-select.select2-hidden-accessible')).select2('destroy');
        }

        entryRows.innerHTML = '';
        document.querySelectorAll('#workingDays input').forEach(function (input) {
          input.checked = false;
        });
      }

      function validateDraft() {
        if (
          !document.getElementById('short_break_after_period').value ||
          !document.getElementById('lunch_break_after_period').value ||
          !document.getElementById('short_break_after_lunch_period').value
        ) {
          Swal.fire('Break Details Required', 'Select short break, lunch break, and short break after lunch periods first.', 'warning');
          return false;
        }

        const breakPeriods = [
          document.getElementById('short_break_after_period').value,
          document.getElementById('lunch_break_after_period').value,
          document.getElementById('short_break_after_lunch_period').value
        ];

        if ((new Set(breakPeriods)).size !== breakPeriods.length) {
          Swal.fire('Duplicate Break Period', 'Choose different periods for short break, lunch break, and short break after lunch.', 'warning');
          return false;
        }

        if (!selectedWorkingDays().length) {
          Swal.fire('Working Days Required', 'Select at least one working day.', 'warning');
          return false;
        }

        if (!entryRows.querySelectorAll('.entry-row').length) {
          Swal.fire('No Rows Added', 'Add at least one timetable detail row.', 'warning');
          return false;
        }

        const invalidEntry = collectDraftEntries().find(function (entry) {
          return !entry.period || !entry.subjectId || !entry.teacherIds.length || !entry.startTime || !entry.endTime || !calculateDuration(entry.startTime, entry.endTime);
        });

        if (invalidEntry) {
          Swal.fire('Incomplete Details', 'Fill subject, at least one teacher, start time, and valid end time for every row.', 'warning');
          return false;
        }

        return true;
      }

      function saveDraftToPreview() {
        if (!validateDraft()) {
          return;
        }

        const days = selectedWorkingDays();
        const draftEntries = collectDraftEntries();
        const draftPeriods = new Set();

        if (draftEntries.some(function (entry) {
          if (draftPeriods.has(String(entry.period))) {
            return true;
          }

          draftPeriods.add(String(entry.period));
          return false;
        })) {
          Swal.fire('Duplicate Period', 'Each period can be added only once before saving.', 'warning');
          return;
        }

        const nextEntries = stagedEntries.filter(function (entry) {
          return !days.includes(entry.day);
        });

        days.forEach(function (day) {
          draftEntries.forEach(function (entry) {
            nextEntries.push({
              ...entry,
              day: day,
            });
          });
        });

        stagedEntries = nextEntries;
        renderHiddenEntries();
        updatePreview();
        clearDraft();
      }

      function duplicateRow(row) {
        const nextPeriod = nextAvailablePeriod(Number(row.querySelector('[data-field="period_no"]').value) + 1);

        if (!nextPeriod) {
          Swal.fire('No Period Available', 'All timetable periods are already added.', 'info');
          return;
        }

        addRow({
          period_no: nextPeriod,
          subject_id: row.querySelector('[data-field="subject_id"]').value,
          teacher_ids: selectedOptions(row.querySelector('[data-field="teacher_ids"]')).map(function (option) {
            return option.value;
          }),
          start_time: row.querySelector('[data-field="start_time"]').value,
          end_time: row.querySelector('[data-field="end_time"]').value
        });
      }

      function bindRow(row) {
        rowInputs(row).forEach(function (input) {
          input.addEventListener('change', function () {
            if (input.classList.contains('teacher-select') && selectedOptions(input).length > 2) {
              selectedOptions(input)[selectedOptions(input).length - 1].selected = false;
              Swal.fire('Maximum Teachers', 'You can select up to two teachers for one period.', 'warning');
            }

            if (input.classList.contains('time-input')) {
              syncDuration(row);
            }

            reindexRows();
          });

          input.addEventListener('input', function () {
            if (input.classList.contains('time-input')) {
              syncDuration(row);
            }
          });
        });

        row.querySelector('.duplicate-entry-btn').addEventListener('click', function () {
          duplicateRow(row);
        });

        row.querySelector('.delete-entry-btn').addEventListener('click', function () {
          row.remove();
          reindexRows();
        });
      }

      Array.from(entryRows.querySelectorAll('.entry-row')).forEach(bindRow);

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('.teacher-select').select2({
          width: '100%',
          placeholder: 'Select teacher',
          maximumSelectionLength: 2
        });
      }

      addPeriodRow.addEventListener('click', function () {
        const period = nextAvailablePeriod();

        if (!period) {
          Swal.fire('No Period Available', 'All timetable periods are already added.', 'info');
          return;
        }

        addRow({
          period_no: period,
          subject_id: '',
          teacher_ids: [],
          start_time: '',
          end_time: ''
        });
      });

      refreshPreview.addEventListener('click', saveDraftToPreview);

      fullReset.addEventListener('click', function () {
        stagedEntries = [];
        renderHiddenEntries();
        clearDraft();
        previewHead.innerHTML = '';
        previewBody.innerHTML = '<tr><td class="text-center text-muted">Fill timetable details and click Save.</td></tr>';

        document.getElementById('short_break_after_period').value = '';
        document.getElementById('lunch_break_after_period').value = '';
        document.getElementById('short_break_after_lunch_period').value = '';

        if (window.jQuery && jQuery.fn.select2) {
          jQuery('#short_break_after_period, #lunch_break_after_period, #short_break_after_lunch_period').val(null).trigger('change');
        }
      });

      form.addEventListener('submit', function (event) {
        if (entryRows.querySelectorAll('.entry-row').length) {
          event.preventDefault();
          Swal.fire('Unsaved Details', 'Click Save to add the current timetable details to preview before generating.', 'warning');
          return;
        }

        if (!stagedEntries.length) {
          event.preventDefault();
          Swal.fire('No Saved Preview', 'Click Save after filling timetable details before generating.', 'warning');
          return;
        }

        renderHiddenEntries();

        const submitButton = form.querySelector('.timetable-submit-btn');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          (submitButton.dataset.loadingText || 'Saving...');
      });

      normalizeStagedEntries();
      renderHiddenEntries();
      reindexRows();

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#short_break_after_period, #lunch_break_after_period, #short_break_after_lunch_period').select2({
          width: '100%',
          placeholder: '--- Select ---',
          allowClear: true
        });
      }

      if (stagedEntries.length) {
        updatePreview();
      } else {
        previewHead.innerHTML = '';
        previewBody.innerHTML = '<tr><td class="text-center text-muted">Fill timetable details and click Save.</td></tr>';
      }
    });
  </script>
@endpush