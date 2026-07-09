<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Project Weeks</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 6px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Project Weeks</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Project</th>
        <th>Applicable From</th>
        <th>Applicable To</th>
        <th>Academic Year</th>
        <th>Grade</th>
        <th>Division</th>
        <th>Total Periods</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($projectWeeks as $projectWeek)
        <tr>
          <td>{{ $projectWeek->code }}</td>
          <td>{{ $projectWeek->project?->project_title ?? '-' }}</td>
          <td>{{ $projectWeek->applicable_from?->format('d M Y') ?? '-' }}</td>
          <td>{{ $projectWeek->applicable_to?->format('d M Y') ?? '-' }}</td>
          <td>{{ $projectWeek->academicYear?->academic_year ?? '-' }}</td>
          <td>{{ $projectWeek->grade?->grade ?? '-' }}</td>
          <td>{{ $projectWeek->divisions->pluck('division')->implode(', ') ?: '-' }}</td>
          <td>{{ $projectWeek->total_periods }}</td>
          <td>{{ ucfirst($projectWeek->status) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
