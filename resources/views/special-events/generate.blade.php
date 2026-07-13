@extends('layouts.app')
@section('title', 'Generate Special Event TimeTable')
@php
    $periods = $entries->where('entry_type', 'period');
    $breaks = $entries->whereIn('entry_type', ['short_break', 'lunch_break']);
    $days = $periods->pluck('day')->unique()->values();
    $totalPeriods = max(1, (int) $periods->max('period_no'));
    $oldPeriods = collect(old('entries', []))
        ->map(fn($entry) => ($entry['day'] ?? '') . '|' . ($entry['period_no'] ?? ''))
        ->filter();
    $selectedPeriods = $oldPeriods->isNotEmpty() ? $oldPeriods : $savedEventPeriods;
@endphp
@push('styles')
    <style>
        .event-cell.is-selected {
            background: #dbeafe !important;
            box-shadow: inset 0 0 0 2px rgba(30, 64, 175, .3)
        }

        .event-cell {
            min-width: 210px
        }

        .event-cell.project-period:not(.is-selected) {
            background: #dff7df !important
        }

        .break-row td {
            background: #fff7ed;
            color: #9a3412;
            font-weight: 700;
            text-align: center
        }

        .lunch-row td {
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
            text-align: center
        }
    </style>
@endpush
@section('content')
    <div class="page-title">
        <h3>Generate Special Event TimeTable</h3>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('special-events.index') }}">Special Events</a></li>
                <li class="breadcrumb-item active">Generate</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="main-table-container mb-3">
            <h5 class="title-w-sec">Load Project Week TimeTable</h5>
            <hr>
            <form method="GET">
                <div class="row">
                    <div class="col-lg-5 o-f-inp mb-3"><label>Grade</label><select id="grade_id" name="grade_id"
                            class="form-select">
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}" @selected($selectedGradeId == $grade->id)>{{ $grade->grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-5 o-f-inp mb-3"><label>Division</label><select id="division_id" name="division_id"
                            class="form-select">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" data-grade-id="{{ $division->grade_id }}"
                                    @selected($selectedDivisionId == $division->id)>
                                    {{ $division->grade?->grade }} - {{ $division->division }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end mb-3"><button class="search-btn">Load</button></div>
                </div>
            </form>
        </div>
        <form method="POST" action="{{ route('special-events.generate.store', $specialEvent) }}" id="eventGenerateForm">
            @csrf
            <input type="hidden" name="grade_ids[]" value="{{ $selectedGradeId }}">
            <input type="hidden" name="division_ids[]" value="{{ $selectedDivisionId }}">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="main-table-container mb-3">
                <h5 class="title-w-sec">{{ $projectWeek->project?->project_title }} TimeTable</h5>
                <hr>
                @error('entries')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
                <div class="table-over">
                    <table class="table table-bordered align-middle">
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
                                    <th>Period {{ $period }}</th>@php($time = $periods->first(fn($e) => $e['period_no'] == $period))
                                    <td>{{ $time ? $time['start_time'] . ' - ' . $time['end_time'] : '-' }}</td>
                                    @foreach ($days as $day)
                                        @php($entry = $periods->first(fn($e) => $e['day'] === $day && $e['period_no'] === $period))@php($key = $day . '|' . $period)@php($field = md5($key))
                                        @if ($entry)
                                            <td
                                                class="event-cell {{ $entry['is_project_period'] ? 'project-period' : '' }} {{ $selectedPeriods->contains($key) ? 'is-selected' : '' }}"
                                                style="background-color: {{ $entry['color'] }}">
                                                <strong
                                                    class="event-title {{ $selectedPeriods->contains($key) ? '' : 'd-none' }}">{{ $specialEvent->event_title }}</strong>
                                                <div>{{ $entry['subject_name'] }}</div><small
                                                    class="text-muted">{{ implode(', ', $entry['teacher_names']) ?: '-' }}</small>
                                                @if ($entry['is_project_period'])
                                                    <div><span class="badge bg-success">Project Period</span></div>
                                                @endif
                                                <label class="d-flex gap-2 mt-2"><input type="checkbox" class="event-toggle"
                                                        name="entries[{{ $field }}][period_no]"
                                                        value="{{ $period }}" @checked($selectedPeriods->contains($key))><input
                                                        type="hidden" class="day-input"
                                                        name="entries[{{ $field }}][day]"
                                                        value="{{ $day }}" @disabled(!$selectedPeriods->contains($key))>
                                                    Special Event Period</label>
                                        </td>@else<td>-</td>
                                        @endif
                                    @endforeach
                                </tr>
                                @foreach (['short_break', 'lunch_break'] as $type)
                                    @php($break = $breaks->first(fn($e) => $e['period_no'] === $period && $e['entry_type'] === $type))@if ($break)
                                        <tr class="{{ $type === 'lunch_break' ? 'lunch-row' : 'break-row' }}">
                                            <td colspan="{{ $days->count() + 2 }}">
                                                {{ $type === 'lunch_break' ? 'Lunch Break' : 'Break' }}
                                                ({{ $break['duration_minutes'] }} mins)
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-center mb-4">
                <div class="btn-flex"><a href="{{ route('special-events.index') }}"
                        class="btn btn-danger">Cancel</a><button class="submit-btn" id="generateBtn"
                        data-loading-text="Generating..."  style="width: 195px !important;" >Generate Time Table</button></div>
            </div>
        </form>
    </section>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && jQuery.fn.select2) jQuery('#grade_id,#division_id').select2({
                width: '100%'
            });
            document.querySelectorAll('.event-toggle').forEach(function(c) {
                c.addEventListener('change', function() {
                    const cell = c.closest('.event-cell');
                    cell.classList.toggle('is-selected', c.checked);
                    cell.querySelector('.event-title').classList.toggle('d-none', !c.checked);
                    cell.querySelector('.day-input').disabled = !c.checked
                })
            });
            document.getElementById('eventGenerateForm').addEventListener('submit', function(e) {
                if (!document.querySelector('.event-toggle:checked')) {
                    e.preventDefault();
                    Swal.fire('No Periods', 'Select at least one special event period.', 'warning');
                    return
                }
                const button = document.getElementById('generateBtn');
                button.disabled = true;
                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' + (
                        button.dataset.loadingText || 'Generating...')
            });
            document.getElementById('grade_id').addEventListener('change', function() {
                const o = Array.from(document.getElementById('division_id').options).find(o => o.dataset
                    .gradeId === this.value);
                if (o) {
                    document.getElementById('division_id').value = o.value;
                    jQuery('#division_id').trigger('change')
                }
            })
        });
    </script>
@endpush
