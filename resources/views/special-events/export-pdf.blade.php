<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Special Events</title>
  <style>
    body { color: #222; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    h2 { margin: 0 0 14px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 7px; text-align: left; vertical-align: top; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Special Events</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Title</th>
        <th>Type</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Coordinator</th>
        <th>Classes</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($specialEvents as $specialEvent)
        <tr>
          <td>{{ $specialEvent->event_code }}</td>
          <td>{{ $specialEvent->event_title }}</td>
          <td>{{ $specialEvent->eventType?->title ?? '-' }}</td>
          <td>{{ $specialEvent->event_start_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $specialEvent->event_end_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ collect()->merge($specialEvent->staffCoordinators->pluck('name'))->merge($specialEvent->teacherCoordinators->pluck('name'))->implode(', ') ?: '-' }}</td>
          <td>{{ $specialEvent->grades->pluck('grade')->implode(', ') ?: '-' }}</td>
          <td>{{ \App\Models\SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
