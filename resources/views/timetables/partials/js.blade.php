<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedTimetables = new Set();
        const selectAll = document.getElementById('selectAllTimetables');
        const applyFiltersButton = document.getElementById('applyFilters');
        const resetFiltersButton = document.getElementById('resetFilters');
        const previewModalElement = document.getElementById('timetablePreviewModal');
        const previewModal = previewModalElement ? new bootstrap.Modal(previewModalElement) : null;
        const previewTitle = document.getElementById('timetablePreviewTitle');
        const previewSubtitle = document.getElementById('timetablePreviewSubtitle');
        const previewPdf = document.getElementById('timetablePreviewPdf');
        const previewHead = document.getElementById('timetablePreviewHead');
        const previewBody = document.getElementById('timetablePreviewBody');
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
            const previewButton = event.target.closest('.timetable-preview-btn');

            if (previewButton) {
                openTimetablePreview(previewButton.dataset.previewUrl, previewButton.dataset.pdfUrl);
                return;
            }

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

        function openTimetablePreview(url, pdfUrl) {
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
                .then(renderTimetablePreview)
                .catch(function () {
                    previewBody.innerHTML = '<tr><td class="text-center text-danger">Unable to load generated timetable.</td></tr>';
                });
        }

        function renderTimetablePreview(data) {
            const days = data.days || [];
            const periods = data.periods || [];
            const breaks = data.breaks || [];
            const totalPeriods = Number(data.timetable?.total_periods || 0);
            const colspan = days.length + 2;

            previewTitle.textContent = data.timetable?.name || 'View TimeTable';
            previewSubtitle.textContent = 'Grade: ' + (data.timetable?.grade || '-') + ' | Divisions: ' + (data.timetable?.divisions || '-');
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

                        return '<td style="background-color: ' + escapeHtml(entry.color || '#ffffff') + ';"><span class="preview-cell-title">' + escapeHtml(entry.subject || '-') + '</span><span class="preview-cell-meta">' + (teachers || '-') + '</span></td>';
                    }).join('') + '</tr>';

                ['short_break', 'lunch_break'].forEach(function (type) {
                    const breakEntry = breaks.find(function (entry) {
                        return Number(entry.period_no) === period && entry.type === type;
                    });

                    if (!breakEntry) {
                        return;
                    }

                    const rowClass = type === 'lunch_break' ? 'lunch' : 'break';
                    html += '<tr class="' + rowClass + '"><td colspan="' + colspan + '">' +
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
