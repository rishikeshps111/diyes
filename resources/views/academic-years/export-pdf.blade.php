<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Academic Years</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Academic Years</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Academic Year</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Status</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($academicYears as $academicYear)
        <tr>
          <td>{{ $academicYear->code }}</td>
          <td>{{ $academicYear->academic_year }}</td>
          <td>{{ $academicYear->start_date?->format('d M Y') }}</td>
          <td>{{ $academicYear->end_date?->format('d M Y') }}</td>
          <td>{{ $academicYear->is_active ? 'Active' : 'Inactive' }}</td>
          <td>{{ $academicYear->description }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
