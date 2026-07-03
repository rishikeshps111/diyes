<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Teachers</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 6px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Teachers</h2>
  <table>
    <thead>
      <tr>
        <th>Employee Code</th>
        <th>Name</th>
        <th>Department</th>
        <th>Designation</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Date of Joining</th>
        <th>Status</th>
        <th>Verification Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($teachers as $teacher)
        <tr>
          <td>{{ $teacher->employee_id }}</td>
          <td>{{ $teacher->name }}</td>
          <td>{{ $teacher->department?->department_name ?? '-' }}</td>
          <td>{{ $teacher->designation?->designation_name ?? '-' }}</td>
          <td>{{ $teacher->email }}</td>
          <td>{{ trim($teacher->phone_country_code.' '.$teacher->phone) }}</td>
          <td>{{ $teacher->date_of_joining?->format('d M Y') ?? '-' }}</td>
          <td>{{ ucfirst($teacher->status) }}</td>
          <td>{{ $teacher->is_verified ? 'Verified' : 'Pending' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
