<input type="checkbox" class="toggle-btn classroom-status-toggle"
  data-toggle-url="{{ route('classrooms.toggle-status', $classroom) }}" @checked($classroom->is_active)>
