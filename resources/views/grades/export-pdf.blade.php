<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Grades</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Grades</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Grade</th>
        <th>Capacity</th>
        <th>Academic Year</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($grades as $grade)
        <tr>
          <td>{{ $grade->code }}</td>
          <td>{{ $grade->grade }}</td>
          <td>{{ $grade->capacity }}</td>
          <td>{{ $grade->academicYear?->academic_year ?? '-' }}</td>
          <td>{{ $grade->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
