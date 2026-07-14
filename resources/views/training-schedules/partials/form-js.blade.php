<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('trainingScheduleForm');
    const statusInput = document.getElementById('status');
    const submitButtons = Array.from(document.querySelectorAll('.training-schedule-submit-btn'));
    const sessionsBody = document.getElementById('scheduleSessionsBody');
    const addSessionButton = document.getElementById('addSessionBtn');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const applicable = document.getElementById('applicable');
    const subjectsField = document.getElementById('teachingSubjectsField');
    const subjects = document.getElementById('subject_ids');
    let pendingButton = null;
    let publishConfirmed = false;

    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#trainer_type_id, #trainer_category_id, #conducted_by, #mode, #applicable').select2({
        width: '100%', placeholder: '--- Select ---', allowClear: true
      });
      jQuery('#subject_ids').select2({ width: '100%', placeholder: '--- Select Subjects ---' });
      jQuery(applicable).on('change', toggleSubjects);
    }

    submitButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        statusInput.value = button.dataset.status;
        pendingButton = button;
      });
    });

    form.addEventListener('submit', function (event) {
      const activeButton = pendingButton || submitButtons[0];

      if (statusInput.value === 'published' && !publishConfirmed) {
        event.preventDefault();
        Swal.fire({
          title: 'Please confirm the Data',
          text: 'Review the training information and schedule details before publishing.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, publish',
          cancelButtonText: 'No'
        }).then(function (result) {
          if (result.isConfirmed) {
            publishConfirmed = true;
            form.requestSubmit(activeButton);
          }
        });
        return;
      }

      submitButtons.forEach(function (button) { button.disabled = true; });
      activeButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
        activeButton.dataset.loadingText;
    });

    addSessionButton.addEventListener('click', function () {
      const index = sessionsBody.querySelectorAll('.schedule-session-row').length;
      sessionsBody.insertAdjacentHTML('beforeend', sessionRow(index));
      syncDateLimits();
      reindexSessions();
    });

    sessionsBody.addEventListener('click', function (event) {
      const button = event.target.closest('.remove-session-btn');
      if (!button) return;

      if (sessionsBody.querySelectorAll('.schedule-session-row').length === 1) {
        Swal.fire('Schedule Required', 'At least one session is required.', 'warning');
        return;
      }

      button.closest('.schedule-session-row').remove();
      reindexSessions();
    });

    sessionsBody.addEventListener('change', function (event) {
      if (event.target.classList.contains('time-from') || event.target.classList.contains('time-to')) {
        calculateDuration(event.target.closest('.schedule-session-row'));
      }
    });

    startDate.addEventListener('change', function () {
      endDate.min = startDate.value;
      if (endDate.value && endDate.value < startDate.value) endDate.value = '';
      syncDateLimits();
    });
    endDate.addEventListener('change', syncDateLimits);
    applicable.addEventListener('change', toggleSubjects);

    toggleSubjects();
    syncDateLimits();
    sessionsBody.querySelectorAll('.schedule-session-row').forEach(calculateDurationIfEmpty);

    function toggleSubjects() {
      const visible = applicable.value === 'teachers';
      subjectsField.classList.toggle('d-none', !visible);
      subjects.disabled = !visible;
      if (window.jQuery && jQuery.fn.select2) jQuery(subjects).trigger('change.select2');
    }

    function syncDateLimits() {
      endDate.min = startDate.value || '';
      sessionsBody.querySelectorAll('.session-date').forEach(function (input) {
        input.min = startDate.value || '';
        input.max = endDate.value || '';
      });
    }

    function calculateDurationIfEmpty(row) {
      if (!row.querySelector('.duration-hours').value) calculateDuration(row);
    }

    function calculateDuration(row) {
      const from = row.querySelector('.time-from').value;
      const to = row.querySelector('.time-to').value;
      const duration = row.querySelector('.duration-hours');

      if (!from || !to) {
        duration.value = '';
        return;
      }

      const fromParts = from.split(':').map(Number);
      const toParts = to.split(':').map(Number);
      const minutes = (toParts[0] * 60 + toParts[1]) - (fromParts[0] * 60 + fromParts[1]);
      duration.value = minutes > 0 ? (minutes / 60).toFixed(2) : '';
    }

    function reindexSessions() {
      sessionsBody.querySelectorAll('.schedule-session-row').forEach(function (row, index) {
        row.querySelector('.session-number').textContent = index + 1;
        row.querySelectorAll('[name]').forEach(function (input) {
          input.name = input.name.replace(/sessions\[\d+\]/, 'sessions[' + index + ']');
        });
      });
    }

    function sessionRow(index) {
      const dateValue = startDate.value || '';
      return '<tr class="schedule-session-row">' +
        '<td class="session-number">' + (index + 1) + '</td>' +
        '<td><input type="date" name="sessions[' + index + '][session_date]" value="' + dateValue + '" class="form-control shadow-none session-date"></td>' +
        '<td><input type="time" name="sessions[' + index + '][time_from]" class="form-control shadow-none time-from"></td>' +
        '<td><input type="time" name="sessions[' + index + '][time_to]" class="form-control shadow-none time-to"></td>' +
        '<td><input type="text" name="sessions[' + index + '][topic_module]" class="form-control shadow-none topic-module"></td>' +
        '<td><input type="number" name="sessions[' + index + '][duration_hours]" step="0.01" class="form-control shadow-none duration-hours" readonly></td>' +
        '<td><div class="action-btns"><button type="button" class="btn-delete border-0 remove-session-btn"><i class="fa-solid fa-trash"></i></button></div></td>' +
        '</tr>';
    }
  });
</script>
