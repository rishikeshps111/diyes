<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedProjects = new Set();
        const selectAll = document.getElementById('selectAllProjects');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const statusModalElement = document.getElementById('projectStatusModal');
        const statusModal = statusModalElement ? new bootstrap.Modal(statusModalElement) : null;
        const statusForm = document.getElementById('projectStatusForm');
        const statusSelect = document.getElementById('project_status_modal');
        const statusSubmitButton = document.getElementById('projectStatusSubmit');
        const csrfToken = '{{ csrf_token() }}';
        let statusUrl = '';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#project_category_filter, #grade_filter, #status_filter').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true
            });
            jQuery('#project_status_modal').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: false,
                dropdownParent: jQuery('#projectStatusModal')
            });
        }

        const table = new DataTable('#projectsTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[14, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('projects.data') }}',
                data: function (data) {
                    data.project_category_id = document.getElementById('project_category_filter').value;
                    data.grade_id = document.getElementById('grade_filter').value;
                    data.status = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'project_code', name: 'project_code' },
                { data: 'project_title', name: 'project_title' },
                { data: 'category', name: 'category.title', orderable: false, searchable: false },
                { data: 'duration_days', name: 'duration_days' },
                { data: 'classes', name: 'classes', orderable: false, searchable: false },
                { data: 'subjects', name: 'subjects', orderable: false, searchable: false },
                { data: 'allocated_teachers', name: 'allocated_teachers', orderable: false, searchable: false },
                { data: 'venue', name: 'venue' },
                { data: 'created_at', name: 'created_at' },
                { data: 'timetable_replacement', name: 'timetable_replacement' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.project-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedProjects.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('projectTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('projectPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            ['project_category_filter', 'grade_filter', 'status_filter'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#project_category_filter, #grade_filter, #status_filter').val(null).trigger('change');
            }
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('projectsTable').addEventListener('change', function (event) {
            if (!event.target.classList.contains('project-row-check')) {
                return;
            }

            event.target.checked
                ? selectedProjects.add(event.target.value)
                : selectedProjects.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('projectsTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.project-delete-btn');
            const statusButton = event.target.closest('.project-status-btn');

            if (deleteButton) {
                confirmAction('Delete Project?', 'This action cannot be undone.', function () {
                    fetch(deleteButton.dataset.deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                        .then(assertOk)
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            selectedProjects.delete(deleteButton.closest('tr')?.querySelector('.project-row-check')?.value);
                            table.draw(false);
                            Swal.fire('Deleted', data.message || 'Project deleted successfully.', 'success');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to delete project. Please try again.', 'error');
                        });
                });
                return;
            }

            if (statusButton && statusModal) {
                statusUrl = statusButton.dataset.statusUrl;
                statusSelect.value = statusButton.dataset.currentStatus || 'draft';
                if (window.jQuery && jQuery.fn.select2) {
                    jQuery(statusSelect).val(statusSelect.value).trigger('change');
                }
                statusModal.show();
            }
        });

        if (statusForm) {
            statusForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!statusUrl) {
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('_method', 'PATCH');
                formData.append('status', statusSelect.value);

                setButtonLoading(statusSubmitButton, true);

                fetch(statusUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(assertOk)
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        statusModal.hide();
                        table.draw(false);
                        toast(data.message || 'Project status updated successfully.');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to update project status. Please try again.', 'error');
                    })
                    .finally(function () {
                        setButtonLoading(statusSubmitButton, false);
                    });
            });
        }

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.project-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedProjects.add(checkbox.value)
                    : selectedProjects.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedProjects.size) {
                    Swal.fire('No Rows Selected', 'Select at least one project to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                selectedProjects.forEach(function (id) {
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
                    .then(assertOk)
                    .then(function (response) {
                        return response.blob().then(function (blob) {
                            return { blob: blob, filename: exportFilename(response, button.dataset.exportUrl) };
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
                        clearSelectedProjects();
                        toast('Export downloaded successfully.');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to export selected projects. Please try again.', 'error');
                    })
                    .finally(function () {
                        setButtonLoading(button, false);
                    });
            });
        });

        function confirmAction(title, text, callback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes'
            }).then(function (result) {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }

        function assertOk(response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return Promise.resolve(response);
        }

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

        function clearSelectedProjects() {
            selectedProjects.clear();
            document.querySelectorAll('.project-row-check').forEach(function (checkbox) {
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
            return exportUrl.includes('/pdf') ? 'projects.pdf' : 'projects.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.project-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }

        function toast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 1800
            });
        }
    });
</script>
