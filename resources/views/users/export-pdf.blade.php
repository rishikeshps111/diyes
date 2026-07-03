<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Users</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h2 { margin: 0 0 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #bbb; padding: 5px; text-align: left; }
    th { background: #f1f1f1; }
  </style>
</head>
<body>
  <h2>Users</h2>
  <table>
    <thead>
      <tr>
        <th>Employee ID</th>
        <th>Username</th>
        <th>Name</th>
        <th>Role</th>
        <th>Department</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Last Login</th>
        <th>Status</th>
        <th>Two-Factor</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
        <tr>
          <td>{{ $user->employee_code }}</td>
          <td>{{ $user->username }}</td>
          <td>{{ $user->name }}</td>
          <td>{{ $user->role?->name ? ucfirst($user->role->name) : '-' }}</td>
          <td>{{ $user->department?->department_name ?? '-' }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ trim($user->phone_country_code.' '.$user->phone) }}</td>
          <td>{{ $user->last_login_at?->format('d M Y h:i A') ?? '-' }}</td>
          <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
          <td>{{ $user->is_two_factor_enabled ? 'Enabled' : 'Disabled' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
