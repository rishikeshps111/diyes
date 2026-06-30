<input type="checkbox" class="toggle-btn grade-status-toggle"
  data-toggle-url="{{ route('grades.toggle-status', $grade) }}" @checked($grade->is_active)>
