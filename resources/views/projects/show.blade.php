@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
  <div class="page-title">
    <h3>Project Details</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Project Management</li>
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
        <li class="breadcrumb-item active">Details</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-lg-10 mb-3">
        <div class="main-table-container mt-3 bg-white">
          <div class="row">
            <div class="col-lg-12 mb-3">
              <div class="v-preview">
                <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name', 'Diyes') }}">
                <span
                  class="{{ in_array($project->status, ['active', 'completed'], true) ? 'status-green' : ($project->status === 'cancelled' ? 'status-red' : '') }}">
                  {{ \App\Models\Project::STATUSES[$project->status] ?? ucfirst($project->status) }}
                </span>
              </div>
            </div>

            <div class="col-lg-12 mb-3">
              <div class="v-preview-widget s-preview-widget">
                <h3>{{ $project->project_title }}</h3>
                <ul>
                  <li>Project Code : <span>{{ $project->project_code }}</span></li>
                  <li>Category : <span>{{ $project->category?->title ?? '-' }}</span></li>
                  <li>Duration : <span>{{ $project->duration_days }} day(s)</span></li>
                  <li>Start Date : <span>{{ $project->start_date?->format('d M Y') ?? '-' }}</span></li>
                  <li>End Date : <span>{{ $project->end_date?->format('d M Y') ?? '-' }}</span></li>
                  <li>Venue : <span>{{ $project->venue ?: '-' }}</span></li>
                  <li>Created By : <span>{{ $project->creator?->name ?? '-' }}</span></li>
                  <li>Created Time : <span>{{ $project->created_at?->format('d M Y h:i A') ?? '-' }}</span></li>
                  <li>Timetable Replacement : <span>{{ $project->timetable_replacement ? 'Yes' : 'No' }}</span></li>
                </ul>
              </div>
            </div>

            <div class="col-lg-12 mb-3">
              <div class="v-preview-widget s-preview-address">
                <h6>Description</h6>
                <p class="mb-0">{{ $project->description ?: '-' }}</p>
              </div>
            </div>

            <div class="col-lg-4 mb-3">
              <div class="v-preview-widget s-preview-address">
                <h6>Classes Applied For</h6>
                <ul>
                  @forelse ($project->grades as $grade)
                    <li><span>{{ $grade->grade }}</span></li>
                  @empty
                    <li><span>-</span></li>
                  @endforelse
                </ul>
              </div>
            </div>

            <div class="col-lg-4 mb-3">
              <div class="v-preview-widget s-preview-address">
                <h6>Applied Subjects</h6>
                <ul>
                  @forelse ($project->subjects as $subject)
                    <li><span>{{ $subject->subject_name }}</span></li>
                  @empty
                    <li><span>-</span></li>
                  @endforelse
                </ul>
              </div>
            </div>

            <div class="col-lg-4 mb-3">
              <div class="v-preview-widget s-preview-address">
                <h6>Allocated Teachers</h6>
                <ul>
                  @forelse ($project->teachers as $teacher)
                    <li><span>{{ $teacher->name }}</span></li>
                  @empty
                    <li><span>-</span></li>
                  @endforelse
                </ul>
              </div>
            </div>

            <div class="col-lg-12 d-flex justify-content-center">
              <div class="btn-flex">
                <a href="{{ route('projects.index') }}" class="btn btn-danger">Back</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
