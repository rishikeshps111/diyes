@extends('layouts.app')
@section('title','Teacher Dashboard')
@section('content')
<div class="page-title"><h3>Welcome, {{ $teacher->name }}</h3></div>
<section class="section dashboard">
  <div class="card border-0 shadow-sm mb-4"><div class="card-body d-flex align-items-center gap-3 py-4">
    <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fa-solid fa-calendar-days fa-xl"></i></div>
    <div><div class="text-muted small">Current Financial Year</div><h4 class="mb-0">{{ $academicYear?->academic_year ?? 'Not set' }}</h4></div>
  </div></div>
  <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white"><h5 class="mb-0">Regular Timetable</h5></div><div class="card-body">@include('teachers.portal._schedule',['preview'=>$regularPreview,'showDates'=>false])</div></div>
  @foreach($schedulePreviews as $schedule)
    @if($schedule['preview']['cells']->isNotEmpty())
      <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white"><h5 class="mb-0">{{ $schedule['title'] }} <small class="text-muted">({{ $schedule['preview']['week_start']->format('d M') }} - {{ $schedule['preview']['week_end']->format('d M Y') }})</small></h5></div><div class="card-body">@include('teachers.portal._schedule',['preview'=>$schedule['preview'],'showDates'=>true])</div></div>
    @endif
  @endforeach
</section>
@endsection
