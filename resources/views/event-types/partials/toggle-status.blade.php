<input type="checkbox" class="toggle-btn event-type-status-toggle"
  data-toggle-url="{{ route('event-types.toggle-status', $eventType) }}" @checked($eventType->is_active)>
