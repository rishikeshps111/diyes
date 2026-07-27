@extends('layouts.app')

@section('title', $leave->exists ? 'Edit Leave Application' : 'Apply Leave')

@push('styles')
<style>
  .half-day-option{align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;display:flex;justify-content:space-between;padding:12px 14px}
  .half-day-option-title{color:#1e293b;font-size:14px;font-weight:600}
  .half-day-option small{color:#64748b;display:block;margin-top:2px}
  .half-day-switch{display:inline-block;height:24px;position:relative;width:46px}
  .half-day-switch input{height:0;opacity:0;width:0}
  .half-day-slider{background:#cbd5e1;border-radius:24px;cursor:pointer;inset:0;position:absolute;transition:.2s}
  .half-day-slider:before{background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.25);content:"";height:18px;left:3px;position:absolute;top:3px;transition:.2s;width:18px}
  .half-day-switch input:checked + .half-day-slider{background:#198754}
  .half-day-switch input:checked + .half-day-slider:before{transform:translateX(22px)}
  .half-day-switch input:focus + .half-day-slider{box-shadow:0 0 0 .2rem rgba(25,135,84,.18)}
</style>
@endpush

@section('content')
  <div class="page-title">
    <h3>{{ $leave->exists ? 'Edit Leave Application' : 'Apply Leave' }}</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Leave Management</li>
        <li class="breadcrumb-item"><a href="{{ route('teacher.leave.index') }}">My Applications</a></li>
        <li class="breadcrumb-item active">{{ $leave->exists ? 'Edit' : 'Apply Leave' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form action="{{ $leave->exists ? route('teacher.leave.update', $leave) : route('teacher.leave.store') }}" method="POST" id="teacherLeaveForm">
          @csrf
          @if($leave->exists) @method('PUT') @endif

          <div class="main-table-container mb-3">
            <h5 class="mb-3">Leave Application Details</h5>
            <div class="row">
              <div class="col-lg-6 o-f-inp mb-3">
                <label for="leave_type">Leave Type <span class="text-danger">*</span></label>
                <select name="leave_type_id" id="leave_type"
                  class="form-select shadow-none @error('leave_type_id') is-invalid @enderror" required>
                  <option value="">--- Select ---</option>
                  @foreach($leaveTypes as $leaveTypeOption)
                    <option value="{{ $leaveTypeOption->id }}" data-half-day="{{ $leaveTypeOption->allow_half_day ? 1 : 0 }}"
                      @selected(old('leave_type_id', $leave->leave_type_id) == $leaveTypeOption->id)>
                      {{ $leaveTypeOption->leave_name }}
                    </option>
                  @endforeach
                </select>
                @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-6 o-f-inp mb-3">
                <label for="remaining">Remaining Balance</label>
                <input type="text" id="remaining" class="form-control shadow-none" value="—" readonly>
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="from_date">From Date <span class="text-danger">*</span></label>
                <input type="date" name="from_date" id="from_date"
                  class="form-control shadow-none @error('from_date') is-invalid @enderror"
                  value="{{ old('from_date', $leave->from_date?->format('Y-m-d')) }}" required>
                @error('from_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="to_date">To Date <span class="text-danger">*</span></label>
                <input type="date" name="to_date" id="to_date"
                  class="form-control shadow-none @error('to_date') is-invalid @enderror"
                  value="{{ old('to_date', $leave->to_date?->format('Y-m-d')) }}" required>
                @error('to_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-4 o-f-inp mb-3">
                <label for="days">Total Days</label>
                <input type="text" id="days" class="form-control shadow-none" value="—" readonly>
              </div>

              <div class="col-lg-6 o-f-inp mb-3" id="halfDayWrap" hidden>
                <label class="d-block">Half Day</label>
                <input type="hidden" name="is_half_day" value="0">
                <div class="half-day-option mt-2">
                  <label for="is_half_day" class="mb-0">
                    <span class="half-day-option-title">Apply for Half Day</span>
                    <small>The selected date will count as 0.5 day.</small>
                  </label>
                  <label class="half-day-switch" for="is_half_day">
                    <input type="checkbox" name="is_half_day" value="1" id="is_half_day"
                      @checked(old('is_half_day', $leave->is_half_day))>
                    <span class="half-day-slider"></span>
                  </label>
                </div>
                @error('is_half_day')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>

              <div class="col-lg-12 o-f-inp mb-3">
                <label for="reason">Reason <span class="text-danger">*</span></label>
                <textarea name="reason" id="reason" rows="5"
                  class="form-control shadow-none @error('reason') is-invalid @enderror"
                  placeholder="Enter the reason for leave" required>{{ old('reason', $leave->reason) }}</textarea>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('teacher.leave.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="leaveSubmitBtn" class="submit-btn" data-loading-text="{{ $leave->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $leave->exists ? 'Update' : 'Submit' }}
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
  const form = document.getElementById('teacherLeaveForm');
  const submitButton = document.getElementById('leaveSubmitBtn');
  const leaveType = document.getElementById('leave_type');
  const fromDate = document.getElementById('from_date');
  const toDate = document.getElementById('to_date');
  const days = document.getElementById('days');
  const remaining = document.getElementById('remaining');
  const halfDayWrap = document.getElementById('halfDayWrap');
  const halfDay = document.getElementById('is_half_day');

  function updateDays() {
    if (halfDay.checked && fromDate.value) {
      toDate.value = fromDate.value;
      toDate.readOnly = true;
    } else {
      toDate.readOnly = false;
    }
    if (!fromDate.value || !toDate.value) {
      days.value = '—';
      return;
    }
    const difference = Math.floor((new Date(toDate.value) - new Date(fromDate.value)) / 86400000) + 1;
    days.value = difference > 0 ? (halfDay.checked ? '0.5' : difference) : '—';
    toDate.min = fromDate.value;
  }

  function updateLeaveType() {
    const option = leaveType.options[leaveType.selectedIndex];
    const allowsHalfDay = option && option.dataset.halfDay === '1';
    halfDayWrap.hidden = !allowsHalfDay;
    if (!allowsHalfDay) {
      halfDay.checked = false;
      toDate.readOnly = false;
    }
    updateDays();

    if (!leaveType.value) {
      remaining.value = '—';
      return;
    }
    fetch('{{ url('teacher/get-leave-balance') }}/' + leaveType.value, {
      headers: {'Accept': 'application/json'}
    })
      .then(response => response.json())
      .then(data => { remaining.value = data.remaining_days; })
      .catch(() => { remaining.value = '—'; });
  }

  leaveType.addEventListener('change', updateLeaveType);
  fromDate.addEventListener('change', updateDays);
  toDate.addEventListener('change', updateDays);
  halfDay.addEventListener('change', updateDays);

  form.addEventListener('submit', function () {
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
      submitButton.dataset.loadingText;
  });

  updateLeaveType();
  updateDays();
});
</script>
@endpush
