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
      <div class="holiday-calendar-box">
        <div id="holidayCalendar"></div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarElement = document.getElementById('holidayCalendar');

      if (!calendarElement || typeof FullCalendar === 'undefined') {
        return;
      }

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
