<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedProjectWeeks = new Set();
        const selectAll = document.getElementById('selectAllProjectWeeks');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const previewModalElement = document.getElementById('projectWeekPreviewModal');
        const previewModal = previewModalElement ? new bootstrap.Modal(previewModalElement) : null;
        const previewTitle = document.getElementById('projectWeekPreviewTitle');
        const previewSubtitle = document.getElementById('projectWeekPreviewSubtitle');
        const previewPdf = document.getElementById('projectWeekPreviewPdf');
        const previewHead = document.getElementById('projectWeekPreviewHead');
        const previewBody = document.getElementById('projectWeekPreviewBody');
        const csrfToken = '{{ csrf_token() }}';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#grade_filter, #status_filter').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true
            });
        }

        const table = new DataTable('#projectWeeksTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[12, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: {
                url: '{{ route('project-weeks.data') }}',
                data: function (data) {
                    data.grade_id = document.getElementById('grade_filter').value;
                    data.status = document.getElementById('status_filter').value;
                }
            },
            columns: [
                { data: 'select', name: 'select', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'project', name: 'project', orderable: false },
                { data: 'applicable_from', name: 'applicable_from' },
                { data: 'applicable_to', name: 'applicable_to' },
                { data: 'academic_year', name: 'academic_year', orderable: false },
                { data: 'grade', name: 'grade', orderable: false },
                { data: 'division', name: 'division', orderable: false },
                { data: 'total_periods', name: 'total_periods' },
                { data: 'status', name: 'status', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ],
            drawCallback: function () {
                document.querySelectorAll('.project-week-row-check').forEach(function (checkbox) {
                    checkbox.checked = selectedProjectWeeks.has(checkbox.value);
                });
                syncSelectAll();
            }
        });

        document.getElementById('projectWeekTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('projectWeekPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });

        applyFiltersButton.addEventListener('click', function () {
            setButtonLoading(applyFiltersButton, true);
            table.draw();
        });

        resetFiltersButton.addEventListener('click', function () {
            setButtonLoading(resetFiltersButton, true);
            ['grade_filter', 'status_filter'].forEach(function (id) {
                document.getElementById(id).value = '';
            });

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#grade_filter, #status_filter').val(null).trigger('change');
            }

            table.search('').draw();
        });

        table.on('draw', function () {
            setButtonLoading(applyFiltersButton, false);
            setButtonLoading(resetFiltersButton, false);
        });

        document.getElementById('projectWeeksTable').addEventListener('change', function (event) {
            if (!event.target.classList.contains('project-week-row-check')) {
                return;
            }

            event.target.checked
                ? selectedProjectWeeks.add(event.target.value)
                : selectedProjectWeeks.delete(event.target.value);

            syncSelectAll();
        });

        document.getElementById('projectWeeksTable').addEventListener('click', function (event) {
            const previewButton = event.target.closest('.project-week-preview-btn');

            if (previewButton) {
                openProjectWeekPreview(previewButton.dataset.previewUrl, previewButton.dataset.pdfUrl);
                return;
            }

            const deleteButton = event.target.closest('.project-week-delete-btn');

            if (!deleteButton) {
                return;
            }

            Swal.fire({
                title: 'Delete Project Week?',
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
                        selectedProjectWeeks.delete(deleteButton.closest('tr')?.querySelector('.project-week-row-check')?.value);
                        table.draw(false);
                        Swal.fire('Deleted', data.message || 'Project week deleted successfully.', 'success');
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to delete project week. Please try again.', 'error');
                    });
            });
        });

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.project-week-row-check').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
                selectAll.checked
                    ? selectedProjectWeeks.add(checkbox.value)
                    : selectedProjectWeeks.delete(checkbox.value);
            });
        });

        document.querySelectorAll('[data-export-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!selectedProjectWeeks.size) {
                    Swal.fire('No Rows Selected', 'Select at least one project week to export.', 'warning');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);

                selectedProjectWeeks.forEach(function (id) {
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

                        clearSelectedProjectWeeks();

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
                        Swal.fire('Error', 'Unable to export selected project weeks. Please try again.', 'error');
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

        function clearSelectedProjectWeeks() {
            selectedProjectWeeks.clear();
            document.querySelectorAll('.project-week-row-check').forEach(function (checkbox) {
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

            return exportUrl.includes('/pdf') ? 'project-weeks.pdf' : 'project-weeks.xlsx';
        }

        function syncSelectAll() {
            const visibleChecks = Array.from(document.querySelectorAll('.project-week-row-check'));
            selectAll.checked = visibleChecks.length > 0 && visibleChecks.every(function (checkbox) {
                return checkbox.checked;
            });
        }

        function openProjectWeekPreview(url, pdfUrl) {
            if (!previewModal) {
                return;
            }

            previewTitle.textContent = 'View TimeTable';
            previewSubtitle.textContent = '';
            previewPdf.href = pdfUrl || '#';
            previewPdf.classList.toggle('d-none', !pdfUrl);
            previewHead.innerHTML = '';
            previewBody.innerHTML = '<tr><td class="text-center text-muted">Loading timetable...</td></tr>';
            previewModal.show();

            fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Preview failed');
                    }

                    return response.json();
                })
                .then(renderProjectWeekPreview)
                .catch(function () {
                    previewBody.innerHTML = '<tr><td class="text-center text-danger">Unable to load generated timetable.</td></tr>';
                });
        }

        function renderProjectWeekPreview(data) {
            const days = data.days || [];
            const periods = data.periods || [];
            const breaks = data.breaks || [];
            const totalPeriods = Number(data.projectWeek?.total_periods || 0);
            const colspan = days.length + 2;

            previewTitle.textContent = data.projectWeek?.project || 'View TimeTable';
            previewSubtitle.textContent = 'Applicable: ' + (data.projectWeek?.applicable_from || '-') + ' to ' + (data.projectWeek?.applicable_to || '-') +
                ' | Grade: ' + (data.projectWeek?.grade || '-') + ' | Divisions: ' + (data.projectWeek?.divisions || '-');
            previewHead.innerHTML = '<tr><th>Period</th><th>Time</th>' + days.map(escapeTh).join('') + '</tr>';

            if (!days.length || !periods.length) {
                previewBody.innerHTML = '<tr><td colspan="' + Math.max(colspan, 1) + '" class="text-center text-muted">No generated timetable entries found.</td></tr>';
                return;
            }

            let html = '';

            for (let period = 1; period <= totalPeriods; period++) {
                const periodEntries = periods.filter(function (entry) {
                    return Number(entry.period_no) === period;
                });
                const timeEntry = periodEntries.find(function (entry) {
                    return entry.start_time && entry.end_time;
                });

                html += '<tr><th>Period ' + period + '</th><td>' + (timeEntry ? escapeHtml(timeEntry.start_time + ' - ' + timeEntry.end_time) : '-') + '</td>' +
                    days.map(function (day) {
                        const entry = periodEntries.find(function (item) {
                            return item.day === day;
                        });

                        if (!entry) {
                            return '<td>-</td>';
                        }

                        const teachers = (entry.teachers || []).map(function (teacher, index) {
                            return 'T' + (index + 1) + ': ' + escapeHtml(teacher);
                        }).join('<br>');
                        const marker = entry.is_project_period ? '<span class="badge bg-success mb-1">Project Period</span><br>' : '';

                        return '<td style="background-color: ' + escapeHtml(entry.color || '#ffffff') + ';">' + marker +
                            '<span class="fw-bold">' + escapeHtml(entry.subject || '-') + '</span><br><span class="text-muted small">' + (teachers || '-') + '</span></td>';
                    }).join('') + '</tr>';

                ['short_break', 'lunch_break'].forEach(function (type) {
                    const breakEntry = breaks.find(function (entry) {
                        return Number(entry.period_no) === period && entry.type === type;
                    });

                    if (!breakEntry) {
                        return;
                    }

                    const rowClass = type === 'lunch_break' ? 'table-success' : 'table-warning';
                    html += '<tr class="' + rowClass + '"><td colspan="' + colspan + '" class="text-center fw-bold">' +
                        escapeHtml(breakEntry.label + ' (' + breakEntry.duration_minutes + ' mins) - ' + breakEntry.start_time + ' - ' + breakEntry.end_time) +
                        '</td></tr>';
                });
            }

            previewBody.innerHTML = html;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeTh(value) {
            return '<th>' + escapeHtml(value) + '</th>';
        }
    });
</script>
