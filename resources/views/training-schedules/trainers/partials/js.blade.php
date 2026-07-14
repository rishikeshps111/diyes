<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = @json(csrf_token());
    const dataUrl = @json(route('training-schedules.trainers.data', $trainingSchedule));
    const storeUrl = @json(route('training-schedules.trainers.store', $trainingSchedule));
    const form = document.getElementById('trainingTrainerForm');
    const modalElement = document.getElementById('trainingTrainerFormModal');
    const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const addButton = document.getElementById('addTrainingTrainerBtn');
    const designationSelect = document.getElementById('designation_id');
    const teacherSelect = document.getElementById('teacher_id');
    const subjectSelect = document.getElementById('subject_id');
    const submitButton = document.getElementById('trainingTrainerSubmitBtn');
    let formUrl = storeUrl;
    let formMethod = 'POST';
    const teacherOptions = teacherSelect ? Array.from(teacherSelect.options).slice(1).map(function (option) {
      return { value: option.value, text: option.textContent, designationId: option.dataset.designationId || '' };
    }) : [];

    if (modalElement && window.jQuery && jQuery.fn.select2) {
      jQuery('#designation_id, #teacher_id, #subject_id').select2({
        width: '100%', placeholder: '--- Select ---', allowClear: true, dropdownParent: jQuery(modalElement)
      });
      jQuery(designationSelect).on('change', function () { filterTeachers(''); });
    }

    const table = new DataTable('#trainingTrainersTable', {
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: false,
      order: [[5, 'desc']],
      dom: 'rt<"table_bottom"ip>',
      ajax: dataUrl,
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'designation', name: 'designation.designation_name', orderable: false },
        { data: 'name', name: 'teacher.name', orderable: false },
        { data: 'subject', name: 'subject.subject_name', orderable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false },
        { data: 'created_at', name: 'created_at', visible: false, searchable: false }
      ]
    });

    document.getElementById('trainingTrainerSearch').addEventListener('keyup', function () {
      table.search(this.value).draw();
    });
    document.getElementById('trainingTrainerPerPage').addEventListener('change', function () {
      table.page.len(Number(this.value)).draw();
    });

    addButton?.addEventListener('click', function () {
      resetForm();
      document.getElementById('trainingTrainerFormTitle').textContent = 'Add Trainer';
      modal.show();
    });

    document.getElementById('trainingTrainersTable').addEventListener('click', function (event) {
      const editButton = event.target.closest('.training-trainer-edit-btn');
      const deleteButton = event.target.closest('.training-trainer-delete-btn');

      if (editButton && form) {
        fetch(editButton.dataset.viewUrl, { headers: { 'Accept': 'application/json' } })
          .then(assertOk)
          .then(function (response) { return response.json(); })
          .then(function (trainer) {
            resetForm();
            formUrl = editButton.dataset.updateUrl;
            formMethod = 'PUT';
            document.getElementById('trainingTrainerFormTitle').textContent = 'Edit Trainer';
            setSelectValue(designationSelect, trainer.designation_id);
            filterTeachers(String(trainer.teacher_id));
            setSelectValue(subjectSelect, trainer.subject_id);
            modal.show();
          })
          .catch(function () { Swal.fire('Error', 'Unable to load trainer details.', 'error'); });
        return;
      }

      if (deleteButton) {
        Swal.fire({
          title: 'Delete Trainer?', text: 'This action cannot be undone.', icon: 'warning',
          showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, delete it'
        }).then(function (result) {
          if (!result.isConfirmed) return;
          fetch(deleteButton.dataset.deleteUrl, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
          })
            .then(assertOk)
            .then(function (response) { return response.json(); })
            .then(function (data) {
              table.draw(false);
              toast(data.message || 'Trainer deleted successfully.');
            })
            .catch(function () { Swal.fire('Error', 'Unable to delete trainer.', 'error'); });
        });
      }
    });

    form?.addEventListener('submit', function (event) {
      event.preventDefault();
      clearErrors();
      setLoading(true);

      const payload = new FormData(form);
      if (formMethod === 'PUT') payload.append('_method', 'PUT');

      fetch(formUrl, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: payload
      })
        .then(function (response) {
          if (response.status === 422) {
            return response.json().then(function (data) { showErrors(data.errors || {}); throw new Error('validation'); });
          }
          return assertOk(response).then(function (validResponse) { return validResponse.json(); });
        })
        .then(function (data) {
          modal.hide();
          table.draw(false);
          toast(data.message || 'Trainer saved successfully.');
        })
        .catch(function (error) {
          if (error.message !== 'validation') Swal.fire('Error', 'Unable to save trainer.', 'error');
        })
        .finally(function () { setLoading(false); });
    });

    function resetForm() {
      form.reset();
      clearErrors();
      formUrl = storeUrl;
      formMethod = 'POST';
      setSelectValue(designationSelect, '');
      filterTeachers('');
      setSelectValue(subjectSelect, '');
    }

    function filterTeachers(selectedValue) {
      if (!teacherSelect) return;
      const designationId = String(designationSelect.value || '');
      teacherSelect.innerHTML = '<option value="">--- Select ---</option>';
      teacherOptions.filter(function (teacher) {
        return designationId && teacher.designationId === designationId;
      }).forEach(function (teacher) {
        teacherSelect.appendChild(new Option(teacher.text, teacher.value, false, teacher.value === String(selectedValue)));
      });
      setSelectValue(teacherSelect, selectedValue);
    }

    function setSelectValue(select, value) {
      if (!select) return;
      select.value = String(value || '');
      if (window.jQuery && jQuery.fn.select2) jQuery(select).trigger('change.select2');
    }

    function clearErrors() {
      form?.querySelectorAll('.is-invalid').forEach(function (input) { input.classList.remove('is-invalid'); });
      form?.querySelectorAll('[data-error-for]').forEach(function (feedback) { feedback.textContent = ''; });
    }

    function showErrors(errors) {
      Object.keys(errors).forEach(function (key) {
        const input = form.querySelector('[name="' + key + '"]');
        const feedback = form.querySelector('[data-error-for="' + key + '"]');
        input?.classList.add('is-invalid');
        if (feedback) feedback.textContent = errors[key][0];
      });
    }

    function setLoading(loading) {
      if (loading) {
        submitButton.dataset.originalHtml = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + submitButton.dataset.loadingText;
      } else {
        submitButton.disabled = false;
        if (submitButton.dataset.originalHtml) submitButton.innerHTML = submitButton.dataset.originalHtml;
      }
    }

    function assertOk(response) {
      if (!response.ok) throw new Error('Request failed');
      return Promise.resolve(response);
    }

    function toast(message) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: message, showConfirmButton: false, timer: 1800 });
    }
  });
</script>
