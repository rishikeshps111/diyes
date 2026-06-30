<input type="checkbox" class="toggle-btn designation-status-toggle"
  data-toggle-url="{{ route('designations.toggle-status', $designation) }}" @checked($designation->is_active)>
