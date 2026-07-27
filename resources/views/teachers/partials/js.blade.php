<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedTeachers = new Set();
        const selectAll = document.getElementById('selectAllTeachers');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const bulkDeleteButton = document.getElementById('bulkDeleteTeachers');
        const csrfToken = '{{ csrf_token() }}';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#department_filter, #designation_filter, #status_filter, #gender_filter').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true
            });

        }

        const table = new DataTable('#teachersTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[12, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('teachers.data') }}',
                data: function (data) {
                    data.department_id = document.getElementById('department_filter').value;
                    //data.designation_id = document.getElementById('designation_filter').value;
                    data.status = document.getElementById('status_filter').value;
                    data.gender = document.getElementById('gender_filter').value;
                    data.date_of_joining = document.getElementById('date_of_joining_filter').value;
                    data.qualification = document.getElementById('qualification_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'employee_id', name: 'employee_id' },
                { data: 'name', name: 'name' },
                { data: 'department', name: 'department.department_name', orderable: false, searchable: false },
                // { data: 'designation', name: 'designation.designation_name', orderable: false, searchable: false },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'date_of_joining', name: 'date_of_joining' },
                { data: 'status', name: 'status' },
                { data: 'verification_status', name: 'is_verified', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.teacher-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedTeachers.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('teacherTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('teacherPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            ['department_filter', 'status_filter', 'gender_filter', 'date_of_joining_filter', 'qualification_filter'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#department_filter, #status_filter, #gender_filter').val(null).trigger('change');
            }
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('teachersTable').addEventListener('change', function (event) {
            if (!event.target.classList.contains('teacher-row-check')) {
                return;
            }

            event.target.checked
                ? selectedTeachers.add(event.target.value)
                : selectedTeachers.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('teachersTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.teacher-delete-btn');
            const verifyButton = event.target.closest('.teacher-verify-btn');

            if (deleteButton) {
                confirmAction('Delete Teacher?', 'This action cannot be undone.', function () {
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
                            selectedTeachers.delete(deleteButton.closest('tr')?.querySelector('.teacher-row-check')?.value);
                            table.draw(false);
                            Swal.fire('Deleted', data.message || 'Teacher deleted successfully.', 'success');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to delete teacher. Please try again.', 'error');
                        });
                });
                return;
            }

            if (verifyButton) {
                confirmAction('Verify Teacher?', 'This teacher will be marked as verified.', function () {
                    fetch(verifyButton.dataset.verifyUrl, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                        .then(assertOk)
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            table.draw(false);
                            toast(data.message || 'Teacher verified successfully.');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to verify teacher. Please try again.', 'error');
                        });
                });
                return;
            }

        });

        if (bulkDeleteButton) {
            bulkDeleteButton.addEventListener('click', function () {
                if (!selectedTeachers.size) {
                    Swal.fire('No Rows Selected', 'Select at least one teacher to delete.', 'warning');
                    return;
                }

                confirmAction('Delete Selected Teachers?', 'This action cannot be undone.', function () {
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('_token', csrfToken);

                    selectedTeachers.forEach(function (id) {
                        formData.append('selected_ids[]', id);
                    });

                    setButtonLoading(bulkDeleteButton, true);

                    fetch(bulkDeleteButton.dataset.deleteUrl, {
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
                            clearSelectedTeachers();
                            table.draw(false);
                            Swal.fire('Deleted', data.message || 'Selected teachers deleted successfully.', 'success');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to delete selected teachers. Please try again.', 'error');
                        })
                        .finally(function () {
                            setButtonLoading(bulkDeleteButton, false);
                        });
                });
            });
        }

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.teacher-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedTeachers.add(checkbox.value)
                    : selectedTeachers.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedTeachers.size) {
                    Swal.fire('No Rows Selected', 'Select at least one teacher to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                selectedTeachers.forEach(function (id) {
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
                        clearSelectedTeachers();
                        toast('Export downloaded successfully.');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to export selected teachers. Please try again.', 'error');
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

        function clearSelectedTeachers() {
            selectedTeachers.clear();
            document.querySelectorAll('.teacher-row-check').forEach(function (checkbox) {
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
            return exportUrl.includes('/pdf') ? 'teachers.pdf' : 'teachers.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.teacher-row-check'));
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