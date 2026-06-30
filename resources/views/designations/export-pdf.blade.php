<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Designations</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Designations</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Designation</th>
        <th>Department</th>
        <th>Grade</th>
        <th>Status</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($designations as $designation)
        <tr>
          <td>{{ $designation->code }}</td>
          <td>{{ $designation->designation_name }}</td>
          <td>{{ $designation->department?->department_name ?? '-' }}</td>
          <td>
            @if ($designation->grade)
              {{ $designation->grade->grade }}{{ $designation->grade->academicYear ? ' - '.$designation->grade->academicYear->academic_year : '' }}
            @else
              -
            @endif
          </td>
          <td>{{ $designation->is_active ? 'Active' : 'Inactive' }}</td>
          <td>{{ $designation->description ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
