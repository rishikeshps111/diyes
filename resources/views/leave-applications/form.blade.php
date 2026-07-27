@extends('layouts.app')
@section('title',$leave->exists?'Edit Leave Application':'Add Leave Application')
@php
$selectedUserType=old('user_type',$leave->applicant_type==='user'?'role:'.$leave->role_id:'teacher');
$selectedApplicant=old('applicant_id',$leave->applicant_type==='user'?$leave->user_id:$leave->teacher_id);
$selectedLeaveType=old('leave_type_id',$leave->leave_type_id);
$teacherOptions=$teachers->map(fn($teacher)=>[
  'id'=>$teacher->id,
  'name'=>$teacher->name,
  'gender'=>strtolower((string)$teacher->gender),
])->values();
$userOptions=$users->map(fn($user)=>[
  'id'=>$user->id,
  'name'=>$user->name,
  'role_id'=>$user->role_id,
])->values();
$leaveTypeOptions=$leaveTypes->map(fn($type)=>[
  'id'=>$type->id,
  'name'=>$type->leave_name,
  'applicable'=>$type->applicable_for,
  'role_id'=>$type->role_id,
  'gender'=>$type->gender_specific,
  'half'=>$type->allow_half_day,
  'approval'=>$type->requires_approval,
  'notice'=>$type->advance_notice_days,
  'max'=>$type->max_leave_days_per_request,
])->values();
@endphp
@section('content')
<div class="page-title"><h3>{{ $leave->exists?'Edit':'Add' }} Leave Application</h3><nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item">Leave Management</li><li class="breadcrumb-item"><a href="{{ route('leave-applications.index') }}">Manage Leave</a></li><li class="breadcrumb-item active">{{ $leave->exists?'Edit':'Add' }}</li></ol></nav></div>
<section class="section dashboard">
<form id="leaveApplicationForm" method="POST" action="{{ $leave->exists?route('leave-applications.update',$leave):route('leave-applications.store') }}">@csrf @if($leave->exists)@method('PUT')@endif
<div class="main-table-container"><div class="row">
<div class="col-lg-4 o-f-inp mb-3"><label>Application No</label><input class="form-control shadow-none" value="{{ $leave->application_no }}" readonly></div>
<div class="col-lg-4 o-f-inp mb-3"><label>Applied Date</label><input class="form-control shadow-none" value="{{ $leave->applied_date?->format('Y-m-d') }}" readonly></div>
<div class="col-lg-4 o-f-inp mb-3"><label for="user_type">User Type <span class="text-danger">*</span></label><select name="user_type" id="user_type" class="form-select shadow-none" required><option value="">--- Select ---</option><option value="teacher" @selected($selectedUserType==='teacher')>Teacher</option>@foreach($roles as $role)<option value="role:{{ $role->id }}" @selected($selectedUserType==='role:'.$role->id)>{{ $role->name }}</option>@endforeach</select>@error('user_type')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-lg-4 o-f-inp mb-3"><label for="applicant_id">Applicant <span class="text-danger">*</span></label><select name="applicant_id" id="applicant_id" class="form-select shadow-none" required><option value="">--- Select ---</option></select>@error('applicant_id')<div class="text-danger small">{{ $message }}</div>@enderror @error('teacher_id')<div class="text-danger small">{{ $message }}</div>@enderror @error('user_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-lg-4 o-f-inp mb-3"><label for="leave_type_id">Leave Type <span class="text-danger">*</span></label><select name="leave_type_id" id="leave_type_id" class="form-select shadow-none" required><option value="">--- Select ---</option></select>@error('leave_type_id')<div class="text-danger small">{{ $message }}</div>@enderror<small id="leaveTypeHelp" class="text-muted"></small></div>
<div class="col-lg-4 o-f-inp mb-3"><label for="from_date">From Date <span class="text-danger">*</span></label><input type="date" name="from_date" id="from_date" class="form-control shadow-none" value="{{ old('from_date',$leave->from_date?->format('Y-m-d')) }}" required>@error('from_date')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-lg-4 o-f-inp mb-3"><label for="to_date">To Date <span class="text-danger">*</span></label><input type="date" name="to_date" id="to_date" class="form-control shadow-none" value="{{ old('to_date',$leave->to_date?->format('Y-m-d')) }}" required>@error('to_date')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-lg-4 o-f-inp mb-3"><label>Total Days</label><input id="days" class="form-control shadow-none" value="{{ old('days',$leave->days) }}" readonly></div>
<div class="col-lg-4 o-f-inp mb-3" id="halfDayGroup"><label for="is_half_day">Half Day <span class="text-danger">*</span></label><select name="is_half_day" id="is_half_day" class="form-select shadow-none" required><option value="0" @selected(!old('is_half_day',$leave->is_half_day))>No</option><option value="1" @selected(old('is_half_day',$leave->is_half_day))>Yes</option></select>@error('is_half_day')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-lg-12 o-f-inp mb-3"><label for="reason">Reason <span class="text-danger">*</span></label><textarea name="reason" id="reason" rows="4" class="form-control shadow-none" required>{{ old('reason',$leave->reason) }}</textarea>@error('reason')<div class="text-danger small">{{ $message }}</div>@enderror</div>
</div></div>
<div class="d-flex justify-content-center mt-3"><div class="btn-flex"><a href="{{ route('leave-applications.index') }}" class="btn btn-danger">Cancel</a><button id="leaveSubmitBtn" type="submit" class="submit-btn" data-loading-text="{{ $leave->exists?'Updating...':'Submitting...' }}">{{ $leave->exists?'Update':'Submit' }}</button></div></div>
</form>
</section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
const teachers=@json($teacherOptions);
const users=@json($userOptions);
const types=@json($leaveTypeOptions);
const userType=document.getElementById('user_type'),applicant=document.getElementById('applicant_id'),leaveType=document.getElementById('leave_type_id'),half=document.getElementById('is_half_day'),from=document.getElementById('from_date'),to=document.getElementById('to_date');
const selectedApplicant=@json((string)$selectedApplicant),selectedType=@json((string)$selectedLeaveType);
function options(items,selected){return '<option value="">--- Select ---</option>'+items.map(i=>'<option value="'+i.id+'"'+(String(i.id)===String(selected)?' selected':'')+'>'+i.name+'</option>').join('');}
function refreshSelect2(element){if(window.jQuery&&jQuery.fn.select2)jQuery(element).trigger('change.select2');}
function syncApplicants(initial){const teacher=userType.value==='teacher',role=Number(userType.value.replace('role:',''));applicant.innerHTML=options(teacher?teachers:users.filter(u=>u.role_id===role),initial?selectedApplicant:'');refreshSelect2(applicant);syncTypes(initial);}
function syncTypes(initial){const teacher=userType.value==='teacher',role=Number(userType.value.replace('role:','')),person=teachers.find(t=>String(t.id)===applicant.value);const allowed=types.filter(t=>{if(teacher){return t.applicable!=='role'&&(t.gender==='all'||t.gender===person?.gender);}return t.gender==='all'&&(t.applicable==='all'||(t.applicable==='role'&&Number(t.role_id)===role));});leaveType.innerHTML=options(allowed,initial?selectedType:'');refreshSelect2(leaveType);syncPolicy();updateDays();}
function syncPolicy(){const type=types.find(t=>String(t.id)===leaveType.value);half.querySelector('option[value="1"]').disabled=!type?.half;if(!type?.half)half.value='0';document.getElementById('leaveTypeHelp').textContent=type?('Max '+type.max+' day(s) per request · '+type.notice+' day(s) notice · '+(type.approval?'Approval required':'Automatic approval')):'';}
function updateDays(){if(!from.value||!to.value){document.getElementById('days').value='';return;}const diff=(new Date(to.value)-new Date(from.value))/86400000+1;document.getElementById('days').value=half.value==='1'?'0.5':(diff>0?diff:'');}
if(window.jQuery&&jQuery.fn.select2){
  jQuery(userType).select2({width:'100%',placeholder:'--- Select ---'}).on('change.leaveApplication',()=>syncApplicants(false));
  jQuery(applicant).select2({width:'100%',placeholder:'--- Select ---'}).on('change.leaveApplication',()=>syncTypes(false));
  jQuery(leaveType).select2({width:'100%',placeholder:'--- Select ---'}).on('change.leaveApplication',syncPolicy);
}else{
  userType.addEventListener('change',()=>syncApplicants(false));
  applicant.addEventListener('change',()=>syncTypes(false));
  leaveType.addEventListener('change',syncPolicy);
}
[from,to,half].forEach(el=>el.addEventListener('change',updateDays));syncApplicants(true);
document.getElementById('leaveApplicationForm').addEventListener('submit',function(){const b=document.getElementById('leaveSubmitBtn');b.disabled=true;b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>'+b.dataset.loadingText;});
});
</script>
@endpush
