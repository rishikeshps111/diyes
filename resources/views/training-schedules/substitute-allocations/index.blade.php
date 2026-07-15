@extends('layouts.app')

@section('title', 'Substitute Allocation')

@section('content')
  <div class="page-title">
    <h3>Substitute Allocation</h3>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Timetable Management</li>
      <li class="breadcrumb-item"><a href="{{ route('training-schedules.index') }}">Training Schedule</a></li>
      <li class="breadcrumb-item active">Substitute Allocation</li>
    </ol></nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container">
      <div class="row align-items-center mb-3">
        <div class="col-md-6">
          @can('create.training-schedule')
            <div class="btn-flex">
              <a href="{{ route('training-schedules.substitute-allocations.create', $trainingSchedule) }}" class="add-btn">Add New</a>
            </div>
          @endcan
        </div>
        <div class="col-md-6"><input id="allocationSearch" class="form-control shadow-none" placeholder="Search..."></div>
      </div>
      <div class="table-over"><table id="allocationsTable" class="align-middle mb-0 table table-custom w-100">
        <thead><tr><th>SL No</th><th>Date</th><th>Trainer</th><th>Grade</th><th>Section</th><th>Subject</th><th>Period</th><th>Substitute</th><th>Actions</th></tr></thead>
      </table></div>
    </div>
  </section>
@endsection

@push('scripts')
  @include('training-schedules.substitute-allocations.partials.index-js')
@endpush
