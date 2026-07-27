<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Teacher Work Load</title>
  <style>
    body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
    h2 { color: #09650d; margin: 0 0 4px; }
    .meta { font-weight: bold; margin-bottom: 12px; }
    table { border-collapse: collapse; margin-bottom: 16px; page-break-inside: avoid; width: 100%; }
    th, td { border: 1px solid #94a3b8; padding: 5px; text-align: center; vertical-align: top; }
    th { background: #f1f5f9; }
    .entry { margin-bottom: 3px; padding: 3px; }
    .title { font-weight: bold; }
    .small { color: #475569; font-size: 8px; }
    .week { page-break-inside: avoid; }
  </style>
</head>
<body>
  <h2>Teacher Work Load - {{ $teacher->name }}</h2>
  <div class="meta">
    {{ ucwords(str_replace('_', ' ', $filters['timetable_type'])) }}
    @if ($filters['timetable_type'] !== 'regular' && $filters['from_date'] && $filters['to_date'])
      | {{ $filters['from_date'] }} to {{ $filters['to_date'] }}
    @endif
  </div>

  @foreach ($previews as $preview)
    <div class="week">
      <h3>
        {{ $preview['show_dates']
          ? $preview['week_start']->format('d M').' - '.$preview['week_end']->format('d M Y')
          : 'Regular Time Table' }}
      </h3>
      <table>
        <thead>
          <tr>
            <th>Period</th>
            @foreach ($preview['days'] as $day => $date)
              <th>{{ $day }}@if ($preview['show_dates'])<br>{{ $date->format('d M') }}@endif</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @for ($period = 1; $period <= $preview['periods']; $period++)
            <tr>
              <th>Period {{ $period }}</th>
              @foreach ($preview['days'] as $day => $date)
                @php($entries = $preview['cells']->get($day.'|'.$period, []))
                <td>
                  @forelse ($entries as $cell)
                    <div class="entry" style="background: {{ $cell['color'] ?? '#fff' }}">
                      <div class="title">{{ $cell['title'] }}</div>
                      @if (!empty($cell['grade']) || !empty($cell['division']))
                        <div class="small">Grade {{ $cell['grade'] ?? '-' }} / {{ $cell['division'] ?? '-' }}</div>
                      @endif
                      @if (!empty($cell['meta']))<div class="small">{{ $cell['meta'] }}</div>@endif
                      <div class="small">{{ $cell['time'] }} | {{ $cell['label'] }}</div>
                      @if (!empty($cell['original_teacher']))<div class="small">For: {{ $cell['original_teacher'] }}</div>@endif
                    </div>
                  @empty
                    —
                  @endforelse
                </td>
              @endforeach
            </tr>
          @endfor
          @if ($preview['periods'] === 0)
            <tr><td colspan="7">No assignments found.</td></tr>
          @endif
        </tbody>
      </table>
    </div>
  @endforeach

  @if ($previews->isEmpty())
    <p>No data available.</p>
  @endif
</body>
</html>
