@extends('layouts.app')

@section('title', 'Training Schedule Trainers')

@section('content')
  <div class="page-title">
    <h3>Training Schedule Trainers</h3>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Timetable Management</li>
      <li class="breadcrumb-item"><a href="{{ route('training-schedules.index') }}">Training Schedule</a></li>
      <li class="breadcrumb-item active">Trainers</li>
    </ol></nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <div class="row g-3 align-items-center">
        <div class="col-lg-12">
          <h4 class="mb-1">{{ $trainingSchedule->title }}</h4>
          <p class="mb-1">{{ $trainingSchedule->code }} / {{ $trainingSchedule->trainerCategory?->title ?? '-' }}</p>
          <span>Type: {{ $trainingSchedule->trainerType?->title ?? '-' }}</span>
          <span class="ms-3">Date: {{ $trainingSchedule->start_date?->format('d M Y') ?? '-' }} to {{ $trainingSchedule->end_date?->format('d M Y') ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="main-table-container">
      <div class="row"><div class="col-lg-12"><div class="btn-flex">
        <a href="{{ route('training-schedules.index') }}" class="btn btn-danger">Back</a>
        @can('create.training-schedule')
          <button type="button" id="addTrainingTrainerBtn" class="add-btn border-0">Add Trainer</button>
        @endcan
      </div></div></div>

      <div class="row mt-3 justify-content-end">
        <div class="col-lg-5"><div class="entry-select">
          <p>Showing</p>
          <select id="trainingTrainerPerPage" class="form-select shadow-none">
            <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
          </select>
          <p>Entries</p>
        </div></div>
        <div class="col-lg-4"><div class="table-search">
          <label for="trainingTrainerSearch" class="nowrap">Search</label>
          <input type="text" id="trainingTrainerSearch" class="form-control shadow-none" placeholder="Search...">
        </div></div>
      </div>

      <div class="table-over mt-3">
        <table id="trainingTrainersTable" class="align-middle mb-0 table table-custom w-100">
          <thead><tr>
            <th>SL No</th><th>Designation</th><th>Name</th><th>Subject</th><th>Actions</th><th class="d-none">Created At</th>
          </tr></thead>
        </table>
      </div>
    </div>
  </section>

  @canany(['create.training-schedule', 'edit.training-schedule'])
    <div class="modal fade" id="trainingTrainerFormModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form id="trainingTrainerForm" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="trainingTrainerFormTitle">Add Trainer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="designation_id">Designation <span class="text-danger">*</span></label>
                <select name="designation_id" id="designation_id" class="form-select shadow-none">
                  <option value="">--- Select ---</option>
                  @foreach ($designations as $designation)
                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback" data-error-for="designation_id"></div>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="teacher_id">Name <span class="text-danger">*</span></label>
                <select name="teacher_id" id="teacher_id" class="form-select shadow-none">
                  <option value="">--- Select ---</option>
                  @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" data-designation-id="{{ $teacher->designation_id }}">{{ $teacher->name }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback" data-error-for="teacher_id"></div>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="subject_id">Subject <span class="text-danger">*</span></label>
                <select name="subject_id" id="subject_id" class="form-select shadow-none">
                  <option value="">--- Select ---</option>
                  @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">
                      {{ $subject->subject_name }}{{ $subject->grade ? ' - '.$subject->grade->grade : '' }}
                    </option>
                  @endforeach
                </select>
                <div class="invalid-feedback" data-error-for="subject_id"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" id="trainingTrainerSubmitBtn" class="btn btn-success" data-loading-text="Saving...">Save</button>
          </div>
        </form>
      </div>
    </div>
  @endcanany
@endsection

@push('scripts')
  @include('training-schedules.trainers.partials.js')
@endpush
