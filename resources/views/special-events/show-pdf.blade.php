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
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ $specialEvent->event_title }}</title>
  <style>
    body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    h2 { color: #111827; margin: 0 0 14px; }
    h3 { border-bottom: 1px solid #d7dee8; color: #111827; font-size: 14px; margin: 18px 0 8px; padding-bottom: 5px; }
    table { border-collapse: collapse; margin-bottom: 10px; width: 100%; }
    th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; vertical-align: top; }
    th { background: #f1f5f9; color: #475569; width: 24%; }
    .textbox { border: 1px solid #cbd5e1; line-height: 1.55; min-height: 36px; padding: 8px; }
  </style>
</head>
<body>
  <h2>{{ $specialEvent->event_title }}</h2>

  <h3>Event Information</h3>
  <table>
    <tr><th>Event Code</th><td>{{ $specialEvent->event_code }}</td><th>Event Type</th><td>{{ $specialEvent->eventType?->title ?? '-' }}</td></tr>
    <tr><th>Academic Year</th><td>{{ $specialEvent->academicYear?->academic_year ?? '-' }}</td><th>Status</th><td>{{ \App\Models\SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status) }}</td></tr>
    <tr><th>Start Date</th><td>{{ $specialEvent->event_start_date?->format('d M Y') ?? '-' }}</td><th>End Date</th><td>{{ $specialEvent->event_end_date?->format('d M Y') ?? '-' }}</td></tr>
    <tr><th>Days</th><td>{{ $specialEvent->days ?: '-' }}</td><th>Venue</th><td>{{ $specialEvent->venue ?: '-' }}</td></tr>
    <tr><th>Organized By</th><td>{{ $specialEvent->organized_by ?: '-' }}</td><th>In-Charge</th><td>{{ $specialEvent->incharge ?: '-' }}</td></tr>
    <tr><th>Contact Number</th><td>{{ $specialEvent->contact_no ?: '-' }}</td><th>Coordinator</th><td>{{ $coordinators }}</td></tr>
    <tr><th>Applicable To</th><td>{{ $participantLabels }}</td><th>Classes</th><td>{{ $classes }}</td></tr>
    <tr><th>Divisions</th><td>{{ $divisions }}</td><th>Media Coverage</th><td>{{ $specialEvent->media_coverable ? 'Enabled' : 'Disabled' }}</td></tr>
    <tr><th>Outside Candidates</th><td>{{ $specialEvent->outside_candidates ? 'Yes' : 'No' }}</td><th>Attachments</th><td>{{ $specialEvent->attachments->pluck('file_name')->implode(', ') ?: '-' }}</td></tr>
  </table>

  <h3>Timing</h3>
  <table>
    <thead>
      <tr>
        <th>Day</th>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($specialEvent->timings as $timing)
        <tr>
          <td>{{ $timing->day_label }}</td>
          <td>{{ $timing->event_date?->format('d M Y') }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($timing->start_time)->format('h:i A') }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($timing->end_time)->format('h:i A') }}</td>
        </tr>
      @empty
        <tr><td colspan="4">-</td></tr>
      @endforelse
    </tbody>
  </table>

  <h3>Event Details</h3>
  <strong>Event Description</strong>
  <div class="textbox">{{ $specialEvent->event_details ?: '-' }}</div>
  <br>
  <strong>Objectives</strong>
  <div class="textbox">{{ $specialEvent->objective ?: '-' }}</div>
</body>
</html>
