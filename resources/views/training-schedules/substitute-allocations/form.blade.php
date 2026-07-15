@extends('layouts.app')

@section('title', $allocation->exists ? 'Edit Substitute Allocation' : 'Add Substitute Allocation')

@section('content')
  <div class="page-title">
    <h3>{{ $allocation->exists ? 'Edit' : 'Add' }} Substitute Allocation</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Timetable Management</li>
        <li class="breadcrumb-item"><a
            href="{{ route('training-schedules.substitute-allocations.index', $trainingSchedule) }}">Substitute
            Allocation</a></li>
        <li class="breadcrumb-item active">{{ $allocation->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container">
      <form id="allocationForm">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="o-f-inp">
              <label>Start Date</label>
              <input class="form-control shadow-none" value="{{ $trainingSchedule->start_date?->format('d-m-Y') }}"
                readonly>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="o-f-inp">
              <label>End Date</label>
              <input class="form-control shadow-none" value="{{ $trainingSchedule->end_date?->format('d-m-Y') }}"
                readonly>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="o-f-inp">
              <label for="subject_id">Subject <span class="text-danger">*</span></label>
              <select id="subject_id" class="form-select shadow-none" required>
                <option value="">--- Select ---</option>
                @foreach ($subjects as $subject)
                  <option value="{{ $subject->id }}" @selected(($allocation->subject_id ?? $allocation->trainerAssignment?->subject_id) === $subject->id)>
                    {{ $subject->subject_name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="o-f-inp">
              <label for="training_schedule_trainer_id">Choose Teacher <span class="text-danger">*</span></label>
              <select id="training_schedule_trainer_id" class="form-select shadow-none" required>
                <option value="">--- Select ---</option>
                @foreach ($teachers as $teacher)
                  <option value="{{ $teacher->id }}"
                    data-periods-url="{{ route('training-schedules.substitute-allocations.periods', [$trainingSchedule, $teacher]) }}"
                    @selected(($allocation->teacher_id ?? $allocation->trainerAssignment?->teacher_id) === $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-12 mb-3">
            <div class="o-f-inp">
              <label>Working Days</label>
              <div id="workingDays" class="d-flex flex-wrap gap-3 pt-2">
                @forelse ($workingDays as $day)
                  <label><input type="checkbox" class="working-day-check" value="{{ $day }}" checked> {{ $day }}</label>
                @empty
                  <span class="text-muted">No working days within the training dates.</span>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        <div class="table-over">
          <table class="align-middle mb-0 table table-custom w-100">
            <thead>
              <tr>
                <th>SL No</th>
                <th>Date</th>
                <th>Grade</th>
                <th>Division</th>
                <th>Period</th>
                <th>Allocate Substitute</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="periodRows">
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Choose a subject and teacher.</td>
              </tr>
            </tbody>
          </table>
        </div>
        @unless ($allocation->exists)
          <button type="button" id="addAllocationRow" class="add-btn border-0 mt-3">Add Row</button>
        @endunless
        <div id="allocationErrors" class="text-danger mt-2"></div>
        <div class="d-flex justify-content-end gap-2 mt-3">
          <a href="{{ route('training-schedules.substitute-allocations.index', $trainingSchedule) }}"
            class="btn btn-danger">Cancel</a>
          <button type="submit" id="allocationSubmit" class="btn btn-success"
            data-loading-text="Saving...">{{ $allocation->exists ? 'Update' : 'Save' }}</button>
        </div>
      </form>
    </div>
  </section>
@endsection

@push('scripts')
  @include('training-schedules.substitute-allocations.partials.form-js')
@endpush
