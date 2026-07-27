@extends('layouts.app')

@section('title', 'My Leave Applications')

@section('content')
  <div class="page-title">
    <h3>My Leave Applications</h3>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Leave Management</li>
      <li class="breadcrumb-item active">My Applications</li>
    </ol></nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container">
      <div class="btn-flex">
        <a href="{{ route('teacher.leave.create') }}" class="add-btn">
          <i class="fa-solid fa-plus me-1"></i>Apply Leave
        </a>
      </div>

      <div class="mt-3 table-container">
        <div class="row justify-content-end">
          <div class="col-lg-5">
            <div class="entry-select">
              <p>Showing</p>
              <select id="leavePerPage" class="form-select shadow-none">
                <option value="10">10</option><option value="25">25</option>
                <option value="50">50</option><option value="100">100</option>
              </select>
              <p>Entries</p>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="table-search">
              <label for="leaveSearch">Search</label>
              <input type="text" id="leaveSearch" class="form-control shadow-none"
                placeholder="Application number, leave type, status or reason">
            </div>
          </div>
        </div>

        <div class="table-over">
          <table id="teacherLeaveTable" class="align-middle mb-0 table table-custom mt-3 w-100">
            <thead><tr>
              <th>SL No</th><th>Application No</th><th>Leave Type</th><th>From</th>
              <th>To</th><th>Days</th><th>Status</th><th>Reason</th>
              <th>Applied On</th><th>Action</th><th class="d-none">Created</th>
            </tr></thead>
          </table>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const table = new DataTable('#teacherLeaveTable', {
    processing: true,
    serverSide: true,
    searching: true,
    lengthChange: false,
    order: [[10, 'desc']],
    dom: 'rt<"table_bottom"ip>',
    ajax: '{{ route('teacher.leave.data') }}',
    columns: [
      {data:'DT_RowIndex', orderable:false, searchable:false},
      {data:'application_no', name:'application_no'},
      {data:'leave_type', orderable:false, searchable:false},
      {data:'from_date', name:'from_date'},
      {data:'to_date', name:'to_date'},
      {data:'days', name:'days'},
      {data:'status', name:'status'},
      {data:'reason', name:'reason'},
      {data:'applied_date', name:'applied_date'},
      {data:'actions', orderable:false, searchable:false},
      {data:'created_at', name:'created_at', visible:false, searchable:false}
    ]
  });

  document.getElementById('leaveSearch').addEventListener('keyup', function () {
    table.search(this.value).draw();
  });
  document.getElementById('leavePerPage').addEventListener('change', function () {
    table.page.len(Number(this.value)).draw();
  });

  document.getElementById('teacherLeaveTable').addEventListener('submit', function (event) {
    const form = event.target.closest('.teacher-leave-cancel-form');
    if (!form) return;
    event.preventDefault();

    Swal.fire({
      title: 'Cancel Leave Request?',
      text: 'This pending leave application will be cancelled.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, cancel it',
      cancelButtonText: 'No'
    }).then(function (result) {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Cancelling...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: function () { Swal.showLoading(); }
        });
        form.submit();
      }
    });
  });
});
</script>
@endpush
