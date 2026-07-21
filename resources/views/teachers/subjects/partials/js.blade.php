<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';
    const form = document.getElementById('teacherSubjectForm');
    const modalElement = document.getElementById('teacherSubjectFormModal');
    const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const storeUrl = '{{ route('teachers.subjects.store', $teacher) }}';

    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#grade_id, #subject_id').select2({ width: '100%', placeholder: '--- Select ---', allowClear: true, dropdownParent: jQuery('#teacherSubjectFormModal') });
    }

    const table = new DataTable('#teacherSubjectsTable', {
      processing: true, serverSide: true, searching: false, lengthChange: false,
      order: [[4, 'desc']], dom: 'rt<"table_bottom"ip>', ajax: '{{ route('teachers.subjects.data', $teacher) }}',
      columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'grade', name: 'subject.grade.grade', orderable: false },
        { data: 'subject_name', name: 'subject.subject_name', orderable: false },
        { data: 'actions', orderable: false, searchable: false },
        { data: 'created_at', visible: false, searchable: false }
      ]
    });

    document.getElementById('addTeacherSubjectBtn')?.addEventListener('click', function () { openForm('Add Subject', storeUrl, ''); });

    document.getElementById('teacherSubjectsTable').addEventListener('click', function (event) {
      const editButton = event.target.closest('.teacher-subject-edit-btn');
      const deleteButton = event.target.closest('.teacher-subject-delete-btn');
      if (editButton && form) {
        fetch(editButton.dataset.viewUrl, { headers: { Accept: 'application/json' } }).then(assertOk).then(r => r.json())
          .then(data => openForm('Edit Subject', editButton.dataset.updateUrl, data.grade_id, data.subject_id))
          .catch(() => Swal.fire('Error', 'Unable to load subject details.', 'error'));
      }
      if (deleteButton) {
        Swal.fire({ title: 'Delete Subject?', text: 'This assignment will be removed.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!' }).then(function (result) {
          if (!result.isConfirmed) return;
          fetch(deleteButton.dataset.deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' } }).then(assertOk).then(r => r.json())
            .then(data => { table.draw(false); Swal.fire('Deleted', data.message, 'success'); })
            .catch(() => Swal.fire('Error', 'Unable to delete subject assignment.', 'error'));
        });
      }
    });

    form?.addEventListener('submit', function (event) {
      event.preventDefault(); clearErrors();
      const button = document.getElementById('teacherSubjectSubmitBtn');
      button.disabled = true;
      fetch(form.dataset.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }, body: new FormData(form) })
        .then(function (response) { if (response.status === 422) return response.json().then(data => { showErrors(data.errors || {}); throw new Error('validation'); }); assertOk(response); return response.json(); })
        .then(data => { modal.hide(); table.draw(false); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2500 }); })
        .catch(error => { if (error.message !== 'validation') Swal.fire('Error', 'Unable to save subject assignment.', 'error'); })
        .finally(() => { button.disabled = false; });
    });

    function openForm(title, action, gradeId, subjectId) {
      form.reset(); clearErrors(); form.dataset.action = action;
      document.getElementById('teacherSubjectFormTitle').textContent = title;
      document.getElementById('grade_id').value = gradeId || '';
      document.getElementById('subject_id').value = subjectId || '';
      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#grade_id').val(gradeId || null).trigger('change.select2');
        jQuery('#subject_id').val(subjectId || null).trigger('change.select2');
      }
      modal.show();
    }
    function clearErrors() { form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); form.querySelectorAll('[data-error-for]').forEach(el => el.textContent = ''); }
    function showErrors(errors) { Object.keys(errors).forEach(key => { const input = form.querySelector('[name="' + key + '"]'); const feedback = form.querySelector('[data-error-for="' + key + '"]'); input?.classList.add('is-invalid'); if (feedback) feedback.textContent = errors[key][0]; }); }
    function assertOk(response) { if (!response.ok) throw new Error('Request failed'); return response; }
  });
</script>
