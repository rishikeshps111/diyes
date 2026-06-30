<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedDivisions = new Set();
        const selectAll = document.getElementById('selectAllDivisions');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const csrfToken = '{{ csrf_token() }}';

        const table = new DataTable('#divisionsTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[10, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('divisions.data') }}',
                data: function (data) {
                    data.grade_id = document.getElementById('grade_filter').value;
                    data.is_active = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'grade', name: 'grade', orderable: false },
                { data: 'division', name: 'division' },
                { data: 'class_teacher', name: 'class_teacher' },
                { data: 'room_number', name: 'room_number' },
                { data: 'capacity', name: 'capacity' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.division-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedDivisions.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('divisionTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('divisionPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            document.getElementById('grade_filter').value = '';
            document.getElementById('status_filter').value = '';
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('divisionsTable').addEventListener('change', function (event) {
            if (event.target.classList.contains('division-status-toggle')) {
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
                            title: data.message || 'Division status updated successfully.',
                            showConfirmButton: false,
                            timer: 1800
                        });
                    })
                    .catch(function () {
                        toggle.checked = !toggle.checked;
                        toggle.disabled = false;
                        Swal.fire('Error', 'Unable to update division status. Please try again.', 'error');
                    });

                return;
            }

            if (!event.target.classList.contains('division-row-check')) {
                return;
            }

            event.target.checked
                ? selectedDivisions.add(event.target.value)
                : selectedDivisions.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('divisionsTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.division-delete-btn');

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Division?',
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
                        selectedDivisions.delete(deleteButton.closest('tr')?.querySelector('.division-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Division deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete division. Please try again.', 'error');
                    });
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.division-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedDivisions.add(checkbox.value)
                    : selectedDivisions.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedDivisions.size) {
                    Swal.fire('No Rows Selected', 'Select at least one division to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                selectedDivisions.forEach(function (id) {
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

                        clearSelectedDivisions();

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
                        Swal.fire('Error', 'Unable to export selected divisions. Please try again.', 'error');
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

        function clearSelectedDivisions() {
            selectedDivisions.clear();
            document.querySelectorAll('.division-row-check').forEach(function (checkbox) {
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

            return exportUrl.includes('/pdf') ? 'divisions.pdf' : 'divisions.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.division-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }
    });
</script>
