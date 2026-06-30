<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Holidays</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Holidays</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Holiday</th>
        <th>Holiday Type</th>
        <th>Academic Year</th>
        <th>Holiday Date</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Branch</th>
        <th>Applicable Classes</th>
        <th>Status</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($holidays as $holiday)
        <tr>
          <td>{{ $holiday->code }}</td>
          <td>{{ $holiday->holiday_name }}</td>
          <td>{{ $holiday->holiday_type }}</td>
          <td>{{ $holiday->academicYear?->academic_year ?? '-' }}</td>
          <td>{{ $holiday->holiday_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $holiday->start_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $holiday->end_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $holiday->applicable_branch ?? '-' }}</td>
          <td>{{ $holiday->applicable_classes ?? '-' }}</td>
          <td>{{ $holiday->is_active ? 'Active' : 'Inactive' }}</td>
          <td>{{ $holiday->description ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
