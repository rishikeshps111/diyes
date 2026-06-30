<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedHolidays = new Set();
        const selectAll = document.getElementById('selectAllHolidays');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const csrfToken = '{{ csrf_token() }}';

        const table = new DataTable('#holidaysTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[10, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('holidays.data') }}',
                data: function (data) {
                    data.applicable_branch = document.getElementById('branch_filter').value;
                    data.academic_year_id = document.getElementById('academic_year_filter').value;
                    data.holiday_type = document.getElementById('holiday_type_filter').value;
                    data.month = document.getElementById('month_filter').value;
                    data.date_from = document.getElementById('date_from_filter').value;
                    data.date_to = document.getElementById('date_to_filter').value;
                    data.is_active = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'holiday_name', name: 'holiday_name' },
                { data: 'holiday_type', name: 'holiday_type' },
                { data: 'holiday_date', name: 'holiday_date' },
                { data: 'applicable_branch', name: 'applicable_branch' },
                { data: 'applicable_classes', name: 'applicable_classes' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.holiday-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedHolidays.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('holidayTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('holidayPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            document.getElementById('branch_filter').value = '';
            document.getElementById('academic_year_filter').value = '';
            document.getElementById('holiday_type_filter').value = '';
            document.getElementById('month_filter').value = '';
            document.getElementById('date_from_filter').value = '';
            document.getElementById('date_to_filter').value = '';
            document.getElementById('status_filter').value = '';
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('holidaysTable').addEventListener('change', function (event) {
            if (event.target.classList.contains('holiday-status-toggle')) {
                const toggle = event.target;
                toggle.disabled = true;

                fetch(toggle.dataset.toggleUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Status update failed');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        table.draw(false);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || 'Holiday status updated successfully.',
                            showConfirmButton: false,
                            timer: 1800
                        });
                    })
                    .catch(function () {
                        toggle.checked = !toggle.checked;
                        toggle.disabled = false;
                        Swal.fire('Error', 'Unable to update holiday status. Please try again.', 'error');
                    });

                return;
            }

            if (!event.target.classList.contains('holiday-row-check')) {
                return;
            }

            event.target.checked
                ? selectedHolidays.add(event.target.value)
                : selectedHolidays.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('holidaysTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.holiday-delete-btn');

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Holiday?',
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
                        selectedHolidays.delete(deleteButton.closest('tr')?.querySelector('.holiday-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Holiday deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete holiday. Please try again.', 'error');
                    });
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.holiday-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedHolidays.add(checkbox.value)
                    : selectedHolidays.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedHolidays.size) {
                    Swal.fire('No Rows Selected', 'Select at least one holiday to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                selectedHolidays.forEach(function (id) {
                    formData.append('selected_ids[]', id);
                });

                setButtonLoading(button, true);

                fetch(button.dataset.exportUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/octet-stream'
                    },
                    body: formData
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Export failed');
                        }

                        return response.blob().then(function (blob) {
                            return {
                                blob: blob,
                                filename: exportFilename(response, button.dataset.exportUrl)
                            };
                        });
                    })
                    .then(function (file) {
                        const downloadUrl = window.URL.createObjectURL(file.blob);
                        const link = document.createElement('a');

                        link.href = downloadUrl;
                        link.download = file.filename;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        window.URL.revokeObjectURL(downloadUrl);

                        clearSelectedHolidays();

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Export downloaded successfully.',
                            showConfirmButton: false,
                            timer: 1800
                        });
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to export selected holidays. Please try again.', 'error');
                    })
                    .finally(function () {
                        setButtonLoading(button, false);
                    });
            });
        });

        function setButtonLoading(button, isLoading) {
            if (!button) {
                return;
            }

            if (isLoading) {
                button.dataset.originalHtml = button.dataset.originalHtml || button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
                    (button.dataset.loadingText || 'Loading...');
                return;
            }

            button.disabled = false;

            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
        }

        function clearSelectedHolidays() {
            selectedHolidays.clear();
            document.querySelectorAll('.holiday-row-check').forEach(function (checkbox) {
                checkbox.checked = false;
            });
            selectAll.checked = false;
        }

        function exportFilename(response, exportUrl) {
            const disposition = response.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename="?([^"]+)"?/);

            if (match && match[1]) {
                return match[1];
            }

            return exportUrl.includes('/pdf') ? 'holidays.pdf' : 'holidays.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.holiday-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }
    });
</script>
