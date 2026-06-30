@extends('layouts.app')

@section('title', $classroom->exists ? 'Edit Classroom' : 'Add Classroom')

@section('content')
  <div class="page-title">
    <h3>Classrooms</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('classrooms.index') }}">Classrooms</a></li>
        <li class="breadcrumb-item active">{{ $classroom->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="classroomForm"
          action="{{ $classroom->exists ? route('classrooms.update', $classroom) : route('classrooms.store') }}">
          @csrf
          @if ($classroom->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $classroom->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="room_name">Room Name <span class="text-danger">*</span></label>
                <input type="text" name="room_name" id="room_name"
                  class="form-control shadow-none @error('room_name') is-invalid @enderror"
                  value="{{ old('room_name', $classroom->room_name) }}">
                @error('room_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="building">Building <span class="text-danger">*</span></label>
                <input type="text" name="building" id="building"
                  class="form-control shadow-none @error('building') is-invalid @enderror"
                  value="{{ old('building', $classroom->building) }}">
                @error('building')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="floor">Floor <span class="text-danger">*</span></label>
                <input type="text" name="floor" id="floor"
                  class="form-control shadow-none @error('floor') is-invalid @enderror"
                  value="{{ old('floor', $classroom->floor) }}">
                @error('floor')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="room_type">Room Type <span class="text-danger">*</span></label>
                <select name="room_type" id="room_type" class="form-select shadow-none @error('room_type') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($roomTypes as $roomType)
                    <option value="{{ $roomType }}" @selected(old('room_type', $classroom->room_type) === $roomType)>
                      {{ $roomType }}
                    </option>
                  @endforeach
                </select>
                @error('room_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="seating_capacity">Seating Capacity <span class="text-danger">*</span></label>
                <input type="number" name="seating_capacity" id="seating_capacity"
                  class="form-control shadow-none @error('seating_capacity') is-invalid @enderror"
                  value="{{ old('seating_capacity', $classroom->seating_capacity) }}" min="1">
                @error('seating_capacity')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_id">Department <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id"
                  class="form-select shadow-none @error('department_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id', $classroom->department_id) == $department->id)>
                      {{ $department->department_name }}
                    </option>
                  @endforeach
                </select>
                @error('department_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="equipment">Equipment</label>
                @php
                  $selectedEquipment = old('equipment', $classroom->equipment ?? []);
                  $mergedEquipmentOptions = collect($equipmentOptions)->merge($selectedEquipment)->filter()->unique();
                @endphp
                <select name="equipment[]" id="equipment"
                  class="form-select shadow-none @error('equipment') is-invalid @enderror" multiple>
                  @foreach ($mergedEquipmentOptions as $equipment)
                    <option value="{{ $equipment }}" @selected(in_array($equipment, $selectedEquipment, true))>{{ $equipment }}</option>
                  @endforeach
                </select>
                @error('equipment')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $classroom->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $classroom->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="remarks">Remarks</label>
                <textarea name="remarks" id="remarks"
                  class="form-control shadow-none @error('remarks') is-invalid @enderror">{{ old('remarks', $classroom->remarks) }}</textarea>
                @error('remarks')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('classrooms.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="classroomSubmitBtn" class="submit-btn"
                data-loading-text="{{ $classroom->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $classroom->exists ? 'Update' : 'Submit' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#equipment').select2({
          tags: true,
          width: '100%',
          placeholder: 'Select or type equipment'
        });
      }

      const classroomForm = document.getElementById('classroomForm');
      const submitButton = document.getElementById('classroomSubmitBtn');

      if (!classroomForm || !submitButton) {
        return;
      }

      classroomForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
