@extends('layouts.app')

@section('title','Activity Log Details')

@section('content')

<div class="container-fluid">

<div class="card">

<div class="card-header d-flex justify-content-between">

<h4>

Activity Log Details

</h4>

<a
href="{{ route('activity-logs') }}"
class="btn btn-secondary">

Back

</a>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th width="220">

User

</th>

<td>

{{ $activityLog->user_name }}

</td>

</tr>

<tr>

<th>

Module

</th>

<td>

{{ $activityLog->module }}

</td>

</tr>

<tr>

<th>

Action

</th>

<td>

@if($activityLog->action=='Create')

<span class="badge bg-success">

Create

</span>

@elseif($activityLog->action=='Update')

<span class="badge bg-primary">

Update

</span>

@elseif($activityLog->action=='Delete')

<span class="badge bg-danger">

Delete

</span>

@elseif($activityLog->action=='Approve')

<span class="badge bg-info">

Approve

</span>

@elseif($activityLog->action=='Reject')

<span class="badge bg-warning text-dark">

Reject

</span>

@else

<span class="badge bg-secondary">

{{ $activityLog->action }}

</span>

@endif

</td>

</tr>

<tr>

<th>

Record ID

</th>

<td>

{{ $activityLog->record_id }}

</td>

</tr>

<tr>

<th>

Description

</th>

<td>

{{ $activityLog->description }}

</td>

</tr>

<tr>

<th>

IP Address

</th>

<td>

{{ $activityLog->ip_address }}

</td>

</tr>

<tr>

<th>

Browser / Device

</th>

<td style="word-break: break-all;">

{{ $activityLog->user_agent }}

</td>

</tr>

<tr>

<th>

URL

</th>

<td style="word-break: break-all;">

{{ $activityLog->url }}

</td>

</tr>

<tr>

<th>

Created At

</th>

<td>

{{ $activityLog->created_at->format('d M Y h:i:s A') }}

</td>

</tr>

</table>

</div>

</div>

</div>

@endsection