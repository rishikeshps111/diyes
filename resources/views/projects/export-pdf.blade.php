<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Projects</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 6px; text-align: left; vertical-align: top; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Projects</h2>
  <table>
    <thead>
      <tr>
        <th>Project Code</th>
        <th>Project Title</th>
        <th>Category</th>
        <th>Duration</th>
        <th>Classes</th>
        <th>Subjects</th>
        <th>Allocated Teachers</th>
        <th>Venue</th>
        <th>Created Date</th>
        <th>Timetable Replacement</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($projects as $project)
        <tr>
          <td>{{ $project->project_code }}</td>
          <td>{{ $project->project_title }}</td>
          <td>{{ $project->category?->title ?? '-' }}</td>
          <td>{{ $project->duration_days }} day(s)</td>
          <td>{{ $project->grades->pluck('grade')->implode(', ') ?: '-' }}</td>
          <td>{{ $project->subjects->pluck('subject_name')->implode(', ') ?: '-' }}</td>
          <td>{{ $project->teachers->pluck('name')->implode(', ') ?: '-' }}</td>
          <td>{{ $project->venue ?: '-' }}</td>
          <td>{{ $project->created_at?->format('d M Y') ?? '-' }}</td>
          <td>{{ $project->timetable_replacement ? 'Yes' : 'No' }}</td>
          <td>{{ \App\Models\Project::STATUSES[$project->status] ?? ucfirst($project->status) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
