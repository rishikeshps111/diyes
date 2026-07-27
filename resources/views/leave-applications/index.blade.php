@extends('layouts.app')
@section('title','Leave Applications')
@push('styles')
<style>
.leave-decision-btn{font-size:11px;line-height:1.2;padding:4px 7px;white-space:nowrap}
.new-leave-badge{animation:leaveBlink 1s step-end infinite}@keyframes leaveBlink{50%{opacity:.25}}
</style>
@endpush
@section('content')
<div class="page-title"><h3>Leave Applications</h3><nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item">Leave Management</li><li class="breadcrumb-item active">Manage Leave</li></ol></nav></div>
<section class="section dashboard">
  <div class="row"><div class="col-12 mb-3"><div class="collapse" id="filterCollapse"><div class="main-table-container"><div class="row">
    <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>Applicant Type</label><select id="applicant_type_filter" class="form-select shadow-none"><option value="">--- Select ---</option><option value="teacher">Teacher</option><option value="user">Other Roles</option></select></div></div>
    <div class="col-lg-3 mb-3"><div class="o-f-inp"><label>Leave Type</label><select id="leave_type_filter" class="form-select shadow-none"><option value="">--- Select ---</option>@foreach($leaveTypes as $type)<option value="{{ $type->id }}">{{ $type->leave_name }}</option>@endforeach</select></div></div>
    <div class="col-lg-2 mb-3"><div class="o-f-inp"><label>Status</label><select id="status_filter" class="form-select shadow-none"><option value="">--- Select ---</option>@foreach($statuses as $status)<option>{{ $status }}</option>@endforeach</select></div></div>
    <div class="col-lg-2 mb-3"><div class="o-f-inp"><label>From Date</label><input type="date" id="from_date_filter" class="form-control shadow-none"></div></div>
    <div class="col-lg-2 mb-3"><div class="o-f-inp"><label>To Date</label><input type="date" id="to_date_filter" class="form-control shadow-none"></div></div>
    <div class="col-12"><div class="filter-btns-top"><button id="resetFilters" type="button" class="reset-btn border-0">Reset</button><button id="applyFilters" type="button" class="search-btn">Search</button></div></div>
  </div></div></div></div></div>
  <div class="main-table-container">
    <div class="btn-flex"><a class="add-btn bg-filter" data-bs-toggle="collapse" href="#filterCollapse">Filters</a>@can('create.leave-application')<a href="{{ route('leave-applications.create') }}" class="add-btn">Add New</a>@endcan</div>
    <div class="mt-3 table-container"><div class="row justify-content-end"><div class="col-lg-5"><div class="entry-select"><p>Showing</p><select id="leavePerPage" class="form-select shadow-none"><option>10</option><option>25</option><option>50</option><option>100</option></select><p>Entries</p></div></div><div class="col-lg-7"><div class="table-search"><label for="leaveSearch">Search</label><input id="leaveSearch" class="form-control shadow-none" placeholder="Application number, applicant, leave type or reason"></div></div></div>
      <div class="table-over"><table id="leaveApplicationsTable" class="align-middle mb-0 table table-custom mt-3 w-100"><thead><tr><th>SL No</th><th>Application No</th><th>Applied Date</th><th>User Type</th><th>Applicant</th><th>Applied By</th><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Actions</th><th class="d-none">Created</th></tr></thead></table></div>
    </div>
  </div>
</section>

<div class="modal fade" id="leaveDecisionModal" tabindex="-1" aria-labelledby="leaveDecisionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="leaveDecisionForm">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="leaveDecisionModalLabel">Process Leave Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" id="leaveDecisionDescription"></p>
        <div class="o-f-inp">
          <label for="leaveDecisionRemarks" id="leaveDecisionRemarksLabel">Remark</label>
          <textarea name="remarks" id="leaveDecisionRemarks" class="form-control shadow-none" rows="4"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn" id="leaveDecisionSubmit" data-loading-text="Processing...">Confirm</button>
      </div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
 const table=new DataTable('#leaveApplicationsTable',{processing:true,serverSide:true,searching:true,lengthChange:false,order:[[12,'desc']],dom:'rt<"table_bottom"ip>',ajax:{url:'{{ route('leave-applications.data') }}',data:function(d){d.applicant_type=document.getElementById('applicant_type_filter').value;d.leave_type_id=document.getElementById('leave_type_filter').value;d.status=document.getElementById('status_filter').value;d.from_date=document.getElementById('from_date_filter').value;d.to_date=document.getElementById('to_date_filter').value;}},columns:[
 {data:'DT_RowIndex',orderable:false,searchable:false},{data:'application_no',name:'application_no'},{data:'applied_date',name:'applied_date'},{data:'user_type',orderable:false,searchable:false},{data:'applicant',orderable:false,searchable:false},{data:'applied_by_name',orderable:false,searchable:false},{data:'leave_type',orderable:false,searchable:false},{data:'from_date',name:'from_date'},{data:'to_date',name:'to_date'},{data:'days',name:'days'},{data:'status',name:'status',orderable:false},{data:'actions',orderable:false,searchable:false},{data:'created_at',name:'created_at',visible:false,searchable:false}
 ]});
 document.getElementById('leaveSearch').addEventListener('keyup',function(){table.search(this.value).draw();});
 document.getElementById('leavePerPage').addEventListener('change',function(){table.page.len(Number(this.value)).draw();});
 document.getElementById('applyFilters').addEventListener('click',function(){table.draw();});
 document.getElementById('resetFilters').addEventListener('click',function(){['applicant_type_filter','leave_type_filter','status_filter','from_date_filter','to_date_filter'].forEach(function(id){document.getElementById(id).value='';});document.getElementById('leaveSearch').value='';table.search('').draw();});
 const decisionModal=new bootstrap.Modal(document.getElementById('leaveDecisionModal')),decisionForm=document.getElementById('leaveDecisionForm'),decisionTitle=document.getElementById('leaveDecisionModalLabel'),decisionDescription=document.getElementById('leaveDecisionDescription'),remarks=document.getElementById('leaveDecisionRemarks'),remarksLabel=document.getElementById('leaveDecisionRemarksLabel'),decisionSubmit=document.getElementById('leaveDecisionSubmit');
 document.getElementById('leaveApplicationsTable').addEventListener('click',function(event){const button=event.target.closest('.leave-decision-btn');if(!button)return;const approve=button.dataset.decision==='approve';decisionForm.action=button.dataset.actionUrl;decisionTitle.textContent=approve?'Approve Leave Application':'Reject Leave Application';decisionDescription.textContent=(approve?'Approve ':'Reject ')+button.dataset.application+'?';remarksLabel.innerHTML=(approve?'Approval Remark':'Rejection Remark <span class="text-danger">*</span>');remarks.placeholder=approve?'Enter approval remark (optional)':'Enter the reason for rejection';remarks.required=!approve;remarks.value='';decisionSubmit.className='btn '+(approve?'btn-success':'btn-danger');decisionSubmit.textContent=approve?'Approve':'Reject';decisionModal.show();});
 decisionForm.addEventListener('submit',function(){decisionSubmit.disabled=true;decisionSubmit.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>'+decisionSubmit.dataset.loadingText;});
});
</script>
@endpush
