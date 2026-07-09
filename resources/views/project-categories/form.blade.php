@extends('layouts.app')

@section('title', $projectCategory->exists ? 'Edit Project Category' : 'Add Project Category')

@section('content')
  <div class="page-title">
    <h3>Project Categories</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('project-categories.index') }}">Project Categories</a></li>
        <li class="breadcrumb-item active">{{ $projectCategory->exists ? 'Edit' : 'Add' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="ProjectCategoryForm"
          action="{{ $projectCategory->exists ? route('project-categories.update', $projectCategory) : route('project-categories.store') }}">
          @csrf
          @if ($projectCategory->exists)
            @method('PUT')
          @endif

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="code">Code</label>
                <input type="text" id="code" class="form-control shadow-none" value="{{ $projectCategory->code }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title"
                  class="form-control shadow-none @error('title') is-invalid @enderror"
                  value="{{ old('title', $projectCategory->title) }}">
                @error('title')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active"
                  class="form-select shadow-none @error('is_active') is-invalid @enderror">
                  <option value="">--- Select ---</option>
                  <option value="1" @selected((string) old('is_active', (int) $projectCategory->is_active) === '1')>Active</option>
                  <option value="0" @selected((string) old('is_active', (int) $projectCategory->is_active) === '0')>Inactive</option>
                </select>
                @error('is_active')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('project-categories.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="ProjectCategorySubmitBtn" class="submit-btn"
                data-loading-text="{{ $projectCategory->exists ? 'Updating...' : 'Submitting...' }}">
                {{ $projectCategory->exists ? 'Update' : 'Submit' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const ProjectCategoryForm = document.getElementById('ProjectCategoryForm');
      const submitButton = document.getElementById('ProjectCategorySubmitBtn');

      if (!ProjectCategoryForm || !submitButton) {
        return;
      }

      ProjectCategoryForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
