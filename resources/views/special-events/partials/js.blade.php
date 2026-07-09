<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedSpecialEvents = new Set();
        const selectAll = document.getElementById('selectAllSpecialEvents');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const mailModalElement = document.getElementById('specialEventMailModal');
        const mailModal = mailModalElement ? new bootstrap.Modal(mailModalElement) : null;
        const mailForm = document.getElementById('specialEventMailForm');
        const mailSubmitButton = document.getElementById('specialEventMailSubmit');
        const mailSubject = document.getElementById('mail_subject');
        const mailDescription = document.getElementById('mail_description');
        const csrfToken = '{{ csrf_token() }}';
        let mailUrl = '';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#event_type_filter, #status_filter').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true
            });
        }

        const table = new DataTable('#specialEventsTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[10, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('special-events.data') }}',
                data: function (data) {
                    data.event_type_id = document.getElementById('event_type_filter').value;
                    data.status = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'event_code', name: 'event_code' },
                { data: 'event_title', name: 'event_title' },
                { data: 'event_start_date', name: 'event_start_date' },
                { data: 'event_end_date', name: 'event_end_date' },
                { data: 'coordinator', name: 'coordinator', orderable: false, searchable: false },
                { data: 'applicable_classes', name: 'applicable_classes', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.special-event-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedSpecialEvents.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('specialEventTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('specialEventPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            ['event_type_filter', 'status_filter'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#event_type_filter, #status_filter').val(null).trigger('change');
            }
            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('specialEventsTable').addEventListener('change', function (event) {
            if (!event.target.classList.contains('special-event-row-check')) {
                return;
            }

            event.target.checked
                ? selectedSpecialEvents.add(event.target.value)
                : selectedSpecialEvents.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('specialEventsTable').addEventListener('click', function (event) {
            const mailButton = event.target.closest('.special-event-mail-btn');
            const deleteButton = event.target.closest('.special-event-delete-btn');

            if (mailButton && mailModal) {
                event.preventDefault();
                mailUrl = mailButton.dataset.mailUrl;
                mailForm.reset();
                mailSubject.value = mailButton.dataset.eventTitle ? 'Special Event: ' + mailButton.dataset.eventTitle : 'Special Event Details';
                mailDescription.value = 'Please find attached the special event details.';
                mailModal.show();
                return;
            }

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Special Event?',
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
                    .then(assertOk)
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        selectedSpecialEvents.delete(deleteButton.closest('tr')?.querySelector('.special-event-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Special event deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete special event. Please try again.', 'error');
                    });
            });
        });

        if (mailForm) {
            mailForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!mailUrl) {
                    return;
                }

                const formData = new FormData(mailForm);
                setButtonLoading(mailSubmitButton, true);

                fetch(mailUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(jsonResponse)
                    .then(function (data) {
                        mailModal.hide();
                        toast(data.message || 'Special event mail queued successfully.');
                    })
                    .catch(function (error) {
                        Swal.fire('Error', validationMessage(error) || 'Unable to send special event mail. Please try again.', 'error');
                    })
                    .finally(function () {
                        setButtonLoading(mailSubmitButton, false);
                    });
            });
        }

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.special-event-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedSpecialEvents.add(checkbox.value)
                    : selectedSpecialEvents.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedSpecialEvents.size) {
                    Swal.fire('No Rows Selected', 'Select at least one special event to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                selectedSpecialEvents.forEach(function (id) {
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
                        selectedSpecialEvents.clear();
                        syncSelectAll();
                        toast('Export downloaded successfully.');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to export selected special events. Please try again.', 'error');
                    })
                    .finally(function () {
                        setButtonLoading(button, false);
                    });
            });
        });

        function assertOk(response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return Promise.resolve(response);
        }

        function jsonResponse(response) {
            return response.text().then(function (text) {
                let data = {};

                if (text) {
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        throw {
                            message: response.ok
                                ? 'Unexpected response from server.'
                                : htmlErrorMessage(text)
                        };
                    }
                }

                if (!response.ok) {
                    throw data;
                }

                return data;
            });
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

        function exportFilename(response, exportUrl) {
            const disposition = response.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename="?([^"]+)"?/);
            if (match && match[1]) {
                return match[1];
            }
            return exportUrl.includes('/pdf') ? 'special-events.pdf' : 'special-events.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.special-event-row-check'));
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

        function validationMessage(error) {
            if (!error || !error.errors) {
                return error && error.message ? error.message : '';
            }

            return Object.values(error.errors).flat().join('<br>');
        }

        function htmlErrorMessage(text) {
            const plain = String(text || '')
                .replace(/<script[\s\S]*?<\/script>/gi, '')
                .replace(/<style[\s\S]*?<\/style>/gi, '')
                .replace(/<[^>]+>/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            return plain ? plain.slice(0, 300) : 'Server returned an HTML error response.';
        }
    });
</script>
