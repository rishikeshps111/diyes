<input type="checkbox" class="toggle-btn department-status-toggle"
  data-toggle-url="{{ route('departments.toggle-status', $department) }}" @checked($department->is_active)>
