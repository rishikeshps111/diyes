@extends('layouts.app')

@section('title', $projectWeek->exists ? 'Edit Project Week' : 'Add Project Week')

@push('styles')
  <style>
    .publish-confirm-modal .modal-content {
      border: 0;
      border-radius: 14px;
      box-shadow: 0 24px 70px rgba(16, 24, 40, 0.18);
      overflow: hidden;
    }

    .publish-confirm-modal .modal-header,
    .publish-confirm-modal .modal-footer {
      background: #f8fafc;
      border-color: #e7edf3;
      padding: 18px 22px;
    }

    .publish-confirm-modal .modal-title {
      color: #1f2937;
      font-size: 20px;
      font-weight: 700;
    }

    .publish-confirm-subtitle {
      color: #667085;
      font-size: 13px;
      margin: 3px 0 0;
    }

    .publish-confirm-modal .modal-body {
      background: #fff;
      padding: 20px 22px;
    }

    .publish-summary-grid {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .publish-summary-item {
      background: #f9fafb;
      border: 1px solid #edf1f5;
      border-radius: 10px;
      padding: 12px 14px;
    }

    .publish-summary-item.full-width {
      grid-column: 1 / -1;
    }

    .publish-summary-label {
      color: #667085;
      display: block;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 5px;
      text-transform: uppercase;
    }

    .publish-summary-value {
      color: #111827;
      font-size: 14px;
      font-weight: 600;
      overflow-wrap: anywhere;
    }

    @media (max-width: 767px) {
      .publish-summary-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endpush

@section('content')
  @php
    $createdByName = $projectWeek->exists ? ($projectWeek->creator?->name ?? '-') : (auth()->user()?->name ?? '-');
    $createdTime = $projectWeek->exists
      ? ($projectWeek->created_at?->format('d M Y h:i A') ?? '-')
      : now()->format('d M Y h:i A');
  @endphp

  <div class="page-title">
    <h3>Project Week</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('project-weeks.index') }}">Project Week</a></li>
        <li class="breadcrumb-item active">{{ $projectWeek->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="projectWeekForm"
          action="{{ $projectWeek->exists ? route('project-weeks.update', $projectWeek) : route('project-weeks.store') }}">
          @csrf
          @if ($projectWeek->exists)
            @method('PUT')
          @endif
          <input type="hidden" name="status" id="status" value="{{ old('status', $projectWeek->status ?? 'draft') }}">

          <div class="main-table-container mb-3">
            <h5 class="title-w-sec">Project Week Information</h5>
            <hr>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $projectWeek->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="project_id">Project <span class="text-danger">*</span></label>
                <select name="project_id" id="project_id"
                  class="form-select shadow-none @error('project_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(old('project_id', $projectWeek->project_id) == $project->id)>
                      {{ $project->project_title }} ({{ $project->project_code }})
                    </option>
                  @endforeach
                </select>
                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="academic_year_id">Academic Year <span class="text-danger">*</span></label>
                <select name="academic_year_id" id="academic_year_id"
                  class="form-select shadow-none @error('academic_year_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $projectWeek->academic_year_id) == $academicYear->id)>
                      {{ $academicYear->academic_year }}
                    </option>
                  @endforeach
                </select>
                @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="applicable_from">Applicable From <span class="text-danger">*</span></label>
                <input type="date" name="applicable_from" id="applicable_from"
                  class="form-control shadow-none @error('applicable_from') is-invalid @enderror"
                  min="{{ $projectWeek->exists ? '' : now()->toDateString() }}"
                  value="{{ old('applicable_from', $projectWeek->applicable_from?->toDateString()) }}">
                @error('applicable_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="applicable_to">Applicable To <span class="text-danger">*</span></label>
                <input type="date" name="applicable_to" id="applicable_to"
                  class="form-control shadow-none @error('applicable_to') is-invalid @enderror"
                  value="{{ old('applicable_to', $projectWeek->applicable_to?->toDateString()) }}">
                @error('applicable_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="grade_id">Grade <span class="text-danger">*</span></label>
                <select name="grade_id" id="grade_id"
                  class="form-select shadow-none @error('grade_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(old('grade_id', $projectWeek->grade_id) == $grade->id)>
                      {{ $grade->grade }}{{ $grade->academicYear ? ' - ' . $grade->academicYear->academic_year : '' }}
                    </option>
                  @endforeach
                </select>
                @error('grade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="division_id">Division <span class="text-danger">*</span></label>
                @php
                  $selectedDivisionId = (int) old('division_id', collect($selectedDivisionIds ?? [])->first());
                @endphp
                <select name="division_id" id="division_id"
                  class="form-select shadow-none @error('division_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" data-grade-id="{{ $division->grade_id }}"
                      @selected($selectedDivisionId === (int) $division->id)>
                      {{ $division->grade?->grade ? $division->grade->grade . ' - ' : '' }}{{ $division->division }}
                    </option>
                  @endforeach
                </select>
                @error('division_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="total_periods">Total Periods <span class="text-danger">*</span></label>
                <input type="number" name="total_periods" id="total_periods" min="1"
                  class="form-control shadow-none @error('total_periods') is-invalid @enderror"
                  value="{{ old('total_periods', $projectWeek->total_periods) }}">
                @error('total_periods')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="created_by">Created By</label>
                <input type="text" id="created_by" class="form-control shadow-none" value="{{ $createdByName }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="created_time">Created Time</label>
                <input type="text" id="created_time" class="form-control shadow-none" value="{{ $createdTime }}" disabled>
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description"
                  class="form-control shadow-none @error('description') is-invalid @enderror">{{ old('description', $projectWeek->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('project-weeks.index') }}" class="btn btn-danger">Cancel</a>
              @if ($projectWeek->exists && $projectWeek->status === 'publish')
                <button type="submit" class="submit-btn project-week-submit-btn" data-status="publish"
                  data-loading-text="Updating...">Update</button>
              @else
                <button type="submit" class="submit-btn project-week-submit-btn" data-status="draft"
                  data-loading-text="Saving...">Save as Draft</button>
                <button type="submit" class="submit-btn project-week-submit-btn" data-status="publish"
                  data-loading-text="Publishing...">Publish</button>
              @endif
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <div class="modal fade publish-confirm-modal" id="publishConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">Please confirm the Data</h5>
            <p class="publish-confirm-subtitle">Review the details before publishing this project week.</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="publish-summary-grid">
            <div class="publish-summary-item">
              <span class="publish-summary-label">Code</span>
              <div class="publish-summary-value" id="confirm_code">-</div>
            </div>
            <div class="publish-summary-item">
              <span class="publish-summary-label">Project</span>
              <div class="publish-summary-value" id="confirm_project">-</div>
            </div>
            <div class="publish-summary-item">
              <span class="publish-summary-label">Applicable From</span>
              <div class="publish-summary-value" id="confirm_applicable_from">-</div>
            </div>
            <div class="publish-summary-item">
              <span class="publish-summary-label">Applicable To</span>
              <div class="publish-summary-value" id="confirm_applicable_to">-</div>
            </div>
            <div class="publish-summary-item">
              <span class="publish-summary-label">Academic Year</span>
              <div class="publish-summary-value" id="confirm_academic_year">-</div>
            </div>
            <div class="publish-summary-item">
              <span class="publish-summary-label">Grade</span>
              <div class="publish-summary-value" id="confirm_grade">-</div>
            </div>
            <div class="publish-summary-item full-width">
              <span class="publish-summary-label">Division</span>
              <div class="publish-summary-value" id="confirm_divisions">-</div>
            </div>
            <div class="publish-summary-item">
              <span class="publish-summary-label">Total Periods</span>
              <div class="publish-summary-value" id="confirm_total_periods">-</div>
            </div>
            <div class="publish-summary-item full-width">
              <span class="publish-summary-label">Description</span>
              <div class="publish-summary-value" id="confirm_description">-</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">No</button>
          <button type="button" class="btn btn-success border-0" id="confirmPublishBtn">Yes</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('projectWeekForm');
      const statusInput = document.getElementById('status');
      const submitButtons = document.querySelectorAll('.project-week-submit-btn');
      const fromInput = document.getElementById('applicable_from');
      const toInput = document.getElementById('applicable_to');
      const gradeSelect = document.getElementById('grade_id');
      const divisionSelect = document.getElementById('division_id');
      const publishConfirmModalElement = document.getElementById('publishConfirmModal');
      const publishConfirmModal = publishConfirmModalElement ? new bootstrap.Modal(publishConfirmModalElement) : null;
      const confirmPublishButton = document.getElementById('confirmPublishBtn');
      const divisionOptions = Array.from(divisionSelect.options).map(function (option) {
        return {
          value: option.value,
          text: option.textContent,
          gradeId: option.dataset.gradeId || '',
          selected: option.selected
        };
      });
      let confirmedPublish = false;
      let pendingSubmitButton = null;

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#project_id, #academic_year_id, #grade_id, #division_id').select2({
          width: '100%',
          placeholder: '--- Select ---',
          allowClear: true
        });

        jQuery(gradeSelect).on('change', filterDivisions);
      }

      submitButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          statusInput.value = button.dataset.status;
          pendingSubmitButton = button;

          submitButtons.forEach(function (submitButton) {
            if (submitButton !== button) {
              submitButton.disabled = true;
            }
          });
        });
      });

      form.addEventListener('submit', function (event) {
        const activeButton = document.activeElement && document.activeElement.classList.contains('project-week-submit-btn')
          ? document.activeElement
          : submitButtons[0];

        if (statusInput.value === 'publish' && !confirmedPublish) {
          event.preventDefault();
          pendingSubmitButton = pendingSubmitButton || activeButton;

          fillPublishConfirmation();
          publishConfirmModal.show();

          return;
        }

        submitButtons.forEach(function (button) {
          button.disabled = true;
        });

        const submitButton = pendingSubmitButton || activeButton;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });

      fromInput.addEventListener('change', syncToMinDate);
      confirmPublishButton.addEventListener('click', function () {
        confirmedPublish = true;
        publishConfirmModal.hide();
        pendingSubmitButton.click();
      });

      publishConfirmModalElement.addEventListener('hidden.bs.modal', function () {
        if (confirmedPublish) {
          return;
        }

        submitButtons.forEach(function (button) {
          button.disabled = false;
        });
        pendingSubmitButton = null;
      });

      syncToMinDate();
      filterDivisions();

      function syncToMinDate() {
        toInput.min = fromInput.value || '';

        if (toInput.value && fromInput.value && toInput.value <= fromInput.value) {
          toInput.value = '';
        }
      }

      function filterDivisions() {
        const gradeId = gradeSelect.value;
        const selectedValue = divisionSelect.value;

        divisionSelect.innerHTML = '';
        divisionSelect.appendChild(new Option('--- Select ---', '', false, !selectedValue));

        divisionOptions
          .filter(function (option) {
            return option.value && (!gradeId || option.gradeId === gradeId);
          })
          .forEach(function (option) {
            const element = new Option(option.text, option.value, false, selectedValue === option.value);
            element.dataset.gradeId = option.gradeId;
            divisionSelect.appendChild(element);
          });

        if (window.jQuery && jQuery.fn.select2) {
          jQuery(divisionSelect).trigger('change.select2');
        }
      }

      function fillPublishConfirmation() {
        setConfirmText('confirm_code', document.getElementById('code').value);
        setConfirmText('confirm_project', selectedText(document.getElementById('project_id')));
        setConfirmText('confirm_applicable_from', fromInput.value);
        setConfirmText('confirm_applicable_to', toInput.value);
        setConfirmText('confirm_academic_year', selectedText(document.getElementById('academic_year_id')));
        setConfirmText('confirm_grade', selectedText(gradeSelect));
        setConfirmText('confirm_divisions', selectedText(divisionSelect));
        setConfirmText('confirm_total_periods', document.getElementById('total_periods').value);
        setConfirmText('confirm_description', document.getElementById('description').value);
      }

      function setConfirmText(id, value) {
        document.getElementById(id).textContent = value || '-';
      }

      function selectedText(select) {
        const option = select.selectedOptions[0];

        return option && option.value ? option.textContent.trim() : '-';
      }

    });
  </script>
@endpush
