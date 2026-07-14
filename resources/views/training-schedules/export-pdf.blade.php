<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Training Schedules</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h2 { margin: 0 0 16px; } table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 7px; text-align: left; } th { background: #f1f1f1; }
  </style>
</head><body>
  <h2>Training Schedules</h2>
  <table><thead><tr>
    <th>Code</th><th>Title</th><th>Category</th><th>Type</th><th>Start Date</th><th>End Date</th><th>Status</th>
  </tr></thead><tbody>
    @foreach ($trainingSchedules as $trainingSchedule)
      <tr>
        <td>{{ $trainingSchedule->code }}</td>
        <td>{{ $trainingSchedule->title }}</td>
        <td>{{ $trainingSchedule->trainerCategory?->title ?? '-' }}</td>
        <td>{{ $trainingSchedule->trainerType?->title ?? '-' }}</td>
        <td>{{ $trainingSchedule->start_date?->format('d M Y') ?? '-' }}</td>
        <td>{{ $trainingSchedule->end_date?->format('d M Y') ?? '-' }}</td>
        <td>{{ \App\Models\TrainingSchedule::STATUSES[$trainingSchedule->status] ?? ucfirst($trainingSchedule->status) }}</td>
      </tr>
    @endforeach
  </tbody></table>
</body></html>
