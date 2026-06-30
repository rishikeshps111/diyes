<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Departments</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Departments</h2>
  <table>
    <thead>
      <tr>
        <th>Department Code</th>
        <th>Department Name</th>
        <th>Department Head</th>
        <th>Teachers</th>
        <th>Display Order</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($departments as $department)
        <tr>
          <td>{{ $department->department_code }}</td>
          <td>{{ $department->department_name }}</td>
          <td>{{ $department->department_head ?? '-' }}</td>
          <td>{{ $department->teacher_count }}</td>
          <td>{{ $department->display_order }}</td>
          <td>{{ $department->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
