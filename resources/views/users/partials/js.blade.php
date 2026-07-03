<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedUsers = new Set();
        const selectAll = document.getElementById('selectAllUsers');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const csrfToken = '{{ csrf_token() }}';
        const resetPasswordModalElement = document.getElementById('resetPasswordModal');
        const resetPasswordModal = new bootstrap.Modal(resetPasswordModalElement);
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const resetPasswordSubmit = document.getElementById('resetPasswordSubmit');
        let resetPasswordUrl = '';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#department_filter, #role_filter, #status_filter').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true
            });
        }

        const table = new DataTable('#usersTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[10, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('users.data') }}',
                data: function (data) {
                    data.department_id = document.getElementById('department_filter').value;
                    data.role_id = document.getElementById('role_filter').value;
                    data.last_login_at = document.getElementById('last_login_filter').value;
                    data.is_active = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'employee_code', name: 'employee_code' },
                { data: 'username', name: 'username' },
                { data: 'name', name: 'name' },
                { data: 'role_name', name: 'role.name', orderable: false, searchable: false },
                { data: 'department', name: 'department.department_name', orderable: false, searchable: false },
                { data: 'last_login_at', name: 'last_login_at' },
                { data: 'is_active', name: 'is_active', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.user-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedUsers.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('userTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('userPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            ['department_filter', 'role_filter', 'last_login_filter', 'status_filter'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#department_filter, #role_filter, #status_filter').val(null).trigger('change');
            }
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('usersTable').addEventListener('change', function (event) {
            if (event.target.classList.contains('user-status-toggle')) {
                const toggle = event.target;
                toggle.disabled = true;

                fetch(toggle.dataset.toggleUrl, {
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
                        toast(data.message || 'User status updated successfully.');
                    })
                    .catch(function () {
                        toggle.checked = !toggle.checked;
                        toggle.disabled = false;
                        Swal.fire('Error', 'Unable to update user status. Please try again.', 'error');
                    });

                return;
            }

            if (!event.target.classList.contains('user-row-check')) {
                return;
            }

            event.target.checked
                ? selectedUsers.add(event.target.value)
                : selectedUsers.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('usersTable').addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.user-delete-btn');
            const resetButton = event.target.closest('.user-reset-password-btn');

            if (deleteButton) {
                confirmAction('Delete User?', 'This action cannot be undone.', function () {
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
                            selectedUsers.delete(deleteButton.closest('tr')?.querySelector('.user-row-check')?.value);
                            table.draw(false);
                            Swal.fire('Deleted', data.message || 'User deleted successfully.', 'success');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to delete user. Please try again.', 'error');
                        });
                });
                return;
            }

            if (resetButton) {
                resetPasswordUrl = resetButton.dataset.resetUrl;
                document.getElementById('resetPasswordUserName').textContent = resetButton.dataset.userName || 'this user';
                resetPasswordForm.reset();
                resetPasswordModal.show();
            }
        });

        resetPasswordForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const payload = {
                password: document.getElementById('reset_password').value,
                password_confirmation: document.getElementById('reset_password_confirmation').value
            };
            setButtonLoading(resetPasswordSubmit, true);

            fetch(resetPasswordUrl, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(assertOk)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    resetPasswordModal.hide();
                    toast(data.message || 'Password reset successfully.');
                })
                .catch(function () {
                    Swal.fire('Error', 'Unable to reset password. Please check both passwords and try again.', 'error');
                })
                .finally(function () {
                    setButtonLoading(resetPasswordSubmit, false);
                });
        });

        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.querySelector(button.dataset.passwordToggle);
                const icon = button.querySelector('i');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.user-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedUsers.add(checkbox.value)
                    : selectedUsers.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedUsers.size) {
                    Swal.fire('No Rows Selected', 'Select at least one user to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                selectedUsers.forEach(function (id) {
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
                        clearSelectedUsers();
                        toast('Export downloaded successfully.');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to export selected users. Please try again.', 'error');
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

        function clearSelectedUsers() {
            selectedUsers.clear();
            document.querySelectorAll('.user-row-check').forEach(function (checkbox) {
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
            return exportUrl.includes('/pdf') ? 'users.pdf' : 'users.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.user-row-check'));
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
