<div class="table-responsive">
  @if($preview['cells']->isEmpty())
    <div class="text-center text-muted py-4">No data available</div>
  @else
    <table class="table table-bordered align-middle mb-0">
      <thead><tr><th style="min-width:120px">Day</th>@for($period=1;$period<=max(1,$preview['periods']);$period++)<th>Period {{ $period }}</th>@endfor</tr></thead>
      <tbody>
      @foreach($preview['days'] as $day => $date)
        <tr><th>{{ $day }}@if($showDates ?? true)<small class="d-block text-muted">{{ $date->format('d M') }}</small>@endif</th>
        @for($period=1;$period<=max(1,$preview['periods']);$period++)
          <td>
            @forelse(collect($preview['cells']->get($day.'|'.$period, [])) as $cell)
              <div class="rounded p-2 mb-1" style="background:{{ $cell['color'] ?? '#eef2ff' }}">
                <strong>{{ $cell['title'] ?? '-' }}</strong>
                @if($cell['grade'] ?? null)<small class="d-block">{{ $cell['grade']->grade ?? '' }} {{ $cell['division']->division ?? '' }}</small>@endif
                @if($cell['time'] ?? null)<small class="d-block">{{ $cell['time'] }}</small>@endif
              </div>
            @empty
              <span class="text-muted">—</span>
            @endforelse
          </td>
        @endfor</tr>
      @endforeach
      </tbody>
    </table>
  @endif
</div>
