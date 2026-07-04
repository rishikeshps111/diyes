<input type="checkbox" class="toggle-btn subject-status-toggle"
  data-toggle-url="{{ route('subjects.toggle-status', $subject) }}" @checked($subject->is_active)>
