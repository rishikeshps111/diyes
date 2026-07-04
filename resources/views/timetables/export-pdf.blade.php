<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Regular Timetables</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 6px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Regular Timetables</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Timetable Name</th>
        <th>Timetable Type</th>
        <th>Academic Year</th>
        <th>Grade</th>
        <th>Division</th>
        <th>Total Periods</th>
        <th>Application From</th>
        <th>Application To</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($timetables as $timetable)
        <tr>
          <td>{{ $timetable->code }}</td>
          <td>{{ $timetable->timetable_name }}</td>
          <td>{{ $timetable->timetableType?->title ?? '-' }}</td>
          <td>{{ $timetable->academicYear?->academic_year ?? '-' }}</td>
          <td>{{ $timetable->grade?->grade ?? '-' }}</td>
          <td>{{ $timetable->divisions->pluck('division')->implode(', ') ?: '-' }}</td>
          <td>{{ $timetable->total_periods_per_day }}</td>
          <td>{{ $timetable->applicable_from?->format('d M Y') ?? '-' }}</td>
          <td>{{ $timetable->applicable_to?->format('d M Y') ?? '-' }}</td>
          <td>{{ ucfirst($timetable->status) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
