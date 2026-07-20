<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';
        const schedulesDataUrl = '{{ route('projects.schedules.data', $project) }}';
        const schedulesStoreUrl = '{{ route('projects.schedules.store', $project) }}';
        const scheduleDayLimit = @json((int) $scheduleDayLimit);
        const formElement = document.getElementById('projectScheduleForm');
        const formModalElement = document.getElementById('projectScheduleFormModal');
        const formModal = formModalElement ? new bootstrap.Modal(formModalElement) : null;
        const viewModal = new bootstrap.Modal(document.getElementById('projectScheduleViewModal'));
        const addButton = document.getElementById('addProjectScheduleBtn');
        let nextDayNumber = @json((int) $nextDayNumber);

        const schedulesTable = new DataTable('#projectSchedulesTable', {
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            order: [[1, 'asc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: schedulesDataUrl,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'day_number', name: 'day_number' },
                { data: 'schedule_date', name: 'schedule_date' },
                { data: 'topic', name: 'topic' },
                { data: 'remarks', name: 'remarks' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ]
        });

        document.getElementById('projectSchedulesTable').addEventListener('click', function (event) {
            const viewButton = event.target.closest('.project-schedule-view-btn');
            const editButton = event.target.closest('.project-schedule-edit-btn');
            const deleteButton = event.target.closest('.project-schedule-delete-btn');

            if (viewButton) {
                showSchedule(viewButton.dataset.viewUrl);
                return;
            }

            if (editButton && formElement) {
                fetch(editButton.dataset.viewUrl, { headers: { 'Accept': 'application/json' } })
                    .then(assertOk)
                    .then(function (response) { return response.json(); })
                    .then(function (schedule) {
                        openScheduleForm('Edit Schedule', editButton.dataset.updateUrl, schedule);
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to load schedule details.', 'error');
                    });
                return;
            }

            if (deleteButton) {
                confirmAction('Delete Schedule?', 'This action cannot be undone.', function () {
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
                            nextDayNumber = data.next_day_number || nextDayNumber;
                            refreshAddButton();
                            schedulesTable.draw(false);
                            Swal.fire('Deleted', data.message || 'Schedule deleted successfully.', 'success');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to delete schedule. Please try again.', 'error');
                        });
                });
            }
        });

        if (addButton && formElement) {
            addButton.addEventListener('click', function () {
                if (nextDayNumber > scheduleDayLimit) {
                    warningToast('All ' + scheduleDayLimit + ' project schedule records have already been added.');
                    return;
                }
                openScheduleForm('Add Schedule', schedulesStoreUrl, {
                    day_label: 'Day ' + nextDayNumber,
                    schedule_date: '',
                    topic: '',
                    description: '',
                    remarks: ''
                });
            });
        }

        if (formElement) {
            formElement.addEventListener('submit', function (event) {
                event.preventDefault();
                clearErrors();

                const submitButton = document.getElementById('projectScheduleSubmitBtn');
                const formData = new FormData(formElement);

                setButtonLoading(submitButton, true);

                fetch(formElement.dataset.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(function (response) {
                        if (response.status === 422) {
                            return response.json().then(function (data) {
                                showErrors(data.errors || { schedule: [data.message || 'Validation failed.'] });
                                throw new Error('Validation failed');
                            });
                        }
                        return assertOk(response).then(function () { return response.json(); });
                    })
                    .then(function (data) {
                        formModal.hide();
                        nextDayNumber = data.next_day_number || nextDayNumber;
                        refreshAddButton();
                        schedulesTable.draw(false);
                        toast(data.message || 'Schedule saved successfully.');
                    })
                    .catch(function (error) {
                        if (error.message !== 'Validation failed') {
                            Swal.fire('Error', 'Unable to save schedule. Please try again.', 'error');
                        }
                    })
                    .finally(function () {
                        setButtonLoading(submitButton, false);
                    });
            });
        }

        function openScheduleForm(title, actionUrl, schedule) {
            formElement.reset();
            clearErrors();
            document.getElementById('projectScheduleFormTitle').textContent = title;
            document.getElementById('day_number_display').value = schedule.day_label || ('Day ' + nextDayNumber);
            document.getElementById('schedule_date').value = schedule.schedule_date || '';
            document.getElementById('topic').value = schedule.topic || '';
            document.getElementById('description').value = schedule.description || '';
            document.getElementById('remarks').value = schedule.remarks || '';
            formElement.dataset.action = actionUrl;
            formModal.show();
        }

        function showSchedule(viewUrl) {
            fetch(viewUrl, { headers: { 'Accept': 'application/json' } })
                .then(assertOk)
                .then(function (response) { return response.json(); })
                .then(function (schedule) {
                    document.getElementById('projectScheduleViewDay').textContent = schedule.day_label || 'Schedule';
                    document.getElementById('projectScheduleViewTitle').textContent = schedule.topic || 'Schedule Details';
                    document.getElementById('projectScheduleViewContent').innerHTML =
                        infoCard('Day Number', schedule.day_label) +
                        infoCard('Date', schedule.schedule_date || '-') +
                        infoCard('Topic', schedule.topic) +
                        textPanel('Description', schedule.description || '-') +
                        textPanel('Remarks', schedule.remarks || '-');
                    viewModal.show();
                })
                .catch(function () {
                    Swal.fire('Error', 'Unable to open schedule.', 'error');
                });
        }

        function infoCard(label, value) {
            return '<div class="col-lg-4">' +
                '<div class="schedule-info-card">' +
                '<div class="schedule-info-label">' + escapeHtml(label) + '</div>' +
                '<div class="schedule-info-value">' + escapeHtml(value || '-') + '</div>' +
                '</div>' +
                '</div>';
        }

        function textPanel(label, value) {
            return '<div class="col-lg-12">' +
                '<div class="schedule-text-panel">' +
                '<div class="schedule-info-label">' + escapeHtml(label) + '</div>' +
                '<p>' + escapeHtml(value || '-') + '</p>' +
                '</div>' +
                '</div>';
        }

        function refreshAddButton() {
            if (addButton) {
                addButton.disabled = false;
            }
        }

        function clearErrors() {
            formElement.querySelectorAll('.is-invalid').forEach(function (input) {
                input.classList.remove('is-invalid');
            });
            formElement.querySelectorAll('[data-error-for]').forEach(function (element) {
                element.textContent = '';
            });
        }

        function showErrors(errors) {
            let displayed = false;

            Object.keys(errors).forEach(function (key) {
                const input = formElement.querySelector('[name="' + key + '"]') || formElement.querySelector('[name="' + key + '[]"]');
                const feedback = formElement.querySelector('[data-error-for="' + key + '"]') || formElement.querySelector('[data-error-for="' + key + '.0"]');
                if (input) {
                    input.classList.add('is-invalid');
                }
                if (feedback) {
                    feedback.textContent = errors[key][0];
                    displayed = true;
                }
            });

            if (!displayed) {
                Swal.fire('Validation Error', Object.values(errors)[0]?.[0] || 'Please check the schedule details.', 'warning');
            }
        }

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

        function warningToast(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    });
</script>
