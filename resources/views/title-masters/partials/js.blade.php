<script>
  document.addEventListener('DOMContentLoaded', function () {
    const selectedRecords = new Set();
    const selectAll = document.getElementById('selectAllTitleMasters');
    const applyButton = document.getElementById('applyFilters');
    const resetButton = document.getElementById('resetFilters');
    const csrfToken = @json(csrf_token());
    const singular = @json($master['singular']);
    const plural = @json($master['plural']);
    const fallbackFilename = @json($master['filename']);

    const table = new DataTable('#TitleMastersTable', {
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: false,
      order: [[6, 'desc']],
      dom: 'rt<"table_bottom"ip>',
      ajax: {
        url: @json(route($master['route'].'.data')),
        data: function (data) {
          data.is_active = document.getElementById('status_filter').value;
        }
      },
      columns: [
        { data: 'select', name: 'select', orderable: false, searchable: false },
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'code', name: 'code' },
        { data: 'title', name: 'title' },
        { data: 'is_active', name: 'is_active', orderable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false },
        { data: 'created_at', name: 'created_at', visible: false, searchable: false }
      ],
      drawCallback: function () {
        document.querySelectorAll('.title-master-row-check').forEach(function (checkbox) {
          checkbox.checked = selectedRecords.has(checkbox.value);
        });
        syncSelectAll();
      }
    });

    document.getElementById('TitleMasterTableSearch').addEventListener('keyup', function () {
      table.search(this.value).draw();
    });
    document.getElementById('TitleMasterPerPage').addEventListener('change', function () {
      table.page.len(Number(this.value)).draw();
    });
    applyButton.addEventListener('click', function () {
      setButtonLoading(applyButton, true);
      table.draw();
    });
    resetButton.addEventListener('click', function () {
      setButtonLoading(resetButton, true);
      document.getElementById('status_filter').value = '';
      document.getElementById('TitleMasterTableSearch').value = '';
      table.search('').draw();
    });
    table.on('draw', function () {
      setButtonLoading(applyButton, false);
      setButtonLoading(resetButton, false);
    });

    document.getElementById('TitleMastersTable').addEventListener('change', function (event) {
      if (event.target.classList.contains('title-master-status-toggle')) {
        const toggle = event.target;
        toggle.disabled = true;
        fetch(toggle.dataset.toggleUrl, {
          method: 'PATCH',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Status update failed');
            return response.json();
          })
          .then(function (data) {
            table.draw(false);
            Swal.fire({
              toast: true, position: 'top-end', icon: 'success',
              title: data.message || singular + ' status updated successfully.',
              showConfirmButton: false, timer: 1800
            });
          })
          .catch(function () {
            toggle.checked = !toggle.checked;
            toggle.disabled = false;
            Swal.fire('Error', 'Unable to update ' + singular + ' status. Please try again.', 'error');
          });
        return;
      }

      if (event.target.classList.contains('title-master-row-check')) {
        event.target.checked ? selectedRecords.add(event.target.value) : selectedRecords.delete(event.target.value);
        syncSelectAll();
      }
    });

    document.getElementById('TitleMastersTable').addEventListener('click', function (event) {
      const deleteButton = event.target.closest('.title-master-delete-btn');
      if (!deleteButton) return;

      Swal.fire({
        title: 'Delete ' + singular + '?', text: 'This action cannot be undone.', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
      }).then(function (result) {
        if (!result.isConfirmed) return;
        fetch(deleteButton.dataset.deleteUrl, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Delete failed');
            return response.json();
          })
          .then(function (data) {
            const checkbox = deleteButton.closest('tr')?.querySelector('.title-master-row-check');
            if (checkbox) selectedRecords.delete(checkbox.value);
            table.draw(false);
            Swal.fire('Deleted', data.message || singular + ' deleted successfully.', 'success');
          })
          .catch(function () {
            Swal.fire('Error', 'Unable to delete ' + singular + '. Please try again.', 'error');
          });
      });
    });

    selectAll.addEventListener('change', function () {
      document.querySelectorAll('.title-master-row-check').forEach(function (checkbox) {
        checkbox.checked = selectAll.checked;
        selectAll.checked ? selectedRecords.add(checkbox.value) : selectedRecords.delete(checkbox.value);
      });
    });

    document.querySelectorAll('[data-export-url]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!selectedRecords.size) {
          Swal.fire('No Rows Selected', 'Select at least one ' + singular + ' to export.', 'warning');
          return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        selectedRecords.forEach(function (id) { formData.append('selected_ids[]', id); });
        setButtonLoading(button, true);

        fetch(button.dataset.exportUrl, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/octet-stream' },
          body: formData
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Export failed');
            return response.blob().then(function (blob) {
              return { blob: blob, filename: exportFilename(response, button.dataset.exportUrl) };
            });
          })
          .then(function (file) {
            const url = window.URL.createObjectURL(file.blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = file.filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
            clearSelected();
            Swal.fire({
              toast: true, position: 'top-end', icon: 'success', title: 'Export downloaded successfully.',
              showConfirmButton: false, timer: 1800
            });
          })
          .catch(function () {
            Swal.fire('Error', 'Unable to export selected ' + plural + '. Please try again.', 'error');
          })
          .finally(function () { setButtonLoading(button, false); });
      });
    });

    function setButtonLoading(button, loading) {
      if (!button) return;
      if (loading) {
        button.dataset.originalHtml = button.dataset.originalHtml || button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          (button.dataset.loadingText || 'Loading...');
      } else {
        button.disabled = false;
        if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
      }
    }

    function clearSelected() {
      selectedRecords.clear();
      document.querySelectorAll('.title-master-row-check').forEach(function (checkbox) { checkbox.checked = false; });
      selectAll.checked = false;
    }

    function exportFilename(response, exportUrl) {
      const disposition = response.headers.get('Content-Disposition') || '';
      const match = disposition.match(/filename="?([^";]+)"?/);
      if (match && match[1]) return match[1];
      return fallbackFilename + (exportUrl.includes('/pdf') ? '.pdf' : '.xlsx');
    }

    function syncSelectAll() {
      const visibleChecks = Array.from(document.querySelectorAll('.title-master-row-check'));
      selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
        return checkbox.checked;
      });
    }
  });
</script>
