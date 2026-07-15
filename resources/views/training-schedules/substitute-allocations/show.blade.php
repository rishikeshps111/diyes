@extends('layouts.app')

@section('title', 'Substitute Timetable')

@section('content')
  <div class="page-title">
    <h3>Substitute Timetable</h3>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Timetable Management</li>
      <li class="breadcrumb-item"><a href="{{ route('training-schedules.substitute-allocations.index', $trainingSchedule) }}">Substitute Allocation</a></li>
      <li class="breadcrumb-item active">View Timetable</li>
    </ol></nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container">
      <div class="row mb-3">
        <div class="col-md-8">
          <h5 class="mb-1">{{ $trainingSchedule->title }}</h5>
          <div class="text-muted">{{ $trainingSchedule->start_date?->format('d M Y') }} - {{ $trainingSchedule->end_date?->format('d M Y') }}</div>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="{{ route('training-schedules.substitute-allocations.index', $trainingSchedule) }}" class="btn btn-danger">Back</a>
        </div>
      </div>

      @forelse ($allocations->groupBy(fn ($item) => ($item->grade_id ?? 0).'|'.($item->division_id ?? 0)) as $group)
        @php
          $groupDates = $group->pluck('allocation_date')->unique(fn ($date) => $date->toDateString())->sort()->values();
          $first = $group->first();
        @endphp
        <h6 class="fw-bold mt-3 mb-2">Grade {{ $first->grade?->grade ?? '-' }} - {{ $first->division?->division ?? '-' }}</h6>
        <div class="table-over mb-4">
          <table class="align-middle mb-0 table table-bordered table-custom w-100">
            <thead><tr><th>Period</th>
              @foreach ($groupDates as $date)<th>{{ $date->format('d M Y') }}<br><small>{{ $date->format('l') }}</small></th>@endforeach
            </tr></thead>
            <tbody>
              @for ($period = 1; $period <= 8; $period++)
                <tr><th>Period {{ $period }}</th>
                  @foreach ($groupDates as $date)
                    @php($cell = $group->first(fn ($item) => $item->allocation_date->isSameDay($date) && (int) ($item->period_no ?? $item->timetableEntry?->period_no) === $period))
                    <td>
                      @if ($cell)
                        <strong>{{ $cell->subject?->subject_name ?? $cell->timetableEntry?->subject?->subject_name ?? '-' }}</strong><br>
                        <small>{{ $cell->teacher?->name ?? $cell->trainerAssignment?->teacher?->name ?? '-' }} &rarr; {{ $cell->substituteTeacher?->name ?? '-' }}</small>
                      @else<span class="text-muted">-</span>@endif
                    </td>
                  @endforeach
                </tr>
              @endfor
            </tbody>
          </table>
        </div>
      @empty
        <div class="text-center text-muted py-4">No substitute allocations found.</div>
      @endforelse
    </div>
  </section>
@endsection
