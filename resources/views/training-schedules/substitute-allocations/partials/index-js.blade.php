<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrf = @json(csrf_token());
  const table = new DataTable('#allocationsTable', {
    processing: true, serverSide: true, searching: true, lengthChange: false,
    order: [[1, 'asc']], dom: 'rt<"table_bottom"ip>',
    ajax: @json(route('training-schedules.substitute-allocations.data', $trainingSchedule)),
    columns: [
      {data:'DT_RowIndex', orderable:false, searchable:false}, {data:'date', name:'allocation_date'},
      {data:'trainer', orderable:false}, {data:'grade', orderable:false}, {data:'section', orderable:false},
      {data:'subject', orderable:false}, {data:'period', orderable:false}, {data:'substitute', orderable:false},
      {data:'actions', orderable:false, searchable:false}
    ]
  });
  document.getElementById('allocationSearch').addEventListener('keyup', function () { table.search(this.value).draw(); });
  document.getElementById('allocationsTable').addEventListener('click', function (event) {
    const button = event.target.closest('.substitute-delete-btn');
    if (!button) return;
    Swal.fire({title:'Delete allocation?', icon:'warning', showCancelButton:true, confirmButtonText:'Delete'}).then(function (result) {
      if (!result.isConfirmed) return;
      fetch(button.dataset.deleteUrl, {method:'DELETE', headers:{'X-CSRF-TOKEN':csrf, Accept:'application/json'}})
        .then(response => response.json()).then(data => { Swal.fire('Deleted', data.message, 'success'); table.draw(false); });
    });
  });
});
</script>
