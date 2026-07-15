@php
  $teacherData = $substituteTeachers->map(fn ($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])->values();
  $gradeData = $grades->map(fn ($grade) => ['id' => $grade->id, 'name' => $grade->grade])->values();
  $divisionData = $divisions->map(fn ($division) => ['id' => $division->id, 'grade_id' => $division->grade_id, 'name' => $division->division])->values();
  $dateData = collect(Carbon\CarbonPeriod::create($trainingSchedule->start_date, $trainingSchedule->end_date))
      ->filter(fn ($date) => $date->format('l') !== 'Sunday')
      ->map(fn ($date) => [
          'value' => $date->toDateString(), 'label' => $date->format('d M Y'), 'day' => $date->format('l'),
      ])->values();
  $existingData = $allocation->exists ? [
      'date' => $allocation->allocation_date?->toDateString(),
      'grade_id' => $allocation->grade_id ?? $allocation->timetableEntry?->timetable?->grade_id,
      'division_id' => $allocation->division_id ?? $allocation->timetableEntry?->timetable?->divisions?->first()?->id,
      'period_no' => $allocation->period_no ?? $allocation->timetableEntry?->period_no,
      'substitute_id' => $allocation->substitute_teacher_id,
  ] : null;
  $submitUrl = $allocation->exists
      ? route('training-schedules.substitute-allocations.update', [$trainingSchedule, $allocation])
      : route('training-schedules.substitute-allocations.store', $trainingSchedule);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
  const dates = {{ Illuminate\Support\Js::from($dateData) }};
  const grades = {{ Illuminate\Support\Js::from($gradeData) }};
  const divisions = {{ Illuminate\Support\Js::from($divisionData) }};
  const teachers = {{ Illuminate\Support\Js::from($teacherData) }};
  const existing = {{ Illuminate\Support\Js::from($existingData) }};
  const rowsBody = document.getElementById('periodRows');
  let rowCount = 0;

  if (window.jQuery && jQuery.fn.select2) jQuery('#subject_id, #training_schedule_trainer_id').select2({width:'100%'});
  document.getElementById('addAllocationRow')?.addEventListener('click', addRow);
  if (existing) addRow(existing);

  function addRow(values = {}) {
    rowCount++;
    if (rowsBody.querySelector('td[colspan]')) rowsBody.innerHTML = '';
    const chosenTeacher = String(document.getElementById('training_schedule_trainer_id').value || '');
    const allowedDays = new Set(Array.from(document.querySelectorAll('.working-day-check:checked')).map(item => item.value));
    const dateOptions = dates.filter(date => allowedDays.has(date.day)).map(date => option(date.value, date.label+' - '+date.day, values.date)).join('');
    const gradeOptions = grades.map(grade => option(grade.id, grade.name, values.grade_id)).join('');
    const substituteOptions = teachers.filter(teacher => String(teacher.id) !== chosenTeacher).map(teacher => option(teacher.id, teacher.name, values.substitute_id)).join('');
    const periodOptions = Array.from({length:8}, (_, index) => option(index + 1, 'Period '+(index + 1), values.period_no)).join('');
    rowsBody.insertAdjacentHTML('beforeend', '<tr><td class="allocation-row-number">'+rowCount+'</td><td><select class="form-select allocation-date"><option value="">--- Select ---</option>'+dateOptions+'</select></td><td><select class="form-select allocation-grade"><option value="">--- Select ---</option>'+gradeOptions+'</select></td><td><select class="form-select allocation-division"><option value="">--- Select ---</option></select></td><td><select class="form-select allocation-period"><option value="">--- Select ---</option>'+periodOptions+'</select></td><td><select class="form-select substitute-select"><option value="">--- Select ---</option>'+substituteOptions+'</select></td><td><button type="button" class="btn-delete border-0 remove-allocation-row" title="Remove"><i class="fa-solid fa-trash"></i></button></td></tr>');
    const row = rowsBody.lastElementChild;
    const gradeSelect = row.querySelector('.allocation-grade');
    loadDivisions(row, values.division_id);
    initializeRowSelects(row);
    if (window.jQuery) {
      jQuery(gradeSelect).on('change', function () { loadDivisions(row); });
    } else {
      gradeSelect.addEventListener('change', function () { loadDivisions(row); });
    }
  }

  rowsBody.addEventListener('click', function (event) {
    const button = event.target.closest('.remove-allocation-row');
    if (!button) return;
    button.closest('tr').remove();
    renumberRows();
    if (!rowsBody.querySelector('tr')) rowsBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Click Add Row to add an allocation.</td></tr>';
  });

  function renumberRows() {
    rowsBody.querySelectorAll('.allocation-row-number').forEach((cell, index) => cell.textContent = index + 1);
    rowCount = rowsBody.querySelectorAll('.allocation-row-number').length;
  }

  function loadDivisions(row, selected = '') {
    const gradeId = row.querySelector('.allocation-grade').value;
    const divisionSelect = row.querySelector('.allocation-division');
    if (window.jQuery && jQuery.fn.select2 && jQuery(divisionSelect).hasClass('select2-hidden-accessible')) jQuery(divisionSelect).select2('destroy');
    divisionSelect.innerHTML = '<option value="">--- Select ---</option>'+divisions.filter(item => String(item.grade_id)===String(gradeId)).map(item => option(item.id,item.name,selected)).join('');
    if (window.jQuery && jQuery.fn.select2) jQuery(divisionSelect).select2({width:'100%'});
  }

  function initializeRowSelects(row) {
    if (!window.jQuery || !jQuery.fn.select2) return;
    jQuery(row).find('.allocation-date, .allocation-grade, .allocation-period, .substitute-select').select2({width:'100%'});
  }

  document.getElementById('allocationForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const allocations = Array.from(rowsBody.querySelectorAll('tr')).map(row => ({
      timetable_entry_id:null, allocation_date:row.querySelector('.allocation-date')?.value,
      grade_id:row.querySelector('.allocation-grade')?.value, division_id:row.querySelector('.allocation-division')?.value,
      period_no:row.querySelector('.allocation-period')?.value, substitute_teacher_id:row.querySelector('.substitute-select')?.value
    })).filter(row => row.allocation_date);
    const error = document.getElementById('allocationErrors'); error.textContent='';
    if (!document.getElementById('subject_id').value || !document.getElementById('training_schedule_trainer_id').value || !allocations.length) { error.textContent='Add at least one allocation row.'; return; }
    fetch({{ Illuminate\Support\Js::from($submitUrl) }}, {
      method:existing?'PUT':'POST', credentials:'same-origin',
      headers:{'X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json',Accept:'application/json'},
      body:JSON.stringify({subject_id:document.getElementById('subject_id').value,teacher_id:document.getElementById('training_schedule_trainer_id').value,allocations})
    }).then(async response => {
      const contentType = response.headers.get('content-type') || '';
      const data = contentType.includes('application/json') ? await response.json() : null;
      if (!response.ok || !data) throw data || {message:'Unable to save the allocation. Please refresh the page and try again.'};
      return data;
    }).then(data => Swal.fire('Saved',data.message,'success').then(()=>location.href={{ Illuminate\Support\Js::from(route('training-schedules.substitute-allocations.index', $trainingSchedule)) }}))
      .catch(data => error.textContent=data.message||Object.values(data.errors||{}).flat().join(' '));
  });
  function option(value,label,selected){return '<option value="'+value+'" '+(String(value)===String(selected)?'selected':'')+'>'+escapeHtml(label)+'</option>';}
  function escapeHtml(value){const div=document.createElement('div');div.textContent=value??'';return div.innerHTML;}
});
</script>
