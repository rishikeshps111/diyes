@extends('layouts.app')

@section('title', $trainingSchedule->exists ? 'Edit Training Schedule' : 'Add Training Schedule')

@section('content')
  <div class="page-title">
    <h3>Training Schedule</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a href="{{ route('training-schedules.index') }}">Training Schedule</a></li>
        <li class="breadcrumb-item active">{{ $trainingSchedule->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="trainingScheduleForm"
          action="{{ $trainingSchedule->exists ? route('training-schedules.update', $trainingSchedule) : route('training-schedules.store') }}">
          @csrf
          @if ($trainingSchedule->exists) @method('PUT') @endif
          <input type="hidden" name="status" id="status"
            value="{{ old('status', $trainingSchedule->status ?? 'draft') }}">

          <div class="main-table-container mb-3">
            <h5 class="title-w-sec">Training Information</h5>
            <hr>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $trainingSchedule->code }}"
                  disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title"
                  class="form-control shadow-none @error('title') is-invalid @enderror"
                  value="{{ old('title', $trainingSchedule->title) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="trainer_type_id">Type <span class="text-danger">*</span></label>
                <select name="trainer_type_id" id="trainer_type_id"
                  class="form-select shadow-none @error('trainer_type_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($trainerTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('trainer_type_id', $trainingSchedule->trainer_type_id) == $type->id)>{{ $type->title }}</option>
                  @endforeach
                </select>
                @error('trainer_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="trainer_category_id">Category <span class="text-danger">*</span></label>
                <select name="trainer_category_id" id="trainer_category_id"
                  class="form-select shadow-none @error('trainer_category_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($trainerCategories as $category)
                    <option value="{{ $category->id }}" @selected(old('trainer_category_id', $trainingSchedule->trainer_category_id) == $category->id)>{{ $category->title }}</option>
                  @endforeach
                </select>
                @error('trainer_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="conducted_by">Conducted By <span class="text-danger">*</span></label>
                <select name="conducted_by" id="conducted_by"
                  class="form-select shadow-none @error('conducted_by') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($conductedByOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('conducted_by', $trainingSchedule->conducted_by) === $value)>
                      {{ $label }}</option>
                  @endforeach
                </select>
                @error('conducted_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="resource_person_trainer">Resource Person / Trainer <span class="text-danger">*</span></label>
                <input type="text" name="resource_person_trainer" id="resource_person_trainer"
                  class="form-control shadow-none @error('resource_person_trainer') is-invalid @enderror"
                  value="{{ old('resource_person_trainer', $trainingSchedule->resource_person_trainer) }}">
                @error('resource_person_trainer')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date"
                  class="form-control shadow-none @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date', $trainingSchedule->start_date?->toDateString()) }}">
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date"
                  class="form-control shadow-none @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date', $trainingSchedule->end_date?->toDateString()) }}">
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="per_day_hours">Per Day Hours <span class="text-danger">*</span></label>
                <input type="number" name="per_day_hours" id="per_day_hours" min="0.01" max="24" step="0.25"
                  class="form-control shadow-none @error('per_day_hours') is-invalid @enderror"
                  value="{{ old('per_day_hours', $trainingSchedule->per_day_hours) }}">
                @error('per_day_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="mode">Mode <span class="text-danger">*</span></label>
                <select name="mode" id="mode" class="form-select shadow-none @error('mode') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($modes as $value => $label)
                    <option value="{{ $value }}" @selected(old('mode', $trainingSchedule->mode) === $value)>{{ $label }}
                    </option>
                  @endforeach
                </select>
                @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="venue">Venue <span class="text-danger">*</span></label>
                <input type="text" name="venue" id="venue"
                  class="form-control shadow-none @error('venue') is-invalid @enderror"
                  value="{{ old('venue', $trainingSchedule->venue) }}">
                @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="total_count">Total Count <span class="text-danger">*</span></label>
                <input type="number" name="total_count" id="total_count" min="1"
                  class="form-control shadow-none @error('total_count') is-invalid @enderror"
                  value="{{ old('total_count', $trainingSchedule->total_count) }}">
                @error('total_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="applicable">Applicable <span class="text-danger">*</span></label>
                <select name="applicable" id="applicable"
                  class="form-select shadow-none @error('applicable') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($applicableOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('applicable', $trainingSchedule->applicable) === $value)>
                      {{ $label }}</option>
                  @endforeach
                </select>
                @error('applicable')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-8 o-f-inp mb-3" id="teachingSubjectsField">
                <label for="subject_ids">Teaching Staff Subjects <span class="text-danger">*</span></label>
                <select name="subject_ids[]" id="subject_ids" multiple
                  class="form-select shadow-none @error('subject_ids') is-invalid @enderror">
                  @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected($selectedSubjectIds->contains($subject->id))>
                      {{ $subject->subject_name }}{{ $subject->grade ? ' - ' . $subject->grade->grade : '' }}
                    </option>
                  @endforeach
                </select>
                @error('subject_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('subject_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-12 o-f-inp"></div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="training_objectives">Training Objectives <span class="text-danger">*</span></label>
                <textarea name="training_objectives" id="training_objectives" rows="3"
                  class="form-control shadow-none @error('training_objectives') is-invalid @enderror">{{ old('training_objectives', $trainingSchedule->training_objectives) }}</textarea>
                @error('training_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="training_description">Training Description <span class="text-danger">*</span></label>
                <textarea name="training_description" id="training_description" rows="4"
                  class="form-control shadow-none @error('training_description') is-invalid @enderror">{{ old('training_description', $trainingSchedule->training_description) }}</textarea>
                @error('training_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="remarks">Remarks</label>
                <textarea name="remarks" id="remarks" rows="3"
                  class="form-control shadow-none @error('remarks') is-invalid @enderror">{{ old('remarks', $trainingSchedule->remarks) }}</textarea>
                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="main-table-container mb-3">
            <h5 class="title-w-sec">Schedule Details</h5>
            <hr>
            @error('sessions')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            <div class="table-over field-td-table">
              <table class="align-middle mb-0 table table-custom mt-3">
                <thead>
                  <tr>
                    <th class="nowrap">Session No</th>
                    <th>Date</th>
                    <th>Time From</th>
                    <th>Time To</th>
                    <th>Topic Module</th>
                    <th>Duration (Hours)</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="scheduleSessionsBody">
                  @foreach ($scheduleSessions as $index => $session)
                    <tr class="schedule-session-row">
                      <td class="session-number">{{ $index + 1 }}</td>
                      <td>
                        <input type="date" name="sessions[{{ $index }}][session_date]"
                          class="form-control shadow-none session-date @if($errors->has("sessions.$index.session_date")) is-invalid @endif"
                          value="{{ data_get($session, 'session_date') }}">
                        @if($errors->has("sessions.$index.session_date"))
                        <div class="invalid-feedback">{{ $errors->first("sessions.$index.session_date") }}</div>@endif
                      </td>
                      <td>
                        <input type="time" name="sessions[{{ $index }}][time_from]"
                          class="form-control shadow-none time-from @if($errors->has("sessions.$index.time_from")) is-invalid @endif"
                          value="{{ data_get($session, 'time_from') }}">
                        @if($errors->has("sessions.$index.time_from"))
                        <div class="invalid-feedback">{{ $errors->first("sessions.$index.time_from") }}</div>@endif
                      </td>
                      <td>
                        <input type="time" name="sessions[{{ $index }}][time_to]"
                          class="form-control shadow-none time-to @if($errors->has("sessions.$index.time_to")) is-invalid @endif"
                          value="{{ data_get($session, 'time_to') }}">
                        @if($errors->has("sessions.$index.time_to"))
                        <div class="invalid-feedback">{{ $errors->first("sessions.$index.time_to") }}</div>@endif
                      </td>
                      <td>
                        <input type="text" name="sessions[{{ $index }}][topic_module]"
                          class="form-control shadow-none topic-module @if($errors->has("sessions.$index.topic_module")) is-invalid @endif"
                          value="{{ data_get($session, 'topic_module') }}">
                        @if($errors->has("sessions.$index.topic_module"))
                        <div class="invalid-feedback">{{ $errors->first("sessions.$index.topic_module") }}</div>@endif
                      </td>
                      <td>
                        <input type="number" name="sessions[{{ $index }}][duration_hours]" step="0.01"
                          class="form-control shadow-none duration-hours @if($errors->has("sessions.$index.duration_hours")) is-invalid @endif"
                          value="{{ data_get($session, 'duration_hours') }}" readonly>
                        @if($errors->has("sessions.$index.duration_hours"))
                        <div class="invalid-feedback">{{ $errors->first("sessions.$index.duration_hours") }}</div>@endif
                      </td>
                      <td>
                        <div class="action-btns">
                          <button type="button" class="btn-delete border-0 remove-session-btn"><i
                              class="fa-solid fa-trash"></i></button>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <button type="button" id="addSessionBtn" class="btn btn-success border-0 mt-3">Add Another Session</button>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('training-schedules.index') }}" class="btn btn-danger">Cancel</a>
              @if ($trainingSchedule->exists && $trainingSchedule->status === 'published')
                <button type="submit" class="submit-btn training-schedule-submit-btn" data-status="published"
                  data-loading-text="Updating...">Update</button>
              @else
                <button type="submit" class="submit-btn training-schedule-submit-btn" data-status="draft"
                  data-loading-text="Saving...">Save as Draft</button>
                <button type="submit" class="submit-btn training-schedule-submit-btn" data-status="published"
                  data-loading-text="Publishing...">Publish</button>
              @endif
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  @include('training-schedules.partials.form-js')
@endpush