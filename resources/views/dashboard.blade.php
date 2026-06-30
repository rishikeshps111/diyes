@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="page-title">
    <h3>Dashboard</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="dashboard-btns">
      <ul>
        <li><a href="{{ asset('timetable-management/generate-table.html') }}">Generate Timetable</a></li>
        <li><a href="{{ asset('timetable-management/add-substitute.html') }}">Assign Substitute</a></li>
        <li><a href="{{ asset('timetable-management/regular-timetable.html') }}">Publish Timetable</a></li>
        <li><a href="{{ asset('timetable-management/add-special-events.html') }}">Create Event</a></li>
        <li><a href="{{ asset('teacher-management/add-teacher.html') }}">Add Teacher</a></li>
      </ul>
    </div>

    <div class="col-lg-12">
      <h3 class="title-dash">Academic Overview</h3>
    </div>

    <div class="row row-col--5">
      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('academic-management/academic-year.html') }}">
          <div class="dashboard-card purple">
            <div class="dash-card-icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="dash-card-content">
              <h6>Current Academic Year</h6>
              <h2>2025-2026</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('academic-management/grade.html') }}">
          <div class="dashboard-card blue">
            <div class="dash-card-icon">
              <i class="fas fa-layer-group"></i>
            </div>
            <div class="dash-card-content">
              <h6>Active Grades</h6>
              <h2>12</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('academic-management/divisions.html') }}">
          <div class="dashboard-card orange">
            <div class="dash-card-icon">
              <i class="fas fa-building-columns"></i>
            </div>
            <div class="dash-card-content">
              <h6>Total Divisions</h6>
              <h2>36</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('academic-management/subject.html') }}">
          <div class="dashboard-card green">
            <div class="dash-card-icon">
              <i class="fas fa-book-open"></i>
            </div>
            <div class="dash-card-content">
              <h6>Total Subjects</h6>
              <h2>18</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('teacher-management/teachers.html') }}">
          <div class="dashboard-card pink">
            <div class="dash-card-icon">
              <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="dash-card-content">
              <h6>Total Teachers</h6>
              <h2>85</h2>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="space-line"></div>
    <div class="col-lg-12">
      <h3 class="title-dash">Timetable Overview</h3>
    </div>

    <div class="row ">
      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('timetable-management/regular-timetable.html') }}">
          <div class="dashboard-card teal">
            <div class="dash-card-icon">
              <i class="fas fa-calendar-check"></i>
            </div>
            <div class="dash-card-content">
              <h6>Published Timetables</h6>
              <h2>24</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('timetable-management/timetable-settings.html') }}">
          <div class="dashboard-card violet">
            <div class="dash-card-icon">
              <i class="fas fa-file-pen"></i>
            </div>
            <div class="dash-card-content">
              <h6>Draft Timetables</h6>
              <h2>8</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="#!">
          <div class="dashboard-card crimson">
            <div class="dash-card-icon">
              <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="dash-card-content">
              <h6>Pending Approvals</h6>
              <h2>5</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('masters/classrooms.html') }}">
          <div class="dashboard-card royal">
            <div class="dash-card-icon">
              <i class="fas fa-school"></i>
            </div>
            <div class="dash-card-content">
              <h6>Today's Classes</h6>
              <h2>42</h2>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="space-line"></div>
    <div class="col-lg-12">
      <h3 class="title-dash">Teacher Availability</h3>
    </div>

    <div class="row last-widgets">
      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('teacher-management/teachers.html') }}">
          <div class="dashboard-card ">
            <div class="dash-card-icon forest">
              <i class="fas fa-user-check"></i>
            </div>
            <div class="dash-card-content">
              <h6>Present Teachers</h6>
              <h2>85</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('teacher-management/teacher-leave.html') }}">
          <div class="dashboard-card ">
            <div class="dash-card-icon coral">
              <i class="fas fa-user-clock"></i>
            </div>
            <div class="dash-card-content">
              <h6>Teachers on Leave</h6>
              <h2>06</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('timetable-management/training-schedule.html') }}">
          <div class="dashboard-card ">
            <div class="dash-card-icon cyan">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="dash-card-content">
              <h6>Teachers in Training</h6>
              <h2>12</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ asset('timetable-management/substitute-allocation.html') }}">
          <div class="dashboard-card ">
            <div class="dash-card-icon gold">
              <i class="fas fa-user-plus"></i>
            </div>
            <div class="dash-card-content">
              <h6>Available Substitute Teachers</h6>
              <h2>18</h2>
            </div>
          </div>
        </a>
      </div>
    </div>
  </section>
@endsection