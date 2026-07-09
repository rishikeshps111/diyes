@extends('layouts.app')

@section('title', $project->exists ? 'Edit Project' : 'Add Project')

@section('content')
  @php
    $selectedSubjects = collect(old('subject_ids', $project->exists ? $project->subjects->pluck('id')->all() : []))->map(fn($id) => (int) $id)->all();
    $selectedGrades = collect(old('grade_ids', $project->exists ? $project->grades->pluck('id')->all() : []))->map(fn($id) => (int) $id)->all();
    $selectedTeachers = collect(old('teacher_ids', $project->exists ? $project->teachers->pluck('id')->all() : []))->map(fn($id) => (int) $id)->all();
    $creatorName = $project->exists ? ($project->creator?->name ?? '-') : auth()->user()?->name;
    $createdTime = $project->exists ? $project->created_at?->format('d M Y h:i A') : now()->format('d M Y h:i A');
    $teacherPickerData = $teachers->map(fn($teacher) => [
        'id' => $teacher->id,
        'name' => $teacher->name,
        'employee_id' => $teacher->employee_id,
        'image_url' => $teacher->imageUrl() ?: asset('assets/img/user.png'),
    ])->values();
  @endphp

  <div class="page-title">
    <h3>Projects</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Project Management</li>
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
        <li class="breadcrumb-item active">{{ $project->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="projectForm"
          action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}">
          @csrf
          @if ($project->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <h5 class="mb-3">Project Details</h5>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="project_code">Project Code <span class="text-danger">*</span></label>
                <input type="hidden" name="project_code" value="{{ old('project_code', $project->project_code) }}">
                <input type="text" id="project_code"
                  class="form-control shadow-none @error('project_code') is-invalid @enderror"
                  value="{{ old('project_code', $project->project_code) }}" maxlength="20" disabled>
                @error('project_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="project_title">Project Title <span class="text-danger">*</span></label>
                <input type="text" name="project_title" id="project_title"
                  class="form-control shadow-none @error('project_title') is-invalid @enderror"
                  value="{{ old('project_title', $project->project_title) }}" maxlength="200">
                @error('project_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="project_category_id">Category <span class="text-danger">*</span></label>
                <select name="project_category_id" id="project_category_id"
                  class="form-select shadow-none @error('project_category_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('project_category_id', $project->project_category_id) == $category->id)>
                      {{ $category->title }}
                    </option>
                  @endforeach
                </select>
                @error('project_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="duration_days">Duration (Days) <span class="text-danger">*</span></label>
                <input type="number" name="duration_days" id="duration_days"
                  class="form-control shadow-none @error('duration_days') is-invalid @enderror"
                  value="{{ old('duration_days', $project->duration_days) }}" min="1">
                @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date"
                  class="form-control shadow-none @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date"
                  class="form-control shadow-none @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}">
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="venue">Venue</label>
                <input type="text" name="venue" id="venue"
                  class="form-control shadow-none @error('venue') is-invalid @enderror"
                  value="{{ old('venue', $project->venue) }}">
                @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="status">Project Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select shadow-none @error('status') is-invalid @enderror">
                  @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $project->status ?: 'draft') === $value)>{{ $label }}
                    </option>
                  @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="created_by_display">Created By</label>
                <input type="text" id="created_by_display" class="form-control shadow-none" value="{{ $creatorName }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="created_time_display">Created Time</label>
                <input type="text" id="created_time_display" class="form-control shadow-none" value="{{ $createdTime }}" disabled>
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4"
                  class="form-control shadow-none @error('description') is-invalid @enderror"
                  maxlength="2000">{{ old('description', $project->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="subject_ids">Applied Subjects <span class="text-danger">*</span></label>
                <select name="subject_ids[]" id="subject_ids"
                  class="form-select shadow-none @error('subject_ids') is-invalid @enderror @error('subject_ids.*') is-invalid @enderror"
                  multiple>
                  @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(in_array($subject->id, $selectedSubjects, true))>
                      {{ $subject->subject_name }} ({{ $subject->subject_code }})
                    </option>
                  @endforeach
                </select>
                @error('subject_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('subject_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="grade_ids">Classes Applied For <span class="text-danger">*</span></label>
                <select name="grade_ids[]" id="grade_ids"
                  class="form-select shadow-none @error('grade_ids') is-invalid @enderror @error('grade_ids.*') is-invalid @enderror"
                  multiple>
                  @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(in_array($grade->id, $selectedGrades, true))>
                      {{ $grade->grade }}
                    </option>
                  @endforeach
                </select>
                @error('grade_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('grade_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="teacher_ids">Allocated Teachers <span class="text-danger">*</span></label>
                <div id="teacherHiddenInputs"></div>
                <button type="button" id="openTeacherPickerBtn" class="form-control shadow-none text-start teacher-picker-open @error('teacher_ids') is-invalid @enderror @error('teacher_ids.*') is-invalid @enderror">
                  Choose Teachers
                </button>
                <div id="selectedTeachersPreview" class="teacher-selected-preview mt-2"></div>
                @error('teacher_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('teacher_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label>Timetable Replacement <span class="text-danger">*</span></label>
                <div class="d-flex gap-3 mt-2">
                  <label class="d-inline-flex align-items-center gap-2">
                    <input type="radio" name="timetable_replacement" value="1" @checked((string) old('timetable_replacement', (int) $project->timetable_replacement) === '1')>
                    Yes
                  </label>
                  <label class="d-inline-flex align-items-center gap-2">
                    <input type="radio" name="timetable_replacement" value="0" @checked((string) old('timetable_replacement', (int) $project->timetable_replacement) === '0')>
                    No
                  </label>
                </div>
                @error('timetable_replacement')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('projects.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="projectSubmitBtn" class="submit-btn"
                data-loading-text="{{ $project->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $project->exists ? 'Update' : 'Submit' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <div class="modal fade" id="teacherPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Choose Allocated Teachers</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3" id="teacherPickerList"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="teacherPickerDoneBtn" class="btn btn-success">Done</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .teacher-picker-open {
      background: #fff;
      min-height: 42px;
    }

    .teacher-picker-card {
      align-items: center;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      gap: 12px;
      padding: 12px;
      transition: .2s;
    }

    .teacher-picker-card.is-selected {
      background: #f0f9eb;
      border-color: #409810;
      box-shadow: 0 0 0 2px rgba(64, 152, 16, .12);
    }

    .teacher-picker-card img,
    .teacher-selected-chip img {
      border-radius: 50%;
      height: 42px;
      object-fit: cover;
      width: 42px;
    }

    .teacher-picker-card h6 {
      font-size: 14px;
      font-weight: 700;
      margin: 0;
    }

    .teacher-picker-card small {
      color: #6b7280;
      font-size: 12px;
    }

    .teacher-selected-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      min-height: 26px;
    }

    .teacher-selected-chip {
      align-items: center;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      border-radius: 999px;
      display: inline-flex;
      gap: 8px;
      padding: 5px 10px 5px 5px;
    }

    .teacher-selected-chip span {
      font-size: 13px;
      font-weight: 600;
    }
  </style>
@endpush

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const projectForm = document.getElementById('projectForm');
      const submitButton = document.getElementById('projectSubmitBtn');
      const startDate = document.getElementById('start_date');
      const endDate = document.getElementById('end_date');
      const teacherPickerModal = new bootstrap.Modal(document.getElementById('teacherPickerModal'));
      const teacherPickerList = document.getElementById('teacherPickerList');
      const teacherHiddenInputs = document.getElementById('teacherHiddenInputs');
      const selectedTeachersPreview = document.getElementById('selectedTeachersPreview');
      const openTeacherPickerBtn = document.getElementById('openTeacherPickerBtn');
      const teacherPickerDoneBtn = document.getElementById('teacherPickerDoneBtn');
      const teachers = @json($teacherPickerData);
      const selectedTeacherIds = new Set(@json($selectedTeachers).map(String));

      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#project_category_id, #status, #subject_ids, #grade_ids').select2({
          width: '100%',
          placeholder: '--- Select ---',
          allowClear: true
        });
      }

      startDate?.addEventListener('change', syncDateRange);
      endDate?.addEventListener('change', syncDateRange);
      syncDateRange();

      openTeacherPickerBtn.addEventListener('click', function () {
        renderTeacherPicker();
        teacherPickerModal.show();
      });

      teacherPickerDoneBtn.addEventListener('click', function () {
        syncSelectedTeachers();
        teacherPickerModal.hide();
      });

      syncSelectedTeachers();

      if (!projectForm || !submitButton) {
        return;
      }

      projectForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });

      function syncDateRange() {
        if (startDate?.value) {
          endDate.min = startDate.value;
        }
        if (endDate?.value) {
          startDate.max = endDate.value;
        }
      }

      function renderTeacherPicker() {
        teacherPickerList.innerHTML = teachers.map(function (teacher) {
          const selected = selectedTeacherIds.has(String(teacher.id));

          return '<div class="col-lg-6">' +
            '<div class="teacher-picker-card ' + (selected ? 'is-selected' : '') + '" data-teacher-id="' + teacher.id + '">' +
            '<img src="' + escapeHtml(teacher.image_url) + '" alt="' + escapeHtml(teacher.name) + '">' +
            '<div class="flex-grow-1">' +
            '<h6>' + escapeHtml(teacher.name) + '</h6>' +
            '<small>' + escapeHtml(teacher.employee_id) + '</small>' +
            '</div>' +
            '<input type="checkbox" ' + (selected ? 'checked' : '') + '>' +
            '</div>' +
            '</div>';
        }).join('');

        teacherPickerList.querySelectorAll('.teacher-picker-card').forEach(function (card) {
          card.addEventListener('click', function () {
            const teacherId = String(card.dataset.teacherId);
            selectedTeacherIds.has(teacherId) ? selectedTeacherIds.delete(teacherId) : selectedTeacherIds.add(teacherId);
            card.classList.toggle('is-selected', selectedTeacherIds.has(teacherId));
            card.querySelector('input').checked = selectedTeacherIds.has(teacherId);
          });
        });
      }

      function syncSelectedTeachers() {
        const selectedTeachers = teachers.filter(function (teacher) {
          return selectedTeacherIds.has(String(teacher.id));
        });

        teacherHiddenInputs.innerHTML = selectedTeachers.map(function (teacher) {
          return '<input type="hidden" name="teacher_ids[]" value="' + teacher.id + '">';
        }).join('');

        selectedTeachersPreview.innerHTML = selectedTeachers.length
          ? selectedTeachers.map(function (teacher) {
              return '<div class="teacher-selected-chip">' +
                '<img src="' + escapeHtml(teacher.image_url) + '" alt="' + escapeHtml(teacher.name) + '">' +
                '<span>' + escapeHtml(teacher.name) + '</span>' +
                '</div>';
            }).join('')
          : '<small class="text-muted">No teachers selected</small>';

        openTeacherPickerBtn.textContent = selectedTeachers.length
          ? selectedTeachers.length + ' teacher(s) selected'
          : 'Choose Teachers';
      }

      function escapeHtml(value) {
        return String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }
    });
  </script>
@endpush
