@extends('layouts.app')

@section('title', $venue->exists ? 'Edit Venue' : 'Add Venue')

@section('content')
  <div class="page-title">
    <h3>Venues</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('venues.index') }}">Venues</a></li>
        <li class="breadcrumb-item active">{{ $venue->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="venueForm"
          action="{{ $venue->exists ? route('venues.update', $venue) : route('venues.store') }}">
          @csrf
          @if ($venue->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $venue->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="venue_name">Venue Name <span class="text-danger">*</span></label>
                <input type="text" name="venue_name" id="venue_name"
                  class="form-control shadow-none @error('venue_name') is-invalid @enderror"
                  value="{{ old('venue_name', $venue->venue_name) }}">
                @error('venue_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="venue_type">Venue Type <span class="text-danger">*</span></label>
                <select name="venue_type" id="venue_type" class="form-select shadow-none @error('venue_type') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($venueTypes as $venueType)
                    <option value="{{ $venueType }}" @selected(old('venue_type', $venue->venue_type) === $venueType)>
                      {{ $venueType }}
                    </option>
                  @endforeach
                </select>
                @error('venue_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="building">Building <span class="text-danger">*</span></label>
                <input type="text" name="building" id="building"
                  class="form-control shadow-none @error('building') is-invalid @enderror"
                  value="{{ old('building', $venue->building) }}">
                @error('building')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="capacity">Capacity <span class="text-danger">*</span></label>
                <input type="number" name="capacity" id="capacity"
                  class="form-control shadow-none @error('capacity') is-invalid @enderror"
                  value="{{ old('capacity', $venue->capacity) }}" min="1">
                @error('capacity')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="contact_person">Contact Person <span class="text-danger">*</span></label>
                <input type="text" name="contact_person" id="contact_person"
                  class="form-control shadow-none @error('contact_person') is-invalid @enderror"
                  value="{{ old('contact_person', $venue->contact_person) }}">
                @error('contact_person')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="facilities">Facilities</label>
                @php
                  $selectedFacilities = old('facilities', $venue->facilities ?? []);
                  $mergedFacilityOptions = collect($facilityOptions)->merge($selectedFacilities)->filter()->unique();
                @endphp
                <select name="facilities[]" id="facilities"
                  class="form-select shadow-none @error('facilities') is-invalid @enderror" multiple>
                  @foreach ($mergedFacilityOptions as $facility)
                    <option value="{{ $facility }}" @selected(in_array($facility, $selectedFacilities, true))>{{ $facility }}</option>
                  @endforeach
                </select>
                @error('facilities')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $venue->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $venue->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="remarks">Remarks</label>
                <textarea name="remarks" id="remarks"
                  class="form-control shadow-none @error('remarks') is-invalid @enderror">{{ old('remarks', $venue->remarks) }}</textarea>
                @error('remarks')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('venues.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="venueSubmitBtn" class="submit-btn"
                data-loading-text="{{ $venue->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $venue->exists ? 'Update' : 'Submit' }}
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
        jQuery('#facilities').select2({
          tags: true,
          width: '100%',
          placeholder: 'Select or type facilities'
        });
      }

      const venueForm = document.getElementById('venueForm');
      const submitButton = document.getElementById('venueSubmitBtn');

      if (!venueForm || !submitButton) {
        return;
      }

      venueForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
