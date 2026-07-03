@extends('layouts.app')

@section('title', $teacher->exists ? 'Edit Teacher' : 'Add Teacher')

@section('content')
  <div class="page-title">
    <h3>Teachers</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Teacher Management</li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
        <li class="breadcrumb-item active">{{ $teacher->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="teacherForm"
          action="{{ $teacher->exists ? route('teachers.update', $teacher) : route('teachers.store') }}"
          enctype="multipart/form-data">
          @csrf
          @if ($teacher->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <h5 class="mb-3">Personal Details</h5>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="employee_id">Employee Code</label>
                <input type="text" id="employee_id" class="form-control shadow-none" value="{{ $teacher->employee_id }}"
                  disabled>
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="name">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name"
                  class="form-control shadow-none @error('name') is-invalid @enderror"
                  value="{{ old('name', $teacher->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="gender">Gender <span class="text-danger">*</span></label>
                <select name="gender" id="gender" class="form-select shadow-none @error('gender') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($genders as $gender)
                    <option value="{{ $gender }}" @selected(old('gender', $teacher->gender) === $gender)>{{ $gender }}
                    </option>
                  @endforeach
                </select>
                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                <input type="date" name="date_of_birth" id="date_of_birth"
                  class="form-control shadow-none @error('date_of_birth') is-invalid @enderror"
                  value="{{ old('date_of_birth', $teacher->date_of_birth?->format('Y-m-d')) }}">
                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email"
                  class="form-control shadow-none @error('email') is-invalid @enderror"
                  value="{{ old('email', $teacher->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="teacher_image">Teacher Image</label>
                <input type="file" name="teacher_image" id="teacher_image"
                  class="form-control shadow-none @error('teacher_image') is-invalid @enderror"
                  accept="image/png,image/jpeg,image/jpg,image/webp">
                @error('teacher_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3"></div>
              <div class="col-lg-4 o-f-inp mb-3"></div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label>Image Preview</label>
                <div>
                  <img id="teacherImagePreview" src="{{ $teacher->imageUrl() ?: asset('assets/img/profile-img.jpg') }}"
                    alt="Teacher image preview" class="teacher-image-preview">
                </div>
              </div>
            </div>
          </div>

          <div class="main-table-container mb-3">
            <h5 class="mb-3">Professional Details</h5>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="qualification">Qualification <span class="text-danger">*</span></label>
                <input type="text" name="qualification" id="qualification"
                  class="form-control shadow-none @error('qualification') is-invalid @enderror"
                  value="{{ old('qualification', $teacher->qualification) }}">
                @error('qualification')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="experience">Experience <span class="text-danger">*</span></label>
                <input type="number" name="experience" id="experience"
                  class="form-control shadow-none @error('experience') is-invalid @enderror"
                  value="{{ old('experience', $teacher->experience) }}" min="0">
                @error('experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="date_of_joining">Date of Joining <span class="text-danger">*</span></label>
                <input type="date" name="date_of_joining" id="date_of_joining"
                  class="form-control shadow-none @error('date_of_joining') is-invalid @enderror"
                  value="{{ old('date_of_joining', $teacher->date_of_joining?->format('Y-m-d')) }}">
                @error('date_of_joining')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="department_id">Department <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id"
                  class="form-select shadow-none @error('department_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id', $teacher->department_id) == $department->id)>
                      {{ $department->department_name }}
                    </option>
                  @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="designation_id">Designation <span class="text-danger">*</span></label>
                <select name="designation_id" id="designation_id"
                  class="form-select shadow-none @error('designation_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($designations as $designation)
                    <option value="{{ $designation->id }}" @selected(old('designation_id', $teacher->designation_id) == $designation->id)>
                      {{ $designation->designation_name }}
                    </option>
                  @endforeach
                </select>
                @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="subject">Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" id="subject"
                  class="form-control shadow-none @error('subject') is-invalid @enderror"
                  value="{{ old('subject', $teacher->subject) }}">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="class_in_charge_id">Class In Charge</label>
                <select name="class_in_charge_id" id="class_in_charge_id"
                  class="form-select shadow-none @error('class_in_charge_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(old('class_in_charge_id', $teacher->class_in_charge_id) == $grade->id)>
                      {{ $grade->grade }}
                    </option>
                  @endforeach
                </select>
                @error('class_in_charge_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="employment_type">Employment Type <span class="text-danger">*</span></label>
                <select name="employment_type" id="employment_type"
                  class="form-select shadow-none @error('employment_type') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($employmentTypes as $employmentType)
                    <option value="{{ $employmentType }}" @selected(old('employment_type', $teacher->employment_type) === $employmentType)>
                      {{ ucfirst($employmentType) }}
                    </option>
                  @endforeach
                </select>
                @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="salary">Salary <span class="text-danger">*</span></label>
                <input type="number" name="salary" id="salary"
                  class="form-control shadow-none @error('salary') is-invalid @enderror"
                  value="{{ old('salary', $teacher->salary) }}" min="0" step="0.01">
                @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select shadow-none @error('status') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $teacher->status) === $status)>
                      {{ ucfirst($status) }}
                    </option>
                  @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="main-table-container mb-3">
            <h5 class="mb-3">Contact Details</h5>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="phone">Phone <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" name="phone_country_code" class="form-control shadow-none teacher-phone-code-input"
                    value="{{ old('phone_country_code', $teacher->phone_country_code ?: '+91') }}" readonly>
                  <input type="text" name="phone" id="phone"
                    class="form-control shadow-none @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $teacher->phone) }}">
                  @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="alternative_phone">Alternative Phone</label>
                <div class="input-group">
                  <input type="text" name="alternative_phone_country_code"
                    class="form-control shadow-none teacher-phone-code-input"
                    value="{{ old('alternative_phone_country_code', $teacher->alternative_phone_country_code ?: '+91') }}"
                    readonly>
                  <input type="text" name="alternative_phone" id="alternative_phone"
                    class="form-control shadow-none @error('alternative_phone') is-invalid @enderror"
                    value="{{ old('alternative_phone', $teacher->alternative_phone) }}">
                  @error('alternative_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>

          <div class="main-table-container mb-3">
            <h5 class="mb-3">Address Details</h5>
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="country_id">Country <span class="text-danger">*</span></label>
                <select name="country_id" id="country_id"
                  class="form-select shadow-none @error('country_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($countries as $country)
                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}"
                      @selected(old('country_id', $teacher->country_id) == $country->id)>{{ $country->name }}</option>
                  @endforeach
                </select>
                @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="state_id">State <span class="text-danger">*</span></label>
                <select name="state_id" id="state_id"
                  class="form-select shadow-none @error('state_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($states as $state)
                    <option value="{{ $state->id }}" data-country-id="{{ $state->country_id }}" @selected(old('state_id', $teacher->state_id) == $state->id)>{{ $state->name }}</option>
                  @endforeach
                </select>
                @error('state_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="district_id">District <span class="text-danger">*</span></label>
                <select name="district_id" id="district_id"
                  class="form-select shadow-none @error('district_id') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  @foreach ($districts as $district)
                    <option value="{{ $district->id }}" data-state-id="{{ $district->state_id }}"
                      @selected(old('district_id', $teacher->district_id) == $district->id)>{{ $district->name }}</option>
                  @endforeach
                </select>
                @error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="pincode">Pincode <span class="text-danger">*</span></label>
                <input type="text" name="pincode" id="pincode"
                  class="form-control shadow-none @error('pincode') is-invalid @enderror"
                  value="{{ old('pincode', $teacher->pincode) }}">
                @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-12 o-f-inp mb-3">
                <label for="address">Address <span class="text-danger">*</span></label>
                <textarea name="address" id="address"
                  class="form-control shadow-none @error('address') is-invalid @enderror">{{ old('address', $teacher->address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('teachers.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="teacherSubmitBtn" class="submit-btn"
                data-loading-text="{{ $teacher->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $teacher->exists ? 'Update' : 'Submit' }}
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
      const teacherForm = document.getElementById('teacherForm');
      const submitButton = document.getElementById('teacherSubmitBtn');
      const countrySelect = document.getElementById('country_id');
      const stateSelect = document.getElementById('state_id');
      const districtSelect = document.getElementById('district_id');
      const teacherImageInput = document.getElementById('teacher_image');
      const teacherImagePreview = document.getElementById('teacherImagePreview');
      const stateOptions = Array.from(stateSelect.options).map(function (option) {
        return {
          value: option.value,
          text: option.textContent,
          countryId: option.dataset.countryId || '',
          selected: option.selected
        };
      });
      const districtOptions = Array.from(districtSelect.options).map(function (option) {
        return {
          value: option.value,
          text: option.textContent,
          stateId: option.dataset.stateId || '',
          selected: option.selected
        };
      });
      const select2Fields = '#gender, #department_id, #designation_id, #class_in_charge_id, #employment_type, #status, #country_id, #state_id, #district_id';

      if (window.jQuery && jQuery.fn.select2) {
        jQuery(select2Fields).select2({
          width: '100%',
          placeholder: '--- Select ---',
          allowClear: true
        });

        jQuery(countrySelect).on('change', handleCountryChange);
        jQuery(stateSelect).on('change', filterDistricts);
      }

      function filterStates() {
        const countryId = countrySelect.value;
        const currentStateId = stateSelect.value;
        const matchingStates = stateOptions.filter(function (option) {
          return !option.value || option.countryId === countryId;
        });
        const hasCurrentState = matchingStates.some(function (option) {
          return option.value === currentStateId;
        });

        fillSelect(stateSelect, matchingStates, hasCurrentState ? currentStateId : '');
        filterDistricts();
        syncSelect2(stateSelect);
      }

      function filterDistricts() {
        const stateId = stateSelect.value;
        const currentDistrictId = districtSelect.value;
        const matchingDistricts = districtOptions.filter(function (option) {
          return !option.value || option.stateId === stateId;
        });
        const hasCurrentDistrict = matchingDistricts.some(function (option) {
          return option.value === currentDistrictId;
        });

        fillSelect(districtSelect, matchingDistricts, hasCurrentDistrict ? currentDistrictId : '');
        syncSelect2(districtSelect);
      }

      function fillSelect(select, options, selectedValue) {
        select.innerHTML = '';
        options.forEach(function (optionData) {
          const option = new Option(optionData.text, optionData.value, false, optionData.value === selectedValue);
          if (optionData.countryId) {
            option.dataset.countryId = optionData.countryId;
          }
          if (optionData.stateId) {
            option.dataset.stateId = optionData.stateId;
          }
          select.appendChild(option);
        });
      }

      function syncSelect2(select) {
        if (window.jQuery && jQuery.fn.select2) {
          jQuery(select).trigger('change.select2');
        }
      }

      function handleCountryChange() {
        const selected = countrySelect.options[countrySelect.selectedIndex];
        document.querySelector('[name="phone_country_code"]').value = selected?.dataset.phoneCode || '+91';
        document.querySelector('[name="alternative_phone_country_code"]').value = selected?.dataset.phoneCode || '+91';
        filterStates();
      }

      countrySelect.addEventListener('change', handleCountryChange);
      stateSelect.addEventListener('change', filterDistricts);
      filterStates();

      teacherImageInput.addEventListener('change', function () {
        const file = teacherImageInput.files && teacherImageInput.files[0];

        if (!file) {
          return;
        }

        teacherImagePreview.src = URL.createObjectURL(file);
        teacherImagePreview.onload = function () {
          URL.revokeObjectURL(teacherImagePreview.src);
        };
      });

      teacherForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush