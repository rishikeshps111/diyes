@extends('layouts.app')

@section('title', 'Project Schedule')

@push('styles')
  <style>
    .schedule-view-modal {
      border: 0;
      border-radius: 14px;
      overflow: hidden;
    }

    .schedule-view-header {
      background: linear-gradient(135deg, #409810, #2b6e07);
      border: 0;
      color: #fff;
      padding: 20px 24px;
    }

    .schedule-view-header .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
      opacity: .9;
    }

    .schedule-view-kicker {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      background: rgba(255, 255, 255, .18);
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 8px;
      padding: 4px 10px;
    }

    .schedule-view-title {
      font-size: 20px;
      font-weight: 700;
      line-height: 1.3;
      margin: 0;
    }

    .schedule-view-body {
      background: #f7f9fc;
      padding: 22px;
    }

    .schedule-info-card {
      background: #fff;
      border: 1px solid #e7ebf2;
      border-radius: 10px;
      height: 100%;
      padding: 14px 16px;
    }

    .schedule-info-label {
      color: #6c757d;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .03em;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    .schedule-info-value {
      color: #1f2937;
      font-size: 15px;
      font-weight: 600;
      word-break: break-word;
    }

    .schedule-text-panel {
      background: #fff;
      border: 1px solid #e7ebf2;
      border-radius: 10px;
      padding: 16px;
    }

    .schedule-text-panel p {
      color: #374151;
      line-height: 1.6;
      margin: 0;
      white-space: pre-wrap;
    }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Project Schedule</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->project_title }}</a></li>
        <li class="breadcrumb-item active">Schedule</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <div class="row g-3 align-items-center">
        <div class="col-lg-12">
          <h4 class="mb-1">{{ $project->project_title }}</h4>
          <p class="mb-1">{{ $project->project_code }} / {{ $project->category?->title ?? '-' }}</p>
          <span>Duration: {{ $scheduleDayLimit }} day(s)</span>
          <span class="ms-3">Date: {{ $project->start_date?->format('d M Y') ?? '-' }} to {{ $project->end_date?->format('d M Y') ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="main-table-container">
      <div class="row">
        <div class="col-lg-12">
          <div class="btn-flex">
            <a href="{{ route('projects.index') }}" class="btn btn-danger">Back</a>
            @can('edit.project')
              <button type="button" id="addProjectScheduleBtn" class="add-btn">Add Schedule</button>
            @endcan
          </div>
        </div>
      </div>

      <div class="table-over mt-3">
        <table id="projectSchedulesTable" class="align-middle mb-0 table table-custom w-100">
          <thead>
            <tr>
              <th>SL No</th>
              <th>Day</th>
              <th>Date</th>
              <th>Topic</th>
              <th>Remarks</th>
              <th>Action</th>
              <th class="d-none">Created At</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </section>

  @can('edit.project')
    <div class="modal fade" id="projectScheduleFormModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form id="projectScheduleForm" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="projectScheduleFormTitle">Add Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="day_number_display">Day Number</label>
                <input type="text" id="day_number_display" class="form-control shadow-none" value="Day {{ $nextDayNumber }}" disabled>
              </div>
              <div class="col-lg-8 o-f-inp mb-3">
                <label for="schedule_date">Date <span class="text-danger">*</span></label>
                <input type="date" name="schedule_date" id="schedule_date" class="form-control shadow-none"
                  @if ($minScheduleDate) min="{{ $minScheduleDate }}" @endif
                  @if ($maxScheduleDate) max="{{ $maxScheduleDate }}" @endif>
                <div class="invalid-feedback" data-error-for="schedule_date"></div>
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="topic">Topic <span class="text-danger">*</span></label>
                <input type="text" name="topic" id="topic" class="form-control shadow-none" maxlength="250">
                <div class="invalid-feedback" data-error-for="topic"></div>
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control shadow-none" rows="3" maxlength="1000"></textarea>
                <div class="invalid-feedback" data-error-for="description"></div>
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="remarks">Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control shadow-none" rows="2" maxlength="500"></textarea>
                <div class="invalid-feedback" data-error-for="remarks"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" id="projectScheduleSubmitBtn" class="btn btn-success" data-loading-text="Saving...">Save</button>
          </div>
        </form>
      </div>
    </div>
  @endcan

  <div class="modal fade" id="projectScheduleViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content schedule-view-modal">
        <div class="modal-header schedule-view-header">
          <div>
            <div class="schedule-view-kicker" id="projectScheduleViewDay">Schedule</div>
            <h5 class="modal-title schedule-view-title" id="projectScheduleViewTitle">Schedule Details</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body schedule-view-body">
          <div id="projectScheduleViewContent" class="row g-3"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @include('projects.schedules.partials.js')
@endpush
