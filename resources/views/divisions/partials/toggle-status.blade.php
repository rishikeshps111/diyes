<input type="checkbox" class="toggle-btn division-status-toggle"
  data-toggle-url="{{ route('divisions.toggle-status', $division) }}" @checked($division->is_active)>
