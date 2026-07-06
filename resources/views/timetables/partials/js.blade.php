<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedTimetables = new Set();
        const selectAll = document.getElementById('selectAllTimetables');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const csrfToken = '{{ csrf_token() }}';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#academic_year_filter, #grade_filter, #division_filter, #timetable_category_filter, #status_filter').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true
            });
        }

        const table = new DataTable('#timetablesTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[11, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('timetables.data') }}',
                data: function (data) {
                    data.academic_year_id = document.getElementById('academic_year_filter').value;
                    data.grade_id = document.getElementById('grade_filter').value;
                    data.division_id = document.getElementById('division_filter').value;
                    data.timetable_category_id = document.getElementById('timetable_category_filter').value;
                    data.status = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'timetable_name', name: 'timetable_name' },
                { data: 'academic_year', name: 'academic_year', orderable: false },
                { data: 'grade', name: 'grade', orderable: false },
                { data: 'division', name: 'division', orderable: false },
                { data: 'total_periods_per_day', name: 'total_periods_per_day' },
                { data: 'applicable_from', name: 'applicable_from' },
                { data: 'applicable_to', name: 'applicable_to' },
                { data: 'status', name: 'status', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.timetable-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedTimetables.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('timetableTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('timetablePerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            ['academic_year_filter', 'grade_filter', 'division_filter', 'timetable_category_filter', 'status_filter'].forEach(function (id) {
                document.getElementById(id).value = '';
            });

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#academic_year_filter, #grade_filter, #division_filter, #timetable_category_filter, #status_filter').val(null).trigger('change');
            }

            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('timetablesTable').addEventListener('change', function (event) {
            if (!event.target.classList.contains('timetable-row-check')) {
                return;
            }

            event.target.checked
                ? selectedTimetables.add(event.target.value)
                : selectedTimetables.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('timetablesTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.timetable-delete-btn');

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Regular Timetable?',
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
                        selectedTimetables.delete(deleteButton.closest('tr')?.querySelector('.timetable-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Regular timetable deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete regular timetable. Please try again.', 'error');
                    });
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.timetable-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedTimetables.add(checkbox.value)
                    : selectedTimetables.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedTimetables.size) {
                    Swal.fire('No Rows Selected', 'Select at least one regular timetable to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                selectedTimetables.forEach(function (id) {
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

                        clearSelectedTimetables();

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
                        Swal.fire('Error', 'Unable to export selected regular timetables. Please try again.', 'error');
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

        function clearSelectedTimetables() {
            selectedTimetables.clear();
            document.querySelectorAll('.timetable-row-check').forEach(function (checkbox) {
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

            return exportUrl.includes('/pdf') ? 'regular-timetables.pdf' : 'regular-timetables.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.timetable-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }
    });
</script>
