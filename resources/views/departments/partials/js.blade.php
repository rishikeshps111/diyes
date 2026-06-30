<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedDepartments = new Set();
        const exportForm = document.getElementById('departmentExportForm');
        const selectAll = document.getElementById('selectAllDepartments');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const csrfToken = '{{ csrf_token() }}';

        const table = new DataTable('#departmentsTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[3, 'asc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('departments.data') }}',
                data: function (data) {
                    data.department_name = document.getElementById('department_name_filter').value;
                    data.department_head = document.getElementById('department_head_filter').value;
                    data.is_active = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'department_code', name: 'department_code' },
                { data: 'department_name', name: 'department_name' },
                { data: 'department_head', name: 'department_head', orderable: false },
                { data: 'teacher_count', name: 'teacher_count' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.department-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedDepartments.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('departmentTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('departmentPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        document.getElementById('applyFilters').addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            document.getElementById('department_name_filter').value = '';
            document.getElementById('department_head_filter').value = '';
            document.getElementById('status_filter').value = '';
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('departmentsTable').addEventListener('change', function (event) {
            if (event.target.classList.contains('department-status-toggle')) {
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
                            title: data.message || 'Department status updated successfully.',
                            showConfirmButton: false,
                            timer: 1800
                        });
                    })
                    .catch(function () {
                        toggle.checked = !toggle.checked;
                        toggle.disabled = false;
                        Swal.fire('Error', 'Unable to update department status. Please try again.', 'error');
                    });

                return;
            }

            if (!event.target.classList.contains('department-row-check')) {
                return;
            }

            event.target.checked
                ? selectedDepartments.add(event.target.value)
                : selectedDepartments.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('departmentsTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.department-delete-btn');

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Department?',
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
                        selectedDepartments.delete(deleteButton.closest('tr')?.querySelector('.department-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Department deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete department. Please try again.', 'error');
                    });
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.department-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedDepartments.add(checkbox.value)
                    : selectedDepartments.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedDepartments.size) {
                    Swal.fire('No Rows Selected', 'Select at least one department to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                selectedDepartments.forEach(function (id) {
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

                        clearSelectedDepartments();

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
                        Swal.fire('Error', 'Unable to export selected departments. Please try again.', 'error');
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

        function clearSelectedDepartments() {
            selectedDepartments.clear();
            document.querySelectorAll('.department-row-check').forEach(function (checkbox) {
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

            return exportUrl.includes('/pdf') ? 'departments.pdf' : 'departments.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.department-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }
    });
</script>
