@extends('layouts.app')

@section('title', 'Generate Timetable')

@push('styles')
  <style>
    .weekly-table th, .weekly-table td { min-width: 145px; padding: 10px; text-align: center; vertical-align: top; }
    .weekly-table .period-column { min-width: 90px; }
    .weekly-cell-title { color: #172033; display: block; font-weight: 700; }
    .weekly-cell-meta { color: #536176; display: block; font-size: 11px; margin-top: 3px; }
    .weekly-badge { border-radius: 12px; display: inline-block; font-size: 10px; font-weight: 700; margin-top: 6px; padding: 3px 7px; }
    .type-regular { background: #f1f5f9; color: #475569; }
    .type-special { background: #2563eb; color: white; }
    .type-project { background: #16a34a; color: white; }
    .type-substitute { background: #7e22ce; color: white; }
    .legend-swatch { border: 1px solid #d1d5db; border-radius: 4px; display: inline-block; height: 16px; margin-right: 5px; vertical-align: -3px; width: 16px; }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Generate Timetable</h3>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item">Timetable Management</li><li class="breadcrumb-item active">Generate Timetable</li></ol></nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <form method="GET" action="{{ route('generate-timetable.index') }}" id="generateTimetableForm">
        <input type="hidden" name="types_present" value="1">
        <div class="row align-items-end">
          <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>Academic Year <span class="text-danger">*</span></label><select name="academic_year_id" id="generatedAcademicYear" class="form-select shadow-none" required><option value="">--- Select ---</option>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected($filters['academic_year_id'] == $year->id)>{{ $year->academic_year }}</option>@endforeach</select></div></div>
          <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>Grade <span class="text-danger">*</span></label><select name="grade_id" id="generatedGrade" class="form-select shadow-none" required><option value="">--- Select ---</option>@foreach($grades as $grade)<option value="{{ $grade->id }}" data-year="{{ $grade->academic_year_id }}" @selected($filters['grade_id'] == $grade->id)>{{ $grade->grade }}</option>@endforeach</select></div></div>
          <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>Division <span class="text-danger">*</span></label><select name="division_id" id="generatedDivision" class="form-select shadow-none" required><option value="">--- Select ---</option>@foreach($divisions as $division)<option value="{{ $division->id }}" data-grade="{{ $division->grade_id }}" @selected($filters['division_id'] == $division->id)>{{ $division->division }}</option>@endforeach</select></div></div>
          <div class="col-lg-3 mb-3"><button class="btn btn-primary w-100" id="generatePreviewButton" type="submit"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generate Preview</button></div>
        </div>
        <div class="d-flex flex-wrap gap-4 mt-1">
          @foreach(['regular' => 'Regular', 'special' => 'Special Event', 'project' => 'Project Week', 'substitute' => 'Substitute Allotted'] as $value => $label)
            <div class="form-check"><input class="form-check-input" type="checkbox" name="types[]" value="{{ $value }}" id="type_{{ $value }}" @checked(in_array($value, $filters['types']))><label class="form-check-label" for="type_{{ $value }}">{{ $label }}</label></div>
          @endforeach
        </div>
      </form>
    </div>

    @if($preview)
      <div class="main-table-container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
          <div><h5 class="mb-1">Current Week: {{ $preview['week_start']->format('d M') }} – {{ $preview['week_end']->format('d M Y') }}</h5><span class="text-muted">Academic Year: {{ $preview['academic_year'] }} | Grade: {{ $preview['grade'] }} | Division: {{ $preview['division'] }}</span></div>
          <a class="btn btn-danger" id="downloadTimetablePdf" href="{{ route('generate-timetable.pdf', request()->query()) }}"><i class="fa-solid fa-file-pdf me-1"></i> Download PDF</a>
        </div>
        <div class="d-flex flex-wrap gap-3 mb-3">
          <span><i class="legend-swatch" style="background:#dbeafe"></i>Special Event</span><span><i class="legend-swatch" style="background:#dcfce7"></i>Project Week</span><span><i class="legend-swatch" style="background:#f3e8ff"></i><i class="fa-solid fa-user-clock me-1"></i>Substitute</span><span><i class="legend-swatch" style="background:#fff"></i>Subject color</span>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered weekly-table mb-0">
            <thead><tr><th class="period-column">Period</th>@foreach($preview['days'] as $day => $date)<th>{{ $day }}<small class="d-block text-muted">{{ $date->format('d M') }}</small></th>@endforeach</tr></thead>
            <tbody>
              @for($period = 1; $period <= $preview['periods']; $period++)
                <tr><th>Period {{ $period }}</th>@foreach($preview['days'] as $day => $date)@php($cell = $preview['cells']->get($day.'|'.$period))<td style="background-color:{{ $cell['color'] ?? '#fff' }}">@if($cell)<span class="weekly-cell-title">{{ $cell['title'] }}</span><span class="weekly-cell-meta">{{ $cell['time'] }}</span><span class="weekly-cell-meta">@if($cell['type'] === 'substitute')<i class="fa-solid fa-user-clock me-1"></i>@endif{{ $cell['teachers'] ?: '-' }}</span>@if(!empty($cell['original_teacher']))<span class="weekly-cell-meta text-decoration-line-through">{{ $cell['original_teacher'] }}</span>@endif<span class="weekly-badge type-{{ $cell['type'] }}">{{ $cell['label'] }}</span>@else<span class="text-muted">—</span>@endif</td>@endforeach</tr>
              @endfor
              @if($preview['periods'] === 0)
                <tr><td colspan="7" class="text-center text-muted py-4">No timetable entries found for this selection and current week.</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const year = document.getElementById('generatedAcademicYear'), grade = document.getElementById('generatedGrade'), division = document.getElementById('generatedDivision');
      const form = document.getElementById('generateTimetableForm'), generateButton = document.getElementById('generatePreviewButton'), pdfButton = document.getElementById('downloadTimetablePdf');

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#generatedAcademicYear, #generatedGrade, #generatedDivision').select2({ width: '100%', placeholder: '--- Select ---' });
      }

      function refreshSelect(select) {
        if (window.jQuery && jQuery.fn.select2) jQuery(select).trigger('change.select2');
      }

      function filterOptions(select, attribute, value) {
        Array.from(select.options).forEach(function(option, index) {
          if (!index) return;
          option.disabled = Boolean(value) && option.dataset[attribute] !== value;
          if (option.disabled && option.selected) select.value = '';
        });
        refreshSelect(select);
      }

      year.addEventListener('change', function(){ filterOptions(grade, 'year', this.value); filterOptions(division, 'grade', grade.value); });
      grade.addEventListener('change', function(){ filterOptions(division, 'grade', this.value); });
      filterOptions(grade, 'year', year.value); filterOptions(division, 'grade', grade.value);

      form.addEventListener('submit', function () {
        generateButton.disabled = true;
        generateButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Generating...';
      });

      if (pdfButton) pdfButton.addEventListener('click', function () {
        if (pdfButton.classList.contains('disabled')) return;
        const originalHtml = pdfButton.innerHTML;
        pdfButton.classList.add('disabled');
        pdfButton.setAttribute('aria-disabled', 'true');
        pdfButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Preparing PDF...';
        window.setTimeout(function () {
          pdfButton.classList.remove('disabled');
          pdfButton.removeAttribute('aria-disabled');
          pdfButton.innerHTML = originalHtml;
        }, 3000);
      });
    });
  </script>
@endpush
