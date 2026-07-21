<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Teacher Scheduler</title><style>
body{font-family:DejaVu Sans,sans-serif;color:#1f2937;font-size:10px}h2{margin:0 0 5px;color:#09650d}.meta{margin-bottom:12px;color:#475569;font-weight:bold}table{border-collapse:collapse;width:100%}th,td{border:1px solid #94a3b8;padding:6px;text-align:center;vertical-align:top}th{background:#f1f5f9}.title{font-weight:bold}.small{color:#475569;font-size:9px;margin-top:2px}.badge{font-size:8px;font-weight:bold;margin-top:4px}.substitute{color:#7e22ce}.special{color:#1d4ed8}.project{color:#15803d}
</style></head><body>
<h2>Teacher Scheduler - {{ $teacher->name }}</h2>
<div class="meta">{{ $preview['week_start']->format('d M') }} - {{ $preview['week_end']->format('d M Y') }} | All assigned classes</div>
<table><thead><tr><th>Period</th>@foreach($preview['days'] as $day => $date)<th>{{ $day }}<br>{{ $date->format('d M') }}</th>@endforeach</tr></thead><tbody>
@for($period=1;$period<=$preview['periods'];$period++)<tr><th>Period {{ $period }}</th>@foreach($preview['days'] as $day=>$date)@php($entries=$preview['cells']->get($day.'|'.$period,[]))<td>@forelse($entries as $cell)<div style="background-color:{{ $cell['color'] ?? '#fff' }};padding:4px;margin-bottom:4px"><div class="title">{{ $cell['title'] }}</div><div class="small">Grade {{ $cell['grade'] }} / {{ $cell['division'] }}</div><div class="small">{{ $cell['time'] }}</div>@if(!empty($cell['original_teacher']))<div class="small">For: {{ $cell['original_teacher'] }}</div>@endif<div class="badge {{ $cell['type'] }}">{{ $cell['label'] }}</div></div>@empty—@endforelse</td>@endforeach</tr>@endfor
@if($preview['periods']===0)<tr><td colspan="7">No timetable assignments found for this teacher in the current week.</td></tr>@endif
</tbody></table></body></html>
