<input type="checkbox" class="toggle-btn venue-status-toggle"
  data-toggle-url="{{ route('venues.toggle-status', $venue) }}" @checked($venue->is_active)>
