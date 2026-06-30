<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Classrooms</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Classrooms</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Room Name</th>
        <th>Building</th>
        <th>Floor</th>
        <th>Room Type</th>
        <th>Capacity</th>
        <th>Department</th>
        <th>Equipment</th>
        <th>Status</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($classrooms as $classroom)
        <tr>
          <td>{{ $classroom->code }}</td>
          <td>{{ $classroom->room_name }}</td>
          <td>{{ $classroom->building }}</td>
          <td>{{ $classroom->floor }}</td>
          <td>{{ $classroom->room_type }}</td>
          <td>{{ $classroom->seating_capacity }}</td>
          <td>{{ $classroom->department?->department_name ?? '-' }}</td>
          <td>{{ collect($classroom->equipment)->filter()->implode(', ') ?: '-' }}</td>
          <td>{{ $classroom->is_active ? 'Active' : 'Inactive' }}</td>
          <td>{{ $classroom->remarks ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
