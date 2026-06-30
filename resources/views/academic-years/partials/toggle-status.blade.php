<input type="checkbox" class="toggle-btn academic-year-status-toggle"
  data-toggle-url="{{ route('academic-years.toggle-status', $academicYear) }}" @checked($academicYear->is_active)>
