<input type="checkbox" class="toggle-btn time-table-category-status-toggle"
  data-toggle-url="{{ route('time-table-categories.toggle-status', $timeTableCategory) }}" @checked($timeTableCategory->is_active)>
