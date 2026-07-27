@extends('layouts.app')

@section('content')

<div class="card">

<div class="card-header">

Log Details

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>User</th>
<td>{{ $userLog->user_name }}</td>
</tr>

<tr>
<th>Module</th>
<td>{{ $userLog->module }}</td>
</tr>

<tr>
<th>Action</th>
<td>{{ $userLog->action }}</td>
</tr>

<tr>
<th>Description</th>
<td>{{ $userLog->description }}</td>
</tr>

<tr>
<th>URL</th>
<td>{{ $userLog->url }}</td>
</tr>

<tr>
<th>Method</th>
<td>{{ $userLog->method }}</td>
</tr>

<tr>
<th>IP Address</th>
<td>{{ $userLog->ip_address }}</td>
</tr>

<tr>
<th>User Agent</th>
<td>{{ $userLog->user_agent }}</td>
</tr>

<tr>
<th>Created At</th>
<td>{{ $userLog->created_at }}</td>
</tr>

</table>

</div>

</div>

@endsection