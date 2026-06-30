<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Venues</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Venues</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Venue Name</th>
        <th>Venue Type</th>
        <th>Building</th>
        <th>Capacity</th>
        <th>Facilities</th>
        <th>Contact Person</th>
        <th>Status</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($venues as $venue)
        <tr>
          <td>{{ $venue->code }}</td>
          <td>{{ $venue->venue_name }}</td>
          <td>{{ $venue->venue_type }}</td>
          <td>{{ $venue->building }}</td>
          <td>{{ $venue->capacity }}</td>
          <td>{{ collect($venue->facilities)->filter()->implode(', ') ?: '-' }}</td>
          <td>{{ $venue->contact_person }}</td>
          <td>{{ $venue->is_active ? 'Active' : 'Inactive' }}</td>
          <td>{{ $venue->remarks ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
