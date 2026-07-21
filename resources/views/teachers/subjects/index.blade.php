@extends('layouts.app')

@section('title', 'Manage Teacher Subjects')

@section('content')
  <div class="page-title">
    <h3>Manage Subjects</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.show', $teacher) }}">{{ $teacher->name }}</a></li>
        <li class="breadcrumb-item active">Manage Subjects</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <div class="row g-3 align-items-center">
        <div class="col-lg-2"><img src="{{ $teacher->imageUrl() ?: asset('assets/img/profile-img.jpg') }}"
            alt="{{ $teacher->name }}" class="teacher-detail-image"></div>
        <div class="col-lg-10">
          <h4 class="mb-1">{{ $teacher->name }}</h4>
          <p class="mb-1">{{ $teacher->employee_id }}</p>
          <span>{{ $teacher->department?->department_name ?? '-' }} /
            {{ $teacher->designation?->designation_name ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="main-table-container">
      <div class="btn-flex">
        <a href="{{ route('teachers.index') }}" class="btn btn-danger">Back</a>
        @can('edit.teacher')<button type="button" id="addTeacherSubjectBtn" class="add-btn">Add Subject</button>@endcan
      </div>
      <div class="table-over mt-3">
        <table id="teacherSubjectsTable" class="align-middle mb-0 table table-custom w-100">
          <thead>
            <tr>
              <th>SL No</th>
              <th>Grade</th>
              <th>Subject</th>
              <th>Action</th>
              <th class="d-none">Created At</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </section>

  @can('edit.teacher')
    <div class="modal fade" id="teacherSubjectFormModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form id="teacherSubjectForm" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="teacherSubjectFormTitle">Add Subject</h5><button type="button" class="btn-close"
              data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="o-f-inp mb-3">
              <label for="grade_id">Grade <span class="text-danger">*</span></label>
              <select name="grade_id" id="grade_id" class="form-select shadow-none">
                <option value="">--- Select ---</option>
                @foreach ($grades as $grade)
                  <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback" data-error-for="grade_id"></div>
            </div>
            <div class="o-f-inp mb-3">
              <label for="subject_id">Subject <span class="text-danger">*</span></label>
              <select name="subject_id" id="subject_id" class="form-select shadow-none">
                <option value="">--- Select ---</option>
                @foreach ($subjects as $subject)
                  <option value="{{ $subject->id }}">{{ $subject->subject_name }}
                  </option>
                @endforeach
              </select>
              <div class="invalid-feedback" data-error-for="subject_id"></div>
            </div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-danger"
              data-bs-dismiss="modal">Cancel</button><button type="submit" id="teacherSubjectSubmitBtn"
              class="btn btn-success">Save</button></div>
        </form>
      </div>
    </div>
  @endcan
@endsection

@push('scripts')
  @include('teachers.subjects.partials.js')
@endpush
