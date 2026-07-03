<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';

    const table = new DataTable('#rolesTable', {
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: false,
      order: [[4, 'desc']],
      dom: 'rt<"table_bottom"ip>',
      ajax: {
        url: '{{ route('roles.data') }}'
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'users_count', name: 'users_count', searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false },
        { data: 'created_at', name: 'created_at', visible: false, searchable: false }
      ]
    });

    document.getElementById('roleTableSearch').addEventListener('keyup', function () {
      table.search(this.value).draw();
    });

    document.getElementById('rolePerPage').addEventListener('change', function () {
      table.page.len(Number(this.value)).draw();
    });

    document.getElementById('rolesTable').addEventListener('click', function (event) {
      const deleteButton = event.target.closest('.role-delete-btn');

      if (!deleteButton) {
        return;
      }

      Swal.fire({
        title: 'Delete Role?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
      }).then(function (result) {
        if (!result.isConfirmed) {
          return;
        }

        fetch(deleteButton.dataset.deleteUrl, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Delete failed');
            }

            return response.json();
          })
          .then(function (data) {
            table.draw(false);
            Swal.fire('Deleted', data.message || 'Role deleted successfully.', 'success');
          })
          .catch(function () {
            Swal.fire('Error', 'Unable to delete role. Please try again.', 'error');
          });
      });
    });
  });
</script>
