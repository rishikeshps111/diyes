@extends('layouts.app')

@section('title', 'Holiday Calender')

@push('styles')
  <style>
    .holiday-calendar-toolbar {
      align-items: end;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: space-between;
    }

    .holiday-calendar-box {
      background: #fff;
      border: 1px solid #e4e7ec;
      border-radius: 8px;
      padding: 14px;
    }

    .holiday-calendar-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 10px 18px;
      margin-bottom: 14px;
    }

    .holiday-calendar-legend span,
    .holiday-month-item-type {
      align-items: center;
      display: inline-flex;
      gap: 7px;
    }

    .holiday-calendar-legend i,
    .holiday-month-item-type i {
      border-radius: 999px;
      display: inline-block;
      height: 10px;
      width: 10px;
    }

    .holiday-month-panel {
      background: #fff;
      border: 1px solid #e4e7ec;
      border-radius: 8px;
      height: 100%;
      padding: 14px;
    }

    .holiday-month-panel h5 {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .holiday-month-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 680px;
      overflow-y: auto;
    }

    .holiday-month-item {
      border: 1px solid #edf0f4;
      border-left: 4px solid var(--holiday-color, #c62828);
      border-radius: 8px;
      padding: 10px 12px;
    }

    .holiday-month-item h6 {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 6px;
    }

    .holiday-month-item p {
      color: #667085;
      font-size: 13px;
      margin-bottom: 4px;
    }

    .holiday-month-empty {
      color: #667085;
      margin: 0;
    }

    #holidayCalendar .fc-toolbar-title {
      font-size: 20px;
      font-weight: 700;
    }

    #holidayCalendar .fc-button-primary {
      background-color: #aeb239;
      border-color: #aeb239;
    }

    #holidayCalendar .fc-daygrid-day.fc-day-sun,
    #holidayCalendar .holiday-second-saturday {
      background: #fff1f1;
    }

    #holidayCalendar .fc-day-sun .fc-daygrid-day-number,
    #holidayCalendar .holiday-second-saturday .fc-daygrid-day-number {
      color: #c62828;
      font-weight: 700;
    }

    #holidayCalendar .fc-event {
      cursor: pointer;
    }
  </style>
@endpush

@section('content')
  <div class="page-title">
    <h3>Holiday Calender</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li>
        <li class="breadcrumb-item active">Holiday Calender</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <form method="GET" action="{{ route('holidays.calendar') }}" class="holiday-calendar-toolbar">
        <div class="o-f-inp" style="min-width: 260px;">
          <label for="academic_year_id">Academic Year</label>
          <select name="academic_year_id" id="academic_year_id" class="form-select shadow-none">
            @foreach ($academicYears as $academicYear)
              <option value="{{ $academicYear->id }}" @selected($selectedAcademicYearId === $academicYear->id)>
                {{ $academicYear->academic_year }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="btn-flex">
          <a href="{{ route('holidays.index') }}" class="btn btn-danger">Back</a>
          <button type="submit" class="submit-btn">Show</button>
        </div>
      </form>
    </div>

    <div class="main-table-container">
      <div class="holiday-calendar-legend">
        @foreach ($holidayTypeColors as $holidayType => $color)
          <span><i style="background-color: {{ $color }}"></i>{{ ucfirst($holidayType) }}</span>
        @endforeach
      </div>

      <div class="row g-3">
        <div class="col-lg-8">
          <div class="holiday-calendar-box">
            <div id="holidayCalendar"></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="holiday-month-panel">
            <h5 id="holidayMonthTitle">Holidays This Month</h5>
            <div id="holidayMonthList" class="holiday-month-list">
              <p class="holiday-month-empty">No holidays found for this month.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarElement = document.getElementById('holidayCalendar');
      const monthTitleElement = document.getElementById('holidayMonthTitle');
      const monthListElement = document.getElementById('holidayMonthList');

      if (!calendarElement || typeof FullCalendar === 'undefined') {
        return;
      }

      const holidayEvents = @json($events).filter(function (event) {
        return event.extendedProps && event.extendedProps.kind === 'holiday';
      });

      const calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: 'dayGridMonth',
        initialDate: @json($initialDate),
        validRange: @json($validRange),
        height: 'auto',
        dayMaxEvents: 3,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: ''
        },
        events: @json($events),
        datesSet: function (info) {
          renderMonthHolidays(info.view.currentStart, info.view.currentEnd);
        },
        dayCellClassNames: function (arg) {
          const date = arg.date;
          const isSecondSaturday = date.getDay() === 6 && date.getDate() >= 8 && date.getDate() <= 14;

          return isSecondSaturday ? ['holiday-second-saturday'] : [];
        },
        eventClick: function (info) {
          const event = info.event;
          const props = event.extendedProps || {};

          if (props.kind !== 'holiday') {
            Swal.fire({
              title: event.title,
              text: props.description || 'Holiday',
              confirmButtonText: 'Close'
            });
            return;
          }

          Swal.fire({
            title: event.title,
            html: '<div class="text-start">' +
              '<p><strong>Type:</strong> ' + escapeHtml(props.type) + '</p>' +
              '<p><strong>Holiday Date:</strong> ' + escapeHtml(props.holidayDate) + '</p>' +
              '<p><strong>Date Range:</strong> ' + escapeHtml(props.range) + '</p>' +
              '<p><strong>Applicable For:</strong> ' + escapeHtml(props.applicableFor) + '</p>' +
              '<p><strong>Description:</strong> ' + escapeHtml(props.description) + '</p>' +
              '</div>',
            confirmButtonText: 'Close'
          });
        }
      });

      calendar.render();

      function renderMonthHolidays(monthStart, monthEnd) {
        const monthHolidays = holidayEvents
          .filter(function (event) {
            const eventStart = dateOnly(event.start);
            const eventEnd = event.end ? dateOnly(event.end) : addDays(eventStart, 1);

            return eventStart < monthEnd && eventEnd > monthStart;
          })
          .sort(function (first, second) {
            return dateOnly(first.start) - dateOnly(second.start);
          });

        monthTitleElement.textContent = monthStart.toLocaleDateString(undefined, {
          month: 'long',
          year: 'numeric'
        }) + ' Holidays';

        if (!monthHolidays.length) {
          monthListElement.innerHTML = '<p class="holiday-month-empty">No holidays found for this month.</p>';
          return;
        }

        monthListElement.innerHTML = monthHolidays.map(function (event) {
          const props = event.extendedProps || {};
          const color = props.typeColor || event.color || '#c62828';

          return '<div class="holiday-month-item" style="--holiday-color: ' + escapeHtml(color) + '">' +
            '<h6>' + escapeHtml(event.title) + '</h6>' +
            '<p class="holiday-month-item-type"><i style="background-color: ' + escapeHtml(color) + '"></i>' +
            escapeHtml(props.type) + '</p>' +
            '<p><strong>Date:</strong> ' + escapeHtml(props.holidayDate) + '</p>' +
            '<p><strong>Range:</strong> ' + escapeHtml(props.range) + '</p>' +
            '<p><strong>Applicable For:</strong> ' + escapeHtml(props.applicableFor) + '</p>' +
            '</div>';
        }).join('');
      }

      function dateOnly(value) {
        const date = value instanceof Date ? value : new Date(value);

        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
      }

      function addDays(date, days) {
        const nextDate = new Date(date);
        nextDate.setDate(nextDate.getDate() + days);

        return nextDate;
      }

      function escapeHtml(value) {
        return String(value || '-')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }
    });
  </script>
@endpush
