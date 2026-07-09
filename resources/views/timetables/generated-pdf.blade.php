<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ $timetable['name'] ?? 'Generated Timetable' }}</title>
  <style>
    body {
      color: #1f2937;
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
    }

    h2 {
      color: #09650d;
      margin: 0 0 4px;
    }

    .meta {
      color: #475569;
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 14px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      border: 1px solid #cbd5e1;
      padding: 7px;
      text-align: center;
      vertical-align: top;
    }

    th {
      background: #f1f5f9;
      color: #334155;
      font-weight: 700;
      white-space: nowrap;
    }

    .subject {
      color: #111827;
      display: block;
      font-weight: 700;
    }

    .teacher {
      color: #475569;
      display: block;
      font-size: 10px;
      line-height: 1.45;
      margin-top: 3px;
    }

    .break td {
      background: #fff7ed;
      color: #9a3412;
      font-weight: 700;
    }

    .lunch td {
      background: #ecfdf5;
      color: #047857;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <h2>{{ $timetable['name'] ?? 'Generated Timetable' }}</h2>
  <div class="meta">
    Grade: {{ $timetable['grade'] ?? '-' }} | Divisions: {{ $timetable['divisions'] ?? '-' }}
  </div>

  <table>
    <thead>
      <tr>
        <th>Period</th>
        <th>Time</th>
        @foreach ($days as $day)
          <th>{{ $day }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @if ($periods->isEmpty())
        <tr>
          <td colspan="{{ max(count($days) + 2, 1) }}">No generated timetable entries found.</td>
        </tr>
      @else
        @for ($period = 1; $period <= (int) ($timetable['total_periods'] ?? 0); $period++)
          @php
            $periodEntries = $periods->where('period_no', $period);
            $timeEntry = $periodEntries->first(fn($entry) => ! empty($entry['start_time']) && ! empty($entry['end_time']));
          @endphp
          <tr>
            <th>Period {{ $period }}</th>
            <td>{{ $timeEntry ? $timeEntry['start_time'].' - '.$timeEntry['end_time'] : '-' }}</td>
            @foreach ($days as $day)
              @php
                $entry = $periodEntries->firstWhere('day', $day);
              @endphp
              @if ($entry)
                <td style="background-color: {{ $entry['color'] ?? '#ffffff' }};">
                  <span class="subject">{{ $entry['subject'] ?? '-' }}</span>
                  <span class="teacher">
                    @forelse ($entry['teachers'] as $teacherIndex => $teacher)
                      T{{ $teacherIndex + 1 }}: {{ $teacher }}@if (! $loop->last)<br>@endif
                    @empty
                      -
                    @endforelse
                  </span>
                </td>
              @else
                <td>-</td>
              @endif
            @endforeach
          </tr>

          @foreach (['short_break', 'lunch_break'] as $breakType)
            @php
              $breakEntry = $breaks->first(fn($entry) => (int) $entry['period_no'] === $period && $entry['type'] === $breakType);
            @endphp
            @if ($breakEntry)
              <tr class="{{ $breakType === 'lunch_break' ? 'lunch' : 'break' }}">
                <td colspan="{{ count($days) + 2 }}">
                  {{ $breakEntry['label'] }} ({{ $breakEntry['duration_minutes'] }} mins) - {{ $breakEntry['start_time'] }} - {{ $breakEntry['end_time'] }}
                </td>
              </tr>
            @endif
          @endforeach
        @endfor
      @endif
    </tbody>
  </table>
</body>
</html>
