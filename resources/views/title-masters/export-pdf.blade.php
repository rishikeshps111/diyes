<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>{{ $master['plural'] }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; } table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; } th { background: #f1f1f1; }
  </style>
</head><body>
  <h2>{{ $master['plural'] }}</h2>
  <table><thead><tr><th>Code</th><th>Title</th><th>Status</th></tr></thead><tbody>
    @foreach ($records as $record)
      <tr><td>{{ $record->code }}</td><td>{{ $record->title }}</td><td>{{ $record->is_active ? 'Active' : 'Inactive' }}</td></tr>
    @endforeach
  </tbody></table>
</body></html>
