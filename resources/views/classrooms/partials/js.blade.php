<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedClassrooms = new Set();
        const selectAll = document.getElementById('selectAllClassrooms');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const csrfToken = '{{ csrf_token() }}';

        const table = new DataTable('#classroomsTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[9, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('classrooms.data') }}',
                data: function (data) {
                    data.building = document.getElementById('building_filter').value;
                    data.floor = document.getElementById('floor_filter').value;
                    data.room_type = document.getElementById('room_type_filter').value;
                    data.department_id = document.getElementById('department_filter').value;
                    data.seating_capacity = document.getElementById('capacity_filter').value;
                    data.is_active = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'room_name', name: 'room_name' },
                { data: 'building', name: 'building' },
                { data: 'room_type', name: 'room_type' },
                { data: 'seating_capacity', name: 'seating_capacity' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.classroom-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedClassrooms.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('classroomTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('classroomPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            document.getElementById('building_filter').value = '';
            document.getElementById('floor_filter').value = '';
            document.getElementById('room_type_filter').value = '';
            document.getElementById('department_filter').value = '';
            document.getElementById('capacity_filter').value = '';
            document.getElementById('status_filter').value = '';
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('classroomsTable').addEventListener('change', function (event) {
            if (event.target.classList.contains('classroom-status-toggle')) {
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
                            title: data.message || 'Classroom status updated successfully.',
                            showConfirmButton: false,
                            timer: 1800
                        });
                    })
                    .catch(function () {
                        toggle.checked = !toggle.checked;
                        toggle.disabled = false;
                        Swal.fire('Error', 'Unable to update classroom status. Please try again.', 'error');
                    });

                return;
            }

            if (!event.target.classList.contains('classroom-row-check')) {
                return;
            }

            event.target.checked
                ? selectedClassrooms.add(event.target.value)
                : selectedClassrooms.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('classroomsTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.classroom-delete-btn');

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Classroom?',
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
                        selectedClassrooms.delete(deleteButton.closest('tr')?.querySelector('.classroom-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Classroom deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete classroom. Please try again.', 'error');
                    });
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.classroom-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedClassrooms.add(checkbox.value)
                    : selectedClassrooms.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedClassrooms.size) {
                    Swal.fire('No Rows Selected', 'Select at least one classroom to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                selectedClassrooms.forEach(function (id) {
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

                        clearSelectedClassrooms();

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
                        Swal.fire('Error', 'Unable to export selected classrooms. Please try again.', 'error');
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

        function clearSelectedClassrooms() {
            selectedClassrooms.clear();
            document.querySelectorAll('.classroom-row-check').forEach(function (checkbox) {
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

            return exportUrl.includes('/pdf') ? 'classrooms.pdf' : 'classrooms.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.classroom-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }
    });
</script>
