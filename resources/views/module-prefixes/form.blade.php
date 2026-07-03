@extends('layouts.app')

@section('title', 'Edit Module Prefix')

@section('content')
  <div class="page-title">
    <h3>Module Prefixes</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Masters</li>
        <li class="breadcrumb-item"><a href="{{ route('module-prefixes.index') }}">Module Prefixes</a></li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard ">
    <div class="row">
      <div class="col-xl-12 mb-3">
        <form method="POST" id="modulePrefixForm" action="{{ route('module-prefixes.update', $modulePrefix) }}">
          @csrf
          @method('PUT')

          <div class="main-table-container mb-3">
            <div class="row">
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="module">Module</label>
                <input type="text" id="module" class="form-control shadow-none"
                  value="{{ $modulePrefix->module_name }}" disabled>
              </div>
              <div class="col-lg-4 o-f-inp mb-3">
                <label for="prefix">Prefix <span class="text-danger">*</span></label>
                <input type="text" name="prefix" id="prefix"
                  class="form-control shadow-none @error('prefix') is-invalid @enderror"
                  value="{{ old('prefix', $modulePrefix->prefix) }}">
                @error('prefix')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-lg-12 d-flex justify-content-center align-items-center">
            <div class="btn-flex">
              <a href="{{ route('module-prefixes.index') }}" class="btn btn-danger">Cancel</a>
              <button type="submit" id="modulePrefixSubmitBtn" class="submit-btn" data-loading-text="Updating...">
                Update
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
      const form = document.getElementById('modulePrefixForm');
      const submitButton = document.getElementById('modulePrefixSubmitBtn');

      if (!form || !submitButton) {
        return;
      }

      form.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
          submitButton.dataset.loadingText;
      });
    });
  </script>
@endpush
