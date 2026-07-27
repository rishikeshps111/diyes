<script>
document.addEventListener('DOMContentLoaded', function () {
  const selected = new Set();
  const selectAll = document.getElementById('selectAllLeaveTypes');
  const csrf = '{{ csrf_token() }}';
  const table = new DataTable('#leaveTypesTable', {
    processing: true,
    serverSide: true,
    searching: true,
    lengthChange: false,
    order: [[12, 'desc']],
    dom: 'rt<"table_bottom"ip>',
    ajax: {
      url: '{{ route('leave-types.data') }}',
      data: function (data) {
        data.leave_type = document.getElementById('leave_type_filter').value;
        data.applicable_for = document.getElementById('applicable_for_filter').value;
        data.status = document.getElementById('status_filter').value;
      }
    },
    columns: [
      {data:'select',orderable:false,searchable:false},
      {data:'DT_RowIndex',orderable:false,searchable:false},
      {data:'code',name:'code'},
      {data:'leave_name',name:'leave_name'},
      {data:'leave_type',name:'leave_type'},
      {data:'max_leaves_per_year',name:'max_leaves_per_year'},
      {data:'carry_forward_allowed',name:'carry_forward_allowed',orderable:false},
      {data:'applicable_for_text',orderable:false,searchable:false},
      {data:'gender_specific',name:'gender_specific'},
      {data:'max_leave_days_per_request',name:'max_leave_days_per_request'},
      {data:'status',name:'status',orderable:false},
      {data:'actions',orderable:false,searchable:false},
      {data:'created_at',name:'created_at',visible:false,searchable:false}
    ],
    drawCallback: function () {
      document.querySelectorAll('.leave-type-row-check').forEach(function (box) { box.checked = selected.has(box.value); });
      syncAll();
    }
  });

  document.getElementById('leaveTypeTableSearch').addEventListener('keyup', function(){ table.search(this.value).draw(); });
  document.getElementById('leaveTypePerPage').addEventListener('change', function(){ table.page.len(Number(this.value)).draw(); });
  document.getElementById('applyFilters').addEventListener('click', function(){ table.draw(); });
  document.getElementById('resetFilters').addEventListener('click', function(){
    ['leave_type_filter','applicable_for_filter','status_filter'].forEach(function(id){ document.getElementById(id).value=''; });
    document.getElementById('leaveTypeTableSearch').value='';
    table.search('').draw();
  });

  document.getElementById('leaveTypesTable').addEventListener('change', function(event){
    if(!event.target.classList.contains('leave-type-row-check')) return;
    event.target.checked ? selected.add(event.target.value) : selected.delete(event.target.value);
    syncAll();
  });
  selectAll.addEventListener('change', function(){
    document.querySelectorAll('.leave-type-row-check').forEach(function(box){
      box.checked=selectAll.checked;
      selectAll.checked ? selected.add(box.value) : selected.delete(box.value);
    });
  });

  document.getElementById('leaveTypesTable').addEventListener('click', function(event){
    const button=event.target.closest('.leave-type-delete-btn');
    if(!button) return;
    Swal.fire({title:'Delete Leave Type?',text:'This action cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Yes, delete it'}).then(function(result){
      if(!result.isConfirmed) return;
      fetch(button.dataset.deleteUrl,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}})
        .then(function(response){return response.json().then(function(data){if(!response.ok)throw new Error(data.message);return data;});})
        .then(function(data){table.draw(false);Swal.fire('Deleted',data.message,'success');})
        .catch(function(error){Swal.fire('Unable to Delete',error.message||'Please try again.','error');});
    });
  });

  document.querySelectorAll('[data-export-url]').forEach(function(button){
    button.addEventListener('click',function(){
      if(!selected.size){Swal.fire('No Rows Selected','Select at least one leave type to export.','warning');return;}
      const form=document.getElementById('leaveTypeExportForm');
      form.querySelectorAll('input[name="selected_ids[]"]').forEach(function(input){input.remove();});
      selected.forEach(function(id){const input=document.createElement('input');input.type='hidden';input.name='selected_ids[]';input.value=id;form.appendChild(input);});
      form.action=button.dataset.exportUrl;
      form.submit();
    });
  });

  function syncAll(){
    const boxes=Array.from(document.querySelectorAll('.leave-type-row-check'));
    selectAll.checked=boxes.length>0&&boxes.every(function(box){return box.checked;});
    selectAll.indeterminate=boxes.some(function(box){return box.checked;})&&!selectAll.checked;
  }
});
</script>
