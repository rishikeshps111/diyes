<input type="checkbox" class="toggle-btn project-category-status-toggle"
  data-toggle-url="{{ route('project-categories.toggle-status', $projectCategory) }}" @checked($projectCategory->is_active)>
