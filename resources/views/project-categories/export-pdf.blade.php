<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Project Categories</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Project Categories</h2>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Title</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($projectCategories as $projectCategory)
        <tr>
          <td>{{ $projectCategory->code }}</td>
          <td>{{ $projectCategory->title }}</td>
          <td>{{ $projectCategory->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
