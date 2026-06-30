<input type="checkbox" class="toggle-btn holiday-status-toggle"
  data-toggle-url="{{ route('holidays.toggle-status', $holiday) }}" @checked($holiday->is_active)>
