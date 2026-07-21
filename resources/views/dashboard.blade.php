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

    <div class="col-lg-12">
      <h3 class="title-dash">Academic Overview</h3>
    </div>

    <div class="row row-col--5">
      <div class="col-lg-3 col-md-6">
        <a href="{{ route('academic-years.index') }}">
          <div class="dashboard-card purple">
            <div class="dash-card-icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="dash-card-content">
              <h6>Current Academic Year</h6>
              <h2>{{ $currentAcademicYear }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('grades.index') }}">
          <div class="dashboard-card blue">
            <div class="dash-card-icon">
              <i class="fas fa-layer-group"></i>
            </div>
            <div class="dash-card-content">
              <h6>Active Grades</h6>
              <h2>{{ $activeGrades }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('divisions.index') }}">
          <div class="dashboard-card orange">
            <div class="dash-card-icon">
              <i class="fas fa-building-columns"></i>
            </div>
            <div class="dash-card-content">
              <h6>Total Divisions</h6>
              <h2>{{ $totalDivisions }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('subjects.index') }}">
          <div class="dashboard-card green">
            <div class="dash-card-icon">
              <i class="fas fa-book-open"></i>
            </div>
            <div class="dash-card-content">
              <h6>Total Subjects</h6>
              <h2>{{ $totalSubjects }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('teachers.index') }}">
          <div class="dashboard-card pink">
            <div class="dash-card-icon">
              <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="dash-card-content">
              <h6>Total Teachers</h6>
              <h2>{{ $totalTeachers }}</h2>
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
        <a href="{{ route('timetables.index', ['status' => 'published']) }}">
          <div class="dashboard-card teal">
            <div class="dash-card-icon">
              <i class="fas fa-calendar-check"></i>
            </div>
            <div class="dash-card-content">
              <h6>Published Timetables</h6>
              <h2>{{ $publishedTimetables }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('timetables.index', ['status' => 'draft']) }}">
          <div class="dashboard-card violet">
            <div class="dash-card-icon">
              <i class="fas fa-file-pen"></i>
            </div>
            <div class="dash-card-content">
              <h6>Draft Timetables</h6>
              <h2>{{ $draftTimetables }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('teachers.index') }}">
          <div class="dashboard-card crimson">
            <div class="dash-card-icon">
              <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="dash-card-content">
              <h6>Pending Approvals</h6>
              <h2>{{ $pendingApprovals }}</h2>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-md-6">
        <a href="{{ route('generate-timetable.index') }}">
          <div class="dashboard-card royal">
            <div class="dash-card-icon">
              <i class="fas fa-school"></i>
            </div>
            <div class="dash-card-content">
              <h6>Today's Classes</h6>
              <h2>{{ $todaysClasses }}</h2>
            </div>
          </div>
        </a>
      </div>
    </div>

  </section>
@endsection
