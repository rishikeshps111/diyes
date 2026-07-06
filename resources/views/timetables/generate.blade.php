@extends('layouts.app')

@section('title', 'Generate TimeTable')

@php
  $storedEntries = $entries->map(fn($entry) => [
    'day' => $entry->day,
    'period_no' => $entry->period_no,
    'entry_type' => $entry->entry_type,
    'subject_id' => $entry->subject_id,
    'teacher_1_id' => $entry->teacher_1_id,
    'teacher_2_id' => $entry->teacher_2_id,
    'start_time' => substr((string) $entry->start_time, 0, 5),
    'end_time' => substr((string) $entry->end_time, 0, 5),
  ])->values()->all();

  $initialEntries = old('entries', $storedEntries ?: [
    [
      'day' => 'Monday',
      'period_no' => 1,
      'entry_type' => 'period',
      'subject_id' => '',
      'teacher_1_id' => '',
      'teacher_2_id' => '',
      'start_time' => '',
      'end_time' => '',
    ]
  ]);
  $previewDays = old('working_days', collect($initialEntries)->pluck('day')->filter()->unique()->values()->all() ?: array_slice($days, 0, 5));
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

    .timetable-summary {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    }

    .summary-item {
      background: #f8fafc;
      border: 1px solid #edf0f5;
      border-radius: 8px;
      padding: 12px 14px;
    }

    .summary-item span {
      color: #64748b;
      display: block;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 5px;
      text-transform: uppercase;
    }

    .summary-item strong {
      color: #1f2937;
      font-size: 14px;
      font-weight: 700;
    }

    .generate-section-title {
      align-items: center;
      display: flex;
      justify-content: space-between;
      gap: 12px;
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
      min-width: 120px;
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

    .copy-entry-btn {
      background: #e8f2ff;
      color: #0d6efd;
    }

    .delete-entry-btn {
      background: #ffecec;
      color: #dc3545;
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


      <div class="timetable-generate-card mb-3">
        <div class="generate-section-title">
          <h5>Select Working Days</h5>
          <a href="{{ route('timetables.index') }}" class="reset-btn text-decoration-none">Back</a>
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
      </div>

      <div class="timetable-generate-card mb-3">
        <div class="generate-section-title">
          <h5>Select Time Table Details</h5>
          <button type="button" class="add-btn border-0" id="addEntryRow">
            <i class="fa-solid fa-plus me-1"></i> Add Row
          </button>
        </div>
        <hr>

        <div class="table-over">
          <table class="align-middle mb-0 table table-custom w-100 entry-table">
            <thead>
              <tr>
                <th>SL No</th>
                <th>Day</th>
                <th>Period</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Teacher 1</th>
                <th>Teacher 2</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="entryRows">
              @foreach ($initialEntries as $index => $entry)
                <tr class="entry-row">
                  <td class="row-number">{{ $loop->iteration }}</td>
                  <td>
                    <select class="form-select shadow-none entry-input" data-field="day" name="entries[{{ $index }}][day]"
                      required>
                      @foreach ($days as $day)
                        <option value="{{ $day }}" @selected(($entry['day'] ?? '') === $day)>{{ $day }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="number" min="1" class="form-control shadow-none entry-input" data-field="period_no"
                      name="entries[{{ $index }}][period_no]" value="{{ $entry['period_no'] ?? 1 }}" required>
                  </td>
                  <td>
                    <select class="form-select shadow-none entry-input entry-type-select" data-field="entry_type"
                      name="entries[{{ $index }}][entry_type]" required>
                      @foreach ($entryTypes as $typeValue => $typeLabel)
                        <option value="{{ $typeValue }}" @selected(($entry['entry_type'] ?? 'period') === $typeValue)>
                          {{ $typeLabel }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select class="form-select shadow-none entry-input period-only subject-select" data-field="subject_id"
                      name="entries[{{ $index }}][subject_id]">
                      <option value="">Select</option>
                      @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(($entry['subject_id'] ?? '') == $subject->id)>
                          {{ $subject->subject_name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select class="form-select shadow-none entry-input period-only teacher-one-select"
                      data-field="teacher_1_id" name="entries[{{ $index }}][teacher_1_id]">
                      <option value="">Select</option>
                      @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(($entry['teacher_1_id'] ?? '') == $teacher->id)>
                          {{ $teacher->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select class="form-select shadow-none entry-input period-only teacher-two-select"
                      data-field="teacher_2_id" name="entries[{{ $index }}][teacher_2_id]">
                      <option value="">Select</option>
                      @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(($entry['teacher_2_id'] ?? '') == $teacher->id)>
                          {{ $teacher->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="time" class="form-control shadow-none entry-input time-input" data-field="start_time"
                      name="entries[{{ $index }}][start_time]" value="{{ $entry['start_time'] ?? '' }}" required>
                  </td>
                  <td>
                    <input type="time" class="form-control shadow-none entry-input time-input" data-field="end_time"
                      name="entries[{{ $index }}][end_time]" value="{{ $entry['end_time'] ?? '' }}" required>
                  </td>
                  <td>
                    <input type="text" class="form-control shadow-none duration-field" value="" readonly>
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                      <button type="button" class="row-action-btn copy-entry-btn" title="Copy">
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
          <button type="submit" class="submit-btn timetable-submit-btn" style="width: 185px !important;">Generate Time
            Table</button>
        </div>
      </div>
    </form>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const entryRows = document.getElementById('entryRows');
      const addEntryRow = document.getElementById('addEntryRow');
      const previewHead = document.getElementById('previewHead');
      const previewBody = document.getElementById('previewBody');
      const entryTypeLabels = @json($entryTypes);

      function rowInputs(row) {
        return Array.from(row.querySelectorAll('[data-field]'));
      }

      function getSelectText(select) {
        return select.selectedOptions.length ? select.selectedOptions[0].text.trim() : '';
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

      function syncDuration(row) {
        const start = row.querySelector('[data-field="start_time"]').value;
        const end = row.querySelector('[data-field="end_time"]').value;
        const duration = calculateDuration(start, end);

        row.querySelector('.duration-field').value = duration ? duration + ' Min' : '';
      }

      function syncPeriodFields(row) {
        const isPeriod = row.querySelector('[data-field="entry_type"]').value === 'period';

        row.querySelectorAll('.period-only').forEach(function (field) {
          field.disabled = !isPeriod;

          if (!isPeriod) {
            field.value = '';
          }
        });
      }

      function reindexRows() {
        Array.from(entryRows.querySelectorAll('.entry-row')).forEach(function (row, index) {
          row.querySelector('.row-number').textContent = index + 1;

          rowInputs(row).forEach(function (input) {
            input.name = 'entries[' + index + '][' + input.dataset.field + ']';
          });

          syncPeriodFields(row);
          syncDuration(row);
        });
      }

      function bindRow(row) {
        rowInputs(row).forEach(function (input) {
          input.addEventListener('change', function () {
            if (input.classList.contains('entry-type-select')) {
              syncPeriodFields(row);
            }

            if (input.classList.contains('time-input')) {
              syncDuration(row);
            }

            updatePreview();
          });

          input.addEventListener('input', function () {
            if (input.classList.contains('time-input')) {
              syncDuration(row);
            }

            updatePreview();
          });
        });

        row.querySelector('.copy-entry-btn').addEventListener('click', function () {
          const values = {};

          rowInputs(row).forEach(function (input) {
            values[input.dataset.field] = input.value;
          });

          const clone = row.cloneNode(true);
          row.after(clone);

          rowInputs(clone).forEach(function (input) {
            input.value = values[input.dataset.field] || '';
          });

          bindRow(clone);
          reindexRows();
          updatePreview();
        });

        row.querySelector('.delete-entry-btn').addEventListener('click', function () {
          if (entryRows.querySelectorAll('.entry-row').length === 1) {
            rowInputs(row).forEach(function (input) {
              if (input.dataset.field === 'period_no') {
                input.value = 1;
              } else if (input.dataset.field === 'entry_type') {
                input.value = 'period';
              } else if (input.dataset.field === 'day') {
                input.value = 'Monday';
              } else {
                input.value = '';
              }
            });
          } else {
            row.remove();
          }

          reindexRows();
          updatePreview();
        });
      }

      function selectedPreviewDays() {
        const checkedDays = Array.from(document.querySelectorAll('#workingDays input:checked')).map(function (input) {
          return input.value;
        });

        if (checkedDays.length) {
          return checkedDays;
        }

        return Array.from(new Set(Array.from(entryRows.querySelectorAll('[data-field="day"]')).map(function (input) {
          return input.value;
        })));
      }

      function collectEntries() {
        return Array.from(entryRows.querySelectorAll('.entry-row')).map(function (row) {
          const subject = row.querySelector('[data-field="subject_id"]');
          const teacherOne = row.querySelector('[data-field="teacher_1_id"]');
          const teacherTwo = row.querySelector('[data-field="teacher_2_id"]');
          const entryType = row.querySelector('[data-field="entry_type"]').value;

          return {
            day: row.querySelector('[data-field="day"]').value,
            period: Number(row.querySelector('[data-field="period_no"]').value || 0),
            entryType: entryType,
            typeLabel: entryTypeLabels[entryType] || 'Period',
            subject: getSelectText(subject) === 'Select' ? '' : getSelectText(subject),
            teacherOne: getSelectText(teacherOne) === 'Select' ? '' : getSelectText(teacherOne),
            teacherTwo: getSelectText(teacherTwo) === 'Select' ? '' : getSelectText(teacherTwo),
            startTime: row.querySelector('[data-field="start_time"]').value,
            endTime: row.querySelector('[data-field="end_time"]').value,
            duration: row.querySelector('.duration-field').value,
          };
        }).filter(function (entry) {
          return entry.day && entry.period;
        });
      }

      function updatePreview() {
        const days = selectedPreviewDays();
        const entries = collectEntries();
        const periods = Array.from(new Set(entries.map(function (entry) {
          return entry.period;
        }))).sort(function (a, b) {
          return a - b;
        });

        previewHead.innerHTML = '<tr><th>Period</th><th>Time</th>' + days.map(function (day) {
          return '<th>' + day + '</th>';
        }).join('') + '</tr>';

        if (!periods.length || !days.length) {
          previewBody.innerHTML = '<tr><td colspan="' + (days.length + 2) + '" class="text-center text-muted">Add timetable details to see preview.</td></tr>';
          return;
        }

        previewBody.innerHTML = periods.map(function (period) {
          const periodEntries = entries.filter(function (entry) {
            return entry.period === period;
          });
          const timeEntry = periodEntries.find(function (entry) {
            return entry.startTime && entry.endTime;
          });

          return '<tr><th>Period ' + period + '</th><td>' + (timeEntry ? timeEntry.startTime + ' - ' + timeEntry.endTime : '-') + '</td>' +
            days.map(function (day) {
              const entry = periodEntries.find(function (item) {
                return item.day === day;
              });

              if (!entry) {
                return '<td>-</td>';
              }

              if (entry.entryType !== 'period') {
                return '<td><span class="preview-cell-title">' + entry.typeLabel + '</span><span class="preview-cell-meta">' + (entry.duration || '-') + '</span></td>';
              }

              const teachers = [entry.teacherOne, entry.teacherTwo].filter(Boolean).join(', ');

              return '<td><span class="preview-cell-title">' + (entry.subject || 'Subject not selected') + '</span><span class="preview-cell-meta">' + (teachers || 'Teacher not selected') + '</span></td>';
            }).join('') + '</tr>';
        }).join('');
      }

      addEntryRow.addEventListener('click', function () {
        const firstRow = entryRows.querySelector('.entry-row');
        const clone = firstRow.cloneNode(true);

        rowInputs(clone).forEach(function (input) {
          if (input.dataset.field === 'period_no') {
            input.value = entryRows.querySelectorAll('.entry-row').length + 1;
          } else if (input.dataset.field === 'entry_type') {
            input.value = 'period';
          } else if (input.dataset.field === 'day') {
            input.value = 'Monday';
          } else {
            input.value = '';
          }
        });

        clone.querySelector('.duration-field').value = '';
        entryRows.appendChild(clone);
        bindRow(clone);
        reindexRows();
        updatePreview();
      });

      document.querySelectorAll('#workingDays input').forEach(function (input) {
        input.addEventListener('change', updatePreview);
      });

      Array.from(entryRows.querySelectorAll('.entry-row')).forEach(bindRow);
      reindexRows();
      updatePreview();
    });
  </script>
@endpush