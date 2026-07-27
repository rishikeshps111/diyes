@extends('layouts.app')

@section('title', $leaveType->exists ? 'Edit Leave Type' : 'Add Leave Type')

@php
  $applicableValue = old('applicable_for', $leaveType->applicable_for);
  $roleId = old('role_id', $leaveType->role_id);
  if ($applicableValue === 'role' && $roleId) $applicableValue = 'role:'.$roleId;
@endphp

@section('content')
  <div class="page-title">
    <h3>{{ $leaveType->exists ? 'Edit' : 'Add' }} Leave Type</h3>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item">Leave Management</li><li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li><li class="breadcrumb-item active">{{ $leaveType->exists ? 'Edit' : 'Add' }}</li></ol></nav>
  </div>

  <section class="section dashboard">
    <form id="leaveTypeForm" method="POST" action="{{ $leaveType->exists ? route('leave-types.update', $leaveType) : route('leave-types.store') }}">
      @csrf
      @if($leaveType->exists) @method('PUT') @endif
      <div class="main-table-container">
        <div class="row">
          @php
            $yesNoFields = [
              'carry_forward_allowed' => 'Carry Forward Allowed',
              'allow_half_day' => 'Allow Half Day',
              'requires_approval' => 'Requires Approval',
              'encashment_allowed' => 'Encashment Allowed',
            ];
          @endphp
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="code">Code <span class="text-danger">*</span></label><input id="code" name="code" class="form-control shadow-none @error('code') is-invalid @enderror" value="{{ old('code',$leaveType->code) }}" readonly required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="leave_name">Name <span class="text-danger">*</span></label><input id="leave_name" name="leave_name" class="form-control shadow-none @error('leave_name') is-invalid @enderror" value="{{ old('leave_name',$leaveType->leave_name) }}" required>@error('leave_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="leave_type">Leave Type <span class="text-danger">*</span></label><select id="leave_type" name="leave_type" class="form-select shadow-none" required><option value="">--- Select ---</option>@foreach($leaveTypes as $value=>$label)<option value="{{ $value }}" @selected(old('leave_type',$leaveType->leave_type)===$value)>{{ $label }}</option>@endforeach</select>@error('leave_type')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="max_leaves_per_year">Max Leaves Per Year <span class="text-danger">*</span></label><input type="number" min="0" id="max_leaves_per_year" name="max_leaves_per_year" class="form-control shadow-none" value="{{ old('max_leaves_per_year',$leaveType->max_leaves_per_year) }}" required>@error('max_leaves_per_year')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          @foreach($yesNoFields as $field=>$label)
            <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="{{ $field }}">{{ $label }} <span class="text-danger">*</span></label><select id="{{ $field }}" name="{{ $field }}" class="form-select shadow-none" required><option value="">--- Select ---</option><option value="1" @selected((string)old($field,(int)$leaveType->{$field})==='1')>Yes</option><option value="0" @selected((string)old($field,(int)$leaveType->{$field})==='0')>No</option></select>@error($field)<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
            @if($field==='carry_forward_allowed')
              <div class="col-lg-4 mb-3" id="carryForwardLimitGroup"><div class="o-f-inp"><label for="max_carry_forward_limit">Max Carry Forward Limit <span class="text-danger">*</span></label><input type="number" min="0" id="max_carry_forward_limit" name="max_carry_forward_limit" class="form-control shadow-none" value="{{ old('max_carry_forward_limit',$leaveType->max_carry_forward_limit) }}">@error('max_carry_forward_limit')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
            @endif
          @endforeach
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="applicable_for">Applicable For <span class="text-danger">*</span></label><select id="applicable_for" name="applicable_for" class="form-select shadow-none" required><option value="">--- Select ---</option><option value="all" @selected($applicableValue==='all')>All</option><option value="teachers" @selected($applicableValue==='teachers')>Teachers</option>@foreach($roles as $role)<option value="role:{{ $role->id }}" @selected($applicableValue==='role:'.$role->id)>{{ $role->name }}</option>@endforeach</select>@error('applicable_for')<div class="text-danger small">{{ $message }}</div>@enderror @error('role_id')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="gender_specific">Gender Specific <span class="text-danger">*</span></label><select id="gender_specific" name="gender_specific" class="form-select shadow-none" required><option value="">--- Select ---</option>@foreach($genders as $value=>$label)<option value="{{ $value }}" @selected(old('gender_specific',$leaveType->gender_specific)===$value)>{{ $label }}</option>@endforeach</select>@error('gender_specific')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="max_leave_days_per_request">Maximum Leave Days Per Request <span class="text-danger">*</span></label><input type="number" min="1" id="max_leave_days_per_request" name="max_leave_days_per_request" class="form-control shadow-none" value="{{ old('max_leave_days_per_request',$leaveType->max_leave_days_per_request) }}" required>@error('max_leave_days_per_request')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="advance_notice_days">Advance Notice Required (Days) <span class="text-danger">*</span></label><input type="number" min="0" id="advance_notice_days" name="advance_notice_days" class="form-control shadow-none" value="{{ old('advance_notice_days',$leaveType->advance_notice_days) }}" required>@error('advance_notice_days')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-4 mb-3"><div class="o-f-inp"><label for="status">Status <span class="text-danger">*</span></label><select id="status" name="status" class="form-select shadow-none" required><option value="">--- Select ---</option><option value="1" @selected((string)old('status',(int)$leaveType->status)==='1')>Active</option><option value="0" @selected((string)old('status',(int)$leaveType->status)==='0')>Inactive</option></select>@error('status')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
          <div class="col-lg-12 mb-3"><div class="o-f-inp"><label for="description">Description <span class="text-danger">*</span></label><textarea id="description" name="description" rows="4" class="form-control shadow-none" required>{{ old('description',$leaveType->description) }}</textarea>@error('description')<div class="text-danger small">{{ $message }}</div>@enderror</div></div>
        </div>
      </div>
      <div class="col-lg-12 d-flex justify-content-center align-items-center mt-3">
        <div class="btn-flex">
          <a href="{{ route('leave-types.index') }}" class="btn btn-danger">Cancel</a>
          <button type="submit" id="leaveTypeSubmitBtn" class="submit-btn"
            data-loading-text="{{ $leaveType->exists ? 'Updating...' : 'Submitting...' }}">
            {{ $leaveType->exists ? 'Update' : 'Submit' }}
          </button>
        </div>
      </div>
    </form>
  </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
  const allowed=document.getElementById('carry_forward_allowed');
  const group=document.getElementById('carryForwardLimitGroup');
  const limit=document.getElementById('max_carry_forward_limit');
  const form=document.getElementById('leaveTypeForm');
  const submitButton=document.getElementById('leaveTypeSubmitBtn');
  function sync(){const yes=allowed.value==='1';group.classList.toggle('d-none',!yes);limit.required=yes;limit.disabled=!yes;if(!yes)limit.value='';}
  allowed.addEventListener('change',sync);sync();
  form.addEventListener('submit',function(){
    submitButton.disabled=true;
    submitButton.innerHTML='<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>'+submitButton.dataset.loadingText;
  });
});
</script>
@endpush
