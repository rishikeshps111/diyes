@props(['label', 'value' => null])

<div class="col-lg-4">
  <strong>{{ $label }}</strong>
  <div>{{ filled($value) ? $value : '-' }}</div>
</div>
