@extends('layouts.app')

@section('title', 'Teacher Scheduler')

@push('styles')
  <style>
    .teacher-weekly-table th, .teacher-weekly-table td { min-width: 145px; padding: 10px; text-align: center; vertical-align: top; }
    .teacher-weekly-table .period-column { min-width: 90px; }
    .scheduler-cell-title { color: #172033; display: block; font-weight: 700; }
    .scheduler-cell-meta { color: #536176; display: block; font-size: 11px; margin-top: 3px; }
    .scheduler-badge { border-radius: 12px; display: inline-block; font-size: 10px; font-weight: 700; margin-top: 6px; padding: 3px 7px; }
    .scheduler-regular { background: #f1f5f9; color: #475569; }
    .scheduler-special { background: #2563eb; color: #fff; }
    .scheduler-project { background: #16a34a; color: #fff; }
    .scheduler-substitute { background: #7e22ce; color: #fff; }
    .scheduler-swatch { border: 1px solid #d1d5db; border-radius: 4px; display: inline-block; height: 16px; margin-right: 5px; vertical-align: -3px; width: 16px; }
    .scheduler-entry { border-radius: 6px; margin-bottom: 6px; padding: 8px; }
    .scheduler-entry:last-child { margin-bottom: 0; }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Teacher Scheduler</h3>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li><li class="breadcrumb-item"><a href="{{ route('teachers.show', $teacher) }}">{{ $teacher->name }}</a></li><li class="breadcrumb-item active">Scheduler</li></ol></nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <div class="row g-3 align-items-center">
        <div class="col-lg-2"><img src="{{ $teacher->imageUrl() ?: asset('assets/img/profile-img.jpg') }}" alt="{{ $teacher->name }}" class="teacher-detail-image"></div>
        <div class="col-lg-10"><h4 class="mb-1">{{ $teacher->name }}</h4><p class="mb-1">{{ $teacher->employee_id }}</p><span>{{ $teacher->department?->department_name ?? '-' }} / {{ $teacher->designation?->designation_name ?? '-' }}</span></div>
      </div>
    </div>

      <div class="main-table-container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h5 class="mb-1">{{ $teacher->name }}: {{ $preview['week_start']->format('d M') }} – {{ $preview['week_end']->format('d M Y') }}</h5><span class="text-muted">All assigned classes for the current week</span></div><div class="d-flex gap-2"><a href="{{ route('teachers.index') }}" class="btn btn-secondary">Back</a><a href="{{ route('teachers.scheduler.pdf', $teacher) }}" id="downloadTeacherSchedulerPdf" class="btn btn-danger"><i class="fa-solid fa-file-pdf me-1"></i> Download PDF</a></div></div>
        <div class="d-flex flex-wrap gap-3 mb-3"><span><i class="scheduler-swatch" style="background:#dbeafe"></i>Special Event</span><span><i class="scheduler-swatch" style="background:#dcfce7"></i>Project Week</span><span><i class="scheduler-swatch" style="background:#f3e8ff"></i>Substitute</span><span><i class="scheduler-swatch" style="background:#fff"></i>Regular</span></div>
        <div class="table-responsive">
          <table class="table table-bordered teacher-weekly-table mb-0">
            <thead><tr><th class="period-column">Period</th>@foreach($preview['days'] as $day => $date)<th>{{ $day }}<small class="d-block text-muted">{{ $date->format('d M') }}</small></th>@endforeach</tr></thead>
            <tbody>
              @for($period = 1; $period <= $preview['periods']; $period++)
                <tr><th>Period {{ $period }}</th>@foreach($preview['days'] as $day => $date)@php($entries = $preview['cells']->get($day.'|'.$period, []))<td>@forelse($entries as $cell)<div class="scheduler-entry" style="background-color:{{ $cell['color'] ?? '#fff' }}"><span class="scheduler-cell-title">{{ $cell['title'] }}</span><span class="scheduler-cell-meta fw-bold">Grade {{ $cell['grade'] }} / {{ $cell['division'] }}</span><span class="scheduler-cell-meta">{{ $cell['time'] }}</span>@if(!empty($cell['original_teacher']))<span class="scheduler-cell-meta">For: {{ $cell['original_teacher'] }}</span>@endif<span class="scheduler-badge scheduler-{{ $cell['type'] }}">{{ $cell['label'] }}</span></div>@empty<span class="text-muted">—</span>@endforelse</td>@endforeach</tr>
              @endfor
              @if($preview['periods'] === 0)<tr><td colspan="7" class="text-center text-muted py-4">No timetable assignments were found for this teacher in the current week.</td></tr>@endif
            </tbody>
          </table>
        </div>
      </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.getElementById('downloadTeacherSchedulerPdf')?.addEventListener('click', function () {
        const button = this, original = button.innerHTML; button.classList.add('disabled'); button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparing PDF...';
        window.setTimeout(function () { button.classList.remove('disabled'); button.innerHTML = original; }, 3000);
      });
    });
  </script>
@endpush
