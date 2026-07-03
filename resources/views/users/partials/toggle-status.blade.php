<input type="checkbox" class="toggle-btn user-status-toggle"
  data-toggle-url="{{ route('users.toggle-status', $user) }}" @checked($user->is_active)>
