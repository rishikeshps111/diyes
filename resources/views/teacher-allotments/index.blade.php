@extends('layouts.app')
@section('title', 'Teacher Allotment')
@push('styles')<style>
.allotment-table th,.allotment-table td{min-width:140px;padding:8px;text-align:center;vertical-align:top}.allotment-table .period-column{min-width:85px}.allotment-entry{border-radius:6px;margin-bottom:5px;padding:7px}.allotment-title{display:block;font-weight:700}.allotment-meta{color:#536176;display:block;font-size:11px;margin-top:2px}.allotment-badge{border-radius:10px;display:inline-block;font-size:9px;font-weight:700;margin-top:4px;padding:2px 6px}.regular{background:#f1f5f9}.special{background:#dbeafe;color:#1d4ed8}.project{background:#dcfce7;color:#15803d}.substitute{background:#f3e8ff;color:#7e22ce}
</style>@endpush
@section('content')
<div class="page-title"><h3>Teacher Allotment</h3><nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item">Teacher Management</li><li class="breadcrumb-item active">Teacher Allotment</li></ol></nav></div>
<section class="section dashboard">
  <div class="main-table-container mb-3"><form method="GET" action="{{ route('teacher-allotments.index') }}" id="teacherAllotmentForm"><div class="row align-items-end">
    <div class="col-lg-4 mb-3"><div class="o-f-inp"><label>Teacher <span class="text-danger">*</span></label><select name="teacher_id" id="allotmentTeacher" class="form-select shadow-none" required><option value="">--- Select ---</option>@foreach($teachers as $option)<option value="{{ $option->id }}" @selected($filters['teacher_id']==$option->id)>{{ $option->name }} ({{ $option->employee_id }})</option>@endforeach</select></div></div>
    <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>From Date <span class="text-danger">*</span></label><input type="date" name="from_date" class="form-control shadow-none" value="{{ $filters['from_date'] }}" required></div></div>
    <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>To Date <span class="text-danger">*</span></label><input type="date" name="to_date" class="form-control shadow-none" value="{{ $filters['to_date'] }}" required></div></div>
    <div class="col-lg-2 mb-3"><button class="btn btn-primary w-100" id="generateAllotment" type="submit"><i class="fa-solid fa-calendar-days me-1"></i> Generate</button></div>
  </div></form></div>
  @if($teacher)
    <div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">{{ $teacher->name }} <small class="text-muted">{{ $filters['from_date'] }} to {{ $filters['to_date'] }}</small></h4><a class="btn btn-danger" id="allotmentPdf" href="{{ route('teacher-allotments.pdf', $filters) }}"><i class="fa-solid fa-file-pdf me-1"></i> Download PDF</a></div>
    @foreach($previews as $preview)
      <div class="main-table-container mb-3"><h5>{{ $preview['week_start']->format('d M') }} – {{ $preview['week_end']->format('d M Y') }}</h5><div class="table-responsive"><table class="table table-bordered allotment-table mb-0"><thead><tr><th class="period-column">Period</th>@foreach($preview['days'] as $day=>$date)<th>{{ $day }}<small class="d-block text-muted">{{ $date->format('d M') }}</small></th>@endforeach</tr></thead><tbody>
      @for($period=1;$period<=$preview['periods'];$period++)<tr><th>Period {{ $period }}</th>@foreach($preview['days'] as $day=>$date)@php($entries=$preview['cells']->get($day.'|'.$period,[]))<td>@forelse($entries as $cell)<div class="allotment-entry {{ $cell['type'] }}" style="background-color: {{ $cell['color'] ?? '#ffffff' }}"><span class="allotment-title">{{ $cell['title'] }}</span><span class="allotment-meta">Grade {{ $cell['grade'] }} / {{ $cell['division'] }}</span><span class="allotment-meta">{{ $cell['time'] }}</span>@if(!empty($cell['original_teacher']))<span class="allotment-meta">For: {{ $cell['original_teacher'] }}</span>@endif<span class="allotment-badge">{{ $cell['label'] }}</span></div>@empty<span class="text-muted">—</span>@endforelse</td>@endforeach</tr>@endfor
      @if($preview['periods']===0)<tr><td colspan="7" class="text-center text-muted py-4">No assignments found for this week.</td></tr>@endif</tbody></table></div></div>
    @endforeach
    @if($previews->isEmpty())
      <div class="main-table-container text-center text-muted py-4">No timetable assignments were found for this teacher in the selected date range.</div>
    @endif
  @endif
</section>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',function(){if(window.jQuery&&jQuery.fn.select2)jQuery('#allotmentTeacher').select2({width:'100%',placeholder:'--- Select ---'});document.getElementById('teacherAllotmentForm').addEventListener('submit',function(){const b=document.getElementById('generateAllotment');b.disabled=true;b.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Generating...'});});</script>@endpush
