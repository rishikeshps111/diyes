<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Subjects</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Subjects</h2>
  <table>
    <thead>
      <tr>
        <th>Subject Code</th>
        <th>Subject</th>
        <th>Grade</th>
        <th>Priority</th>
        <th>Practical Required</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($subjects as $subject)
        <tr>
          <td>{{ $subject->subject_code }}</td>
          <td>{{ $subject->subject_name }}</td>
          <td>{{ $subject->grade?->grade ?? '-' }}</td>
          <td>{{ ucfirst($subject->priority) }}</td>
          <td>{{ $subject->is_praticals ? 'Yes' : 'No' }}</td>
          <td>{{ $subject->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
