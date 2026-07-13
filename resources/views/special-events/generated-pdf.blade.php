<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937
        }

        h2 {
            color: #09650d;
            margin-bottom: 3px
        }

        h3 {
            margin-top: 18px
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 18px
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
            vertical-align: top
        }

        th {
            background: #f1f5f9
        }

        .meta {
            font-weight: bold;
            color: #475569
        }
    </style>
</head>

<body>
    <h2>{{ $specialEvent['title'] }}</h2>
    <div class="meta">Applicable: {{ $specialEvent['from'] }} to {{ $specialEvent['to'] }}</div>
    @foreach ($timetables as $tt)
        <h3>Grade {{ $tt['grade'] }} - {{ $tt['division'] }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Time</th>
                    @foreach ($tt['days'] as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($p = 1; $p <= $tt['total_periods']; $p++)
                    @php($pe = $tt['periods']->where('period_no', $p))@php($time = $pe->first())
                    <tr>
                        <th>Period {{ $p }}</th>
                        <td>{{ $time ? $time['start_time'] . ' - ' . $time['end_time'] : '-' }}</td>
                        @foreach ($tt['days'] as $day)
                            @php($entry = $pe->firstWhere('day', $day))
                            <td style="background-color: {{ $entry['color'] ?? '#ffffff' }}">
                                @if ($entry)
                                    <strong>{{ $entry['subject'] }}</strong><br><small>{{ implode(', ', $entry['teachers']) ?: '-' }}</small>
                                    @if ($entry['is_event_period'])
                                        <br>Special Event
                                    @elseif ($entry['is_project_period'])
                                        <br>Project Period
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
    @endforeach
</body>

</html>
