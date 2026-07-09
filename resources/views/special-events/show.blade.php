@extends('layouts.app')

@section('title', 'View Special Event')

@php
  $participantLabels = collect($specialEvent->participants ?? [])
    ->map(fn($value) => \App\Models\SpecialEvent::PARTICIPANTS[$value] ?? $value)
    ->filter()
    ->implode(', ') ?: '-';
  $coordinators = collect()
    ->merge($specialEvent->staffCoordinators->pluck('name'))
    ->merge($specialEvent->teacherCoordinators->pluck('name'))
    ->filter()
    ->implode(', ') ?: '-';
  $classes = $specialEvent->grades->pluck('grade')->implode(', ') ?: '-';
  $divisions = $specialEvent->divisions->map(fn($division) => ($division->grade?->grade ? $division->grade->grade.' - ' : '').$division->division)->implode(', ') ?: '-';
  $firstTiming = $specialEvent->timings->first();
  $lastTiming = $specialEvent->timings->last();
@endphp

@push('styles')
  <style>
    .evpreview-card {
      border-bottom: 1px solid #edf1f5;
      padding: 0 0 22px;
      margin-bottom: 24px;
    }

    .evpreview-card:last-child {
      border-bottom: 0;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .evpreview-title {
      color: #1f2937;
      font-size: 18px;
      font-weight: 800;
      margin-bottom: 16px;
    }

    .evpreview-grid {
      display: grid;
      gap: 14px;
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .evpreview-item {
      background: #f8fafc;
      border: 1px solid #e7edf3;
      border-radius: 8px;
      padding: 12px 14px;
    }

    .evpreview-item label {
      color: #667085;
      display: block;
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    .evpreview-item span,
    .evpreview-textbox {
      color: #111827;
      font-size: 14px;
      font-weight: 600;
      overflow-wrap: anywhere;
    }

    .evpreview-full {
      grid-column: 1 / -1;
    }

    .evpreview-textbox {
      background: #fff;
      border: 1px solid #eef2f6;
      border-radius: 8px;
      font-weight: 500;
      line-height: 1.6;
      min-height: 46px;
      padding: 12px;
    }

    .evpreview-status {
      border-radius: 999px;
      display: inline-flex;
      padding: 4px 10px;
    }

    .evpreview-banner {
      border-radius: 12px;
      height: 100px;
      object-fit: cover;
      width: 150px;
    }

    @media (max-width: 1199px) {
      .evpreview-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 767px) {
      .evpreview-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Special Event</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('special-events.index') }}">Special Events</a></li>
        <li class="breadcrumb-item active">View</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container">
      <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('special-events.index') }}" class="reset-btn text-decoration-none">Back</a>
      </div>

      <div class="evpreview-card">
        <div class="evpreview-title">Event Information</div>
        <div class="evpreview-grid">
          <div class="evpreview-item">
            <label>Event Code</label>
            <span>{{ $specialEvent->event_code }}</span>
          </div>
          <div class="evpreview-item">
            <label>Event Title</label>
            <span>{{ $specialEvent->event_title }}</span>
          </div>
          <div class="evpreview-item">
            <label>Event Type</label>
            <span>{{ $specialEvent->eventType?->title ?? '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Academic Year</label>
            <span>{{ $specialEvent->academicYear?->academic_year ?? '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Event Start Date</label>
            <span>{{ $specialEvent->event_start_date?->format('d M Y') ?? '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Event End Date</label>
            <span>{{ $specialEvent->event_end_date?->format('d M Y') ?? '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Days</label>
            <span>{{ $specialEvent->days ?: '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Start Time</label>
            <span>{{ $firstTiming ? \Illuminate\Support\Carbon::parse($firstTiming->start_time)->format('h:i A') : '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>End Time</label>
            <span>{{ $lastTiming ? \Illuminate\Support\Carbon::parse($lastTiming->end_time)->format('h:i A') : '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Venue</label>
            <span>{{ $specialEvent->venue ?: '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Organized By</label>
            <span>{{ $specialEvent->organized_by ?: '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>In-Charge</label>
            <span>{{ $specialEvent->incharge ?: '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Contact Number</label>
            <span>{{ $specialEvent->contact_no ?: '-' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Event Coordinator</label>
            <span>{{ $coordinators }}</span>
          </div>
          <div class="evpreview-item">
            <label>Applicable To</label>
            <span>{{ $participantLabels }}</span>
          </div>
          <div class="evpreview-item">
            <label>Classes</label>
            <span>{{ $classes }}</span>
          </div>
          <div class="evpreview-item">
            <label>Divisions</label>
            <span>{{ $divisions }}</span>
          </div>
          <div class="evpreview-item">
            <label>Status</label>
            <span class="evpreview-status {{ in_array($specialEvent->status, ['active', 'complete'], true) ? 'status-green' : (in_array($specialEvent->status, ['cancelled', 'inactive'], true) ? 'status-red' : 'status-orange') }}">
              {{ \App\Models\SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status) }}
            </span>
          </div>
        </div>
      </div>

      <div class="evpreview-card">
        <div class="evpreview-title">Timing</div>
        <div class="evpreview-grid">
          <div class="evpreview-item evpreview-full">
            <label>Event Timings</label>
            <div class="evpreview-textbox">
              @forelse ($specialEvent->timings as $timing)
                {{ $timing->day_label }} :
                {{ $timing->event_date?->format('d M Y') }}
                ({{ \Illuminate\Support\Carbon::parse($timing->start_time)->format('h:i A') }} -
                {{ \Illuminate\Support\Carbon::parse($timing->end_time)->format('h:i A') }})<br>
              @empty
                -
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="evpreview-card">
        <div class="evpreview-title">Event Details</div>
        <div class="evpreview-grid">
          <div class="evpreview-item evpreview-full">
            <label>Event Description</label>
            <div class="evpreview-textbox">{{ $specialEvent->event_details ?: '-' }}</div>
          </div>
          <div class="evpreview-item evpreview-full">
            <label>Objectives</label>
            <div class="evpreview-textbox">{{ $specialEvent->objective ?: '-' }}</div>
          </div>
        </div>
      </div>

      <div class="evpreview-card">
        <div class="evpreview-title">Additional Settings</div>
        <div class="evpreview-grid">
          <div class="evpreview-item">
            <label>Media Coverage</label>
            <span>{{ $specialEvent->media_coverable ? 'Enabled' : 'Disabled' }}</span>
          </div>
          <div class="evpreview-item">
            <label>Outside Candidates</label>
            <span>{{ $specialEvent->outside_candidates ? 'Yes' : 'No' }}</span>
          </div>
          <div class="evpreview-item evpreview-full">
            <label>Event Banner</label>
            <div class="evpreview-textbox">
              @if ($specialEvent->bannerUrl())
                <img src="{{ $specialEvent->bannerUrl() }}" alt="{{ $specialEvent->event_title }}" class="evpreview-banner">
              @else
                -
              @endif
            </div>
          </div>
          <div class="evpreview-item evpreview-full">
            <label>Attachments</label>
            <div class="evpreview-textbox">
              @forelse ($specialEvent->attachments as $attachment)
                <a href="{{ $attachment->fileUrl() }}" target="_blank">{{ $attachment->file_name }}</a>@if (! $loop->last)<br>@endif
              @empty
                -
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
