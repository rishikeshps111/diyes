@extends('layouts.app')

@section('title', 'Teacher Documents')

@section('content')
  <div class="page-title">
    <h3>Teacher Documents</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.show', $teacher) }}">{{ $teacher->name }}</a></li>
        <li class="breadcrumb-item active">Documents</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <div class="row g-3 align-items-center">
        <div class="col-lg-2">
          <img src="{{ $teacher->imageUrl() ?: asset('assets/img/profile-img.jpg') }}" alt="{{ $teacher->name }}"
            class="teacher-detail-image">
        </div>
        <div class="col-lg-10">
          <h4 class="mb-1">{{ $teacher->name }}</h4>
          <p class="mb-1">{{ $teacher->employee_id }}</p>
          <span>{{ $teacher->department?->department_name ?? '-' }} /
            {{ $teacher->designation?->designation_name ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="main-table-container">
      <div class="row">
        <div class="col-lg-12">
          <div class="btn-flex">
            <a href="{{ route('teachers.index') }}" class="btn btn-danger">Back</a>
            @can('edit.teacher')
              <button type="button" id="addDocumentBtn" class="add-btn">Add Document</button>
            @endcan
          </div>
        </div>
      </div>

      <div class="table-over mt-3">
        <table id="teacherDocumentsTable" class="align-middle mb-0 table table-custom w-100">
          <thead>
            <tr>
              <th>SL No</th>
              <th>Document Type</th>
              <th>Verified Status</th>
              <th>Verified By</th>
              <th>Verified At</th>
              <th>Action</th>
              <th class="d-none">Created At</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </section>

  @can('edit.teacher')
    <div class="modal fade" id="teacherDocumentFormModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form id="teacherDocumentForm" class="modal-content" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="teacherDocumentFormTitle">Add Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="o-f-inp mb-3">
              <label for="document_type">Document Type <span class="text-danger">*</span></label>
              <select name="document_type" id="document_type" class="form-select shadow-none">
                <option value="">--- Select ---</option>
                @foreach ($documentTypes as $documentType)
                  <option value="{{ $documentType }}">{{ $documentType }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback" data-error-for="document_type"></div>
            </div>
            <div class="o-f-inp mb-3">
              <label for="document_file">Choose File <span class="text-danger">*</span></label>
              <input type="file" name="document_file" id="document_file" class="form-control shadow-none"
                accept=".pdf,.jpg,.jpeg,.png,.webp">
              <div class="invalid-feedback" data-error-for="document_file"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" id="teacherDocumentSubmitBtn" class="btn btn-success"
              data-loading-text="Saving...">Save</button>
          </div>
        </form>
      </div>
    </div>
  @endcan

  <div class="modal fade" id="documentViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="documentViewerTitle">Document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="documentViewerMeta" class="row g-3 mb-3"></div>
          <div id="documentViewerContent"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @include('teachers.documents.partials.js')
@endpush