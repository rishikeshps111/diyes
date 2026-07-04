<input type="checkbox" class="toggle-btn time-table-type-status-toggle"
  data-toggle-url="{{ route('time-table-types.toggle-status', $timeTableType) }}" @checked($timeTableType->is_active)>
