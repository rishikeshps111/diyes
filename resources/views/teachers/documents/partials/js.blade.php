<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';
        const documentFormElement = document.getElementById('teacherDocumentForm');
        const documentFormModalElement = document.getElementById('teacherDocumentFormModal');
        const documentFormModal = documentFormModalElement ? new bootstrap.Modal(documentFormModalElement) : null;
        const documentViewerModal = new bootstrap.Modal(document.getElementById('documentViewerModal'));
        const documentsDataUrl = '{{ route('teachers.documents.data', $teacher) }}';
        const documentsStoreUrl = '{{ route('teachers.documents.store', $teacher) }}';
        const existingDocumentTypes = new Set();
        let currentDocumentType = '';

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#document_type').select2({
                width: '100%',
                placeholder: '--- Select ---',
                allowClear: true,
                dropdownParent: jQuery('#teacherDocumentFormModal')
            });
        }

        const documentsTable = new DataTable('#teacherDocumentsTable', {
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            order: [[6, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: documentsDataUrl,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'document_type', name: 'document_type' },
                { data: 'verification_status', name: 'verification_status' },
                { data: 'verified_by_name', name: 'verified_by_name', orderable: false, searchable: false },
                { data: 'verified_at', name: 'verified_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ]
        });

        documentsTable.on('xhr', function (event, settings, json) {
            existingDocumentTypes.clear();

            (json?.data || []).forEach(function (row) {
                if (row.document_type) {
                    existingDocumentTypes.add(stripHtml(row.document_type));
                }
            });
        });

        document.getElementById('teacherDocumentsTable').addEventListener('click', function (event) {
            const viewButton = event.target.closest('.teacher-document-view-btn');
            const editButton = event.target.closest('.teacher-document-edit-btn');
            const deleteButton = event.target.closest('.teacher-document-delete-btn');
            const verifyButton = event.target.closest('.teacher-document-verify-btn');

            if (viewButton) {
                showDocument(viewButton.dataset.viewUrl);
                return;
            }

            if (editButton && documentFormElement) {
                fetch(editButton.dataset.viewUrl, { headers: { 'Accept': 'application/json' } })
                    .then(assertOk)
                    .then(function (response) { return response.json(); })
                    .then(function (documentData) {
                        openDocumentForm('Edit Document', editButton.dataset.updateUrl, documentData.document_type, false);
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Unable to load document details.', 'error');
                    });
                return;
            }

            if (deleteButton) {
                confirmAction('Delete Document?', 'This action cannot be undone.', function () {
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
                            documentsTable.draw(false);
                            Swal.fire('Deleted', data.message || 'Document deleted successfully.', 'success');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to delete document. Please try again.', 'error');
                        });
                });
                return;
            }

            if (verifyButton) {
                confirmAction('Verify Document?', 'This document will be marked as verified.', function () {
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
                            documentsTable.draw(false);
                            toast(data.message || 'Document verified successfully.');
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Unable to verify document. Please try again.', 'error');
                        });
                });
            }
        });

        const addDocumentButton = document.getElementById('addDocumentBtn');
        if (addDocumentButton && documentFormElement) {
            addDocumentButton.addEventListener('click', function () {
                openDocumentForm('Add Document', documentsStoreUrl, '', true);
            });
        }

        if (documentFormElement) {
            documentFormElement.addEventListener('submit', function (event) {
                event.preventDefault();
                clearDocumentErrors();

                const submitButton = document.getElementById('teacherDocumentSubmitBtn');
                const formData = new FormData(documentFormElement);
                const selectedDocumentType = formData.get('document_type');

                if (isDuplicateDocumentType(selectedDocumentType)) {
                    showDocumentErrors({
                        document_type: ['This document type is already added for this teacher.']
                    });
                    return;
                }

                setButtonLoading(submitButton, true);

                fetch(documentFormElement.dataset.action, {
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
                                showDocumentErrors(data.errors || {});
                                throw new Error('Validation failed');
                            });
                        }
                        return assertOk(response).then(function () { return response.json(); });
                    })
                    .then(function (data) {
                        documentFormModal.hide();
                        documentsTable.draw(false);
                        toast(data.message || 'Document saved successfully.');
                    })
                    .catch(function (error) {
                        if (error.message !== 'Validation failed') {
                            Swal.fire('Error', 'Unable to save document. Please try again.', 'error');
                        }
                    })
                    .finally(function () {
                        setButtonLoading(submitButton, false);
                    });
            });
        }

        function showDocument(viewUrl) {
            fetch(viewUrl, { headers: { 'Accept': 'application/json' } })
                .then(assertOk)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    const url = data.document_file_url;
                    const lowerUrl = url.toLowerCase();
                    document.getElementById('documentViewerTitle').textContent = data.document_type;
                    document.getElementById('documentViewerMeta').innerHTML =
                        '<div class="col-lg-4"><strong>Document Type</strong><div>' + escapeHtml(data.document_type) + '</div></div>' +
                        '<div class="col-lg-4"><strong>Verification Status</strong><div>' + escapeHtml(data.verification_status) + '</div></div>' +
                        '<div class="col-lg-4"><strong>File Name</strong><div>' + escapeHtml(data.file_name) + '</div></div>';
                    document.getElementById('documentViewerContent').innerHTML = lowerUrl.endsWith('.pdf')
                        ? '<iframe src="' + url + '" class="w-100 teacher-document-frame"></iframe>'
                        : '<img src="' + url + '" alt="' + escapeHtml(data.document_type) + '" class="img-fluid d-block mx-auto">';
                    documentViewerModal.show();
                })
                .catch(function () {
                    Swal.fire('Error', 'Unable to open document.', 'error');
                });
        }

        function openDocumentForm(title, actionUrl, documentType, fileRequired) {
            documentFormElement.reset();
            clearDocumentErrors();
            currentDocumentType = documentType || '';
            document.getElementById('teacherDocumentFormTitle').textContent = title;
            refreshDocumentTypeOptions(currentDocumentType);
            document.getElementById('document_type').value = documentType || '';
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#document_type').val(documentType || null).trigger('change');
            }
            document.getElementById('document_file').required = fileRequired;
            documentFormElement.dataset.action = actionUrl;
            documentFormModal.show();
        }

        function refreshDocumentTypeOptions(allowedDocumentType) {
            document.getElementById('document_type').querySelectorAll('option').forEach(function (option) {
                if (!option.value) {
                    option.disabled = false;
                    return;
                }

                option.disabled = existingDocumentTypes.has(option.value) && option.value !== allowedDocumentType;
            });

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#document_type').trigger('change.select2');
            }
        }

        function isDuplicateDocumentType(documentType) {
            return !!documentType && existingDocumentTypes.has(documentType) && documentType !== currentDocumentType;
        }

        function clearDocumentErrors() {
            documentFormElement.querySelectorAll('.is-invalid').forEach(function (input) {
                input.classList.remove('is-invalid');
            });
            documentFormElement.querySelectorAll('[data-error-for]').forEach(function (element) {
                element.textContent = '';
            });
        }

        function showDocumentErrors(errors) {
            Object.keys(errors).forEach(function (key) {
                const input = documentFormElement.querySelector('[name="' + key + '"]');
                const feedback = documentFormElement.querySelector('[data-error-for="' + key + '"]');
                if (input) {
                    input.classList.add('is-invalid');
                }
                if (feedback) {
                    feedback.textContent = errors[key][0];
                }
            });
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

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function stripHtml(value) {
            const element = document.createElement('div');
            element.innerHTML = value;
            return element.textContent || element.innerText || '';
        }
    });
</script>
