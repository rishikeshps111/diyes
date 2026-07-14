@extends('layouts.app')

@section('title', 'View Training Schedule')

@section('content')
  <div class="page-title">
    <h3>Training Schedule</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('training-schedules.index') }}">Training Schedule</a></li>
        <li class="breadcrumb-item active">View</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="main-table-container">
      <div class="trpreview-card">
        <div class="trpreview-title">Training Information</div>

        <div class="px-4 pt-4">
          <div class="trpreview-item" style="width: 100%;">
            <label>Training Title</label>
            <span>{{ $trainingSchedule->title }}</span>
          </div>
        </div>

        <div class="trpreview-grid">
          <div class="trpreview-item">
            <label>Code</label>
            <span>{{ $trainingSchedule->code }}</span>
          </div>
          <div class="trpreview-item">
            <label>Training Type</label>
            <span>{{ $trainingSchedule->trainerType?->title ?? '-' }}</span>
          </div>
          <div class="trpreview-item">
            <label>Training Category</label>
            <span>{{ $trainingSchedule->trainerCategory?->title ?? '-' }}</span>
          </div>
          <div class="trpreview-item">
            <label>Conducted By</label>
            <span>{{ \App\Models\TrainingSchedule::CONDUCTED_BY_OPTIONS[$trainingSchedule->conducted_by] ?? ucfirst($trainingSchedule->conducted_by) }}</span>
          </div>
          <div class="trpreview-item">
            <label>Resource Person / Trainer</label>
            <span>{{ $trainingSchedule->resource_person_trainer }}</span>
          </div>
          <div class="trpreview-item">
            <label>Start Date</label>
            <span>{{ $trainingSchedule->start_date?->format('d M Y') ?? '-' }}</span>
          </div>
          <div class="trpreview-item">
            <label>End Date</label>
            <span>{{ $trainingSchedule->end_date?->format('d M Y') ?? '-' }}</span>
          </div>
          <div class="trpreview-item">
            <label>Per Day Hours</label>
            <span>{{ rtrim(rtrim((string) $trainingSchedule->per_day_hours, '0'), '.') }} Hours</span>
          </div>
          <div class="trpreview-item">
            <label>Mode</label>
            <span>{{ \App\Models\TrainingSchedule::MODES[$trainingSchedule->mode] ?? ucfirst($trainingSchedule->mode) }}</span>
          </div>
          <div class="trpreview-item">
            <label>Venue</label>
            <span>{{ $trainingSchedule->venue ?: '-' }}</span>
          </div>
          <div class="trpreview-item">
            <label>Total Count</label>
            <span>{{ $trainingSchedule->total_count }}</span>
          </div>
          <div class="trpreview-item">
            <label>Applicable To</label>
            <span>{{ \App\Models\TrainingSchedule::APPLICABLE_OPTIONS[$trainingSchedule->applicable] ?? ucfirst($trainingSchedule->applicable) }}</span>
          </div>
          @if ($trainingSchedule->applicable === 'teachers')
            <div class="trpreview-item trpreview-full">
              <label>Teaching Staff Subjects</label>
              <span>{{ $trainingSchedule->subjects->pluck('subject_name')->implode(', ') ?: '-' }}</span>
            </div>
          @endif
          <div class="trpreview-item">
            <label>Status</label>
            <span class="trpreview-status" @if ($trainingSchedule->status !== 'published')
            style="background: #f59e0b !important;" @endif>
              {{ \App\Models\TrainingSchedule::STATUSES[$trainingSchedule->status] ?? ucfirst($trainingSchedule->status) }}
            </span>
          </div>
          <div class="trpreview-item">
            <label>Created By</label>
            <span>{{ $trainingSchedule->creator?->name ?? '-' }}</span>
          </div>
        </div>
      </div>

      <div class="trpreview-card">
        <div class="trpreview-title">Training Details</div>
        <div class="trpreview-grid">
          <div class="trpreview-item trpreview-full">
            <label>Training Objectives</label>
            <div class="trpreview-textbox" style="white-space: pre-wrap;">
              {{ $trainingSchedule->training_objectives ?: '-' }}
            </div>
          </div>
          <div class="trpreview-item trpreview-full">
            <label>Training Description</label>
            <div class="trpreview-textbox" style="white-space: pre-wrap;">
              {{ $trainingSchedule->training_description ?: '-' }}
            </div>
          </div>
          <div class="trpreview-item trpreview-full">
            <label>Remarks</label>
            <div class="trpreview-textbox" style="white-space: pre-wrap;">{{ $trainingSchedule->remarks ?: '-' }}</div>
          </div>
        </div>
      </div>

      <div class="trpreview-card">
        <div class="trpreview-title">Schedule Details</div>
        <div class="table-over">
          <table class="trpreview-table">
            <thead>
              <tr>
                <th>Session</th>
                <th>Date</th>
                <th>Time From</th>
                <th>Time To</th>
                <th>Topic Module</th>
                <th>Duration</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($trainingSchedule->sessions as $session)
                <tr>
                  <td>{{ $session->session_no }}</td>
                  <td>{{ $session->session_date?->format('d M Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($session->time_from)->format('h:i A') }}</td>
                  <td>{{ \Carbon\Carbon::parse($session->time_to)->format('h:i A') }}</td>
                  <td>
                    <div class="trpreview-topic">{{ $session->topic_module }}</div>
                  </td>
                  <td>{{ rtrim(rtrim((string) $session->duration_hours, '0'), '.') }} Hours</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">No schedule sessions found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-center">
        <div class="btn-flex">
          <a href="{{ route('training-schedules.index') }}" class="btn btn-danger">Back</a>
        </div>
      </div>
    </div>
  </section>
@endsection