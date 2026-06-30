<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Divisions</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Divisions</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Grade</th>
        <th>Division</th>
        <th>Class Teacher</th>
        <th>Capacity</th>
        <th>Room Number</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($divisions as $division)
        <tr>
          <td>{{ $division->code }}</td>
          <td>
            @if ($division->grade)
              {{ $division->grade->grade }}{{ $division->grade->academicYear ? ' - '.$division->grade->academicYear->academic_year : '' }}
            @else
              -
            @endif
          </td>
          <td>{{ $division->division }}</td>
          <td>{{ $division->class_teacher ?? '-' }}</td>
          <td>{{ $division->capacity }}</td>
          <td>{{ $division->room_number ?? '-' }}</td>
          <td>{{ $division->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
