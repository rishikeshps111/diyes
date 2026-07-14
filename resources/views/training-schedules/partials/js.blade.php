<script>
  document.addEventListener('DOMContentLoaded', function () {
    const selectedSchedules = new Set();
    const selectAll = document.getElementById('selectAllTrainingSchedules');
    const applyButton = document.getElementById('applyFilters');
    const resetButton = document.getElementById('resetFilters');
    const csrfToken = @json(csrf_token());

    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#status_filter, #category_filter, #type_filter').select2({
        width: '100%', placeholder: '--- Select ---', allowClear: true
      });
    }

    const table = new DataTable('#trainingSchedulesTable', {
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: false,
      order: [[9, 'desc']],
      dom: 'rt<"table_bottom"ip>',
      ajax: {
        url: @json(route('training-schedules.data')),
        data: function (data) {
          data.status = document.getElementById('status_filter').value;
          data.trainer_category_id = document.getElementById('category_filter').value;
          data.trainer_type_id = document.getElementById('type_filter').value;
        }
      },
      columns: [
        { data: 'select', name: 'select', orderable: false, searchable: false },
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'title', name: 'title' },
        { data: 'category', name: 'trainerCategory.title', orderable: false },
        { data: 'type', name: 'trainerType.title', orderable: false },
        { data: 'start_date', name: 'start_date' },
        { data: 'end_date', name: 'end_date' },
        { data: 'status', name: 'status', orderable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false },
        { data: 'created_at', name: 'created_at', visible: false, searchable: false }
      ],
      drawCallback: function () {
        document.querySelectorAll('.training-schedule-row-check').forEach(function (checkbox) {
          checkbox.checked = selectedSchedules.has(checkbox.value);
        });
        syncSelectAll();
      }
    });

    document.getElementById('trainingScheduleTableSearch').addEventListener('keyup', function () {
      table.search(this.value).draw();
    });
    document.getElementById('trainingSchedulePerPage').addEventListener('change', function () {
      table.page.len(Number(this.value)).draw();
    });
    applyButton.addEventListener('click', function () {
      setButtonLoading(applyButton, true);
      table.draw();
    });
    resetButton.addEventListener('click', function () {
      setButtonLoading(resetButton, true);
      ['status_filter', 'category_filter', 'type_filter'].forEach(function (id) {
        document.getElementById(id).value = '';
      });
      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#status_filter, #category_filter, #type_filter').val(null).trigger('change');
      }
      document.getElementById('trainingScheduleTableSearch').value = '';
      table.search('').draw();
    });
    table.on('draw', function () {
      setButtonLoading(applyButton, false);
      setButtonLoading(resetButton, false);
    });

    document.getElementById('trainingSchedulesTable').addEventListener('change', function (event) {
      if (!event.target.classList.contains('training-schedule-row-check')) return;
      event.target.checked ? selectedSchedules.add(event.target.value) : selectedSchedules.delete(event.target.value);
      syncSelectAll();
    });

    document.getElementById('trainingSchedulesTable').addEventListener('click', function (event) {
      const button = event.target.closest('.training-schedule-delete-btn');
      if (!button) return;

      Swal.fire({
        title: 'Delete Training Schedule?', text: 'This action cannot be undone.', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
      }).then(function (result) {
        if (!result.isConfirmed) return;
        fetch(button.dataset.deleteUrl, {
          method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Delete failed');
            return response.json();
          })
          .then(function (data) {
            const checkbox = button.closest('tr')?.querySelector('.training-schedule-row-check');
            if (checkbox) selectedSchedules.delete(checkbox.value);
            table.draw(false);
            Swal.fire('Deleted', data.message || 'Training schedule deleted successfully.', 'success');
          })
          .catch(function () {
            Swal.fire('Error', 'Unable to delete training schedule. Please try again.', 'error');
          });
      });
    });

    selectAll.addEventListener('change', function () {
      document.querySelectorAll('.training-schedule-row-check').forEach(function (checkbox) {
        checkbox.checked = selectAll.checked;
        selectAll.checked ? selectedSchedules.add(checkbox.value) : selectedSchedules.delete(checkbox.value);
      });
    });

    document.querySelectorAll('[data-export-url]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!selectedSchedules.size) {
          Swal.fire('No Rows Selected', 'Select at least one training schedule to export.', 'warning');
          return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        selectedSchedules.forEach(function (id) { formData.append('selected_ids[]', id); });
        setButtonLoading(button, true);

        fetch(button.dataset.exportUrl, {
          method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/octet-stream' }, body: formData
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
            selectedSchedules.clear();
            selectAll.checked = false;
            table.draw(false);
          })
          .catch(function () {
            Swal.fire('Error', 'Unable to export selected training schedules. Please try again.', 'error');
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

    function exportFilename(response, exportUrl) {
      const disposition = response.headers.get('Content-Disposition') || '';
      const match = disposition.match(/filename="?([^";]+)"?/);
      if (match && match[1]) return match[1];
      return exportUrl.includes('/pdf') ? 'training-schedules.pdf' : 'training-schedules.xlsx';
    }

    function syncSelectAll() {
      const checks = Array.from(document.querySelectorAll('.training-schedule-row-check'));
      selectAll.checked = checks.length > 0 && checks.every(function (checkbox) { return checkbox.checked; });
    }
  });
</script>
