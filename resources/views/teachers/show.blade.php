@extends('layouts.app')

@section('title', 'Teacher Details')

@section('content')
  <div class="page-title">
    <h3>Teacher Details</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Teacher Management</li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
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
                <span @if(!$teacher->is_verified) style="background-color: #ecc413;" @endif>
                  <i class="fa-solid {{ $teacher->is_verified ? 'fa-circle-check' : 'fa-clock' }}"></i>
                  {{ $teacher->is_verified ? 'Verified' : 'Pending' }}
                </span>
              </div>
            </div>

            <div class="col-lg-3 mb-3">
              <div class="student-preview-profile">
                <img src="{{ $teacher->imageUrl() ?: asset('assets/img/user.png') }}" alt="{{ $teacher->name }}">
              </div>
            </div>

            <div class="col-lg-9 mb-3">
              <div class="v-preview-widget s-preview-widget">
                <h3>{{ $teacher->name }}</h3>
                <ul>
                  <li>DOB : <span>{{ $teacher->date_of_birth?->format('d-m-Y') ?? '-' }}</span></li>
                  <li>Email : <span>{{ $teacher->email }}</span></li>
                  <li>Phone No : <span>{{ trim($teacher->phone_country_code . ' ' . $teacher->phone) }}</span></li>
                  <li>Alternate Phone :
                    <span>{{ $teacher->alternative_phone ? trim($teacher->alternative_phone_country_code . ' ' . $teacher->alternative_phone) : '-' }}</span>
                  </li>
                  <li>Qualification : <span>{{ $teacher->qualification }}</span></li>
                  <li>Experience : <span>{{ $teacher->experience }} Years</span></li>
                  <li>Department : <span>{{ $teacher->department?->department_name ?? '-' }}</span></li>
                  <li>Status : <span
                      class="{{ $teacher->status === 'active' ? 'status-green' : 'status-red' }}">{{ ucfirst($teacher->status) }}</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="col-lg-6 mb-3">
              <div class="v-preview-widget s-preview-address">
                <h6>Address</h6>
                <ul>
                  <li><label>Address :</label> <span>{{ $teacher->address }}</span></li>
                  <li><label>Country :</label> <span>{{ $teacher->country?->name ?? '-' }}</span></li>
                  <li><label>State :</label> <span>{{ $teacher->state?->name ?? '-' }}</span></li>
                  <li><label>District :</label> <span>{{ $teacher->district?->name ?? '-' }}</span></li>
                  <li><label>Pincode :</label> <span>{{ $teacher->pincode }}</span></li>
                </ul>
              </div>
            </div>

            <div class="col-lg-6 mb-3">
              <div class="v-preview-widget s-preview-address">
                <h6>Professional Details</h6>
                <ul>
                  <li><label>Employee ID :</label> <span>{{ $teacher->employee_id }}</span></li>
                  <li><label>Designation :</label> <span>{{ $teacher->designation?->designation_name ?? '-' }}</span></li>
                  <li><label>Subject :</label> <span>{{ $teacher->subject }}</span></li>
                  <li><label>Joining Date :</label> <span>{{ $teacher->date_of_joining?->format('d-m-Y') ?? '-' }}</span>
                  </li>
                  <li><label>Class In Charge :</label> <span>{{ $teacher->classInCharge?->grade ?? '-' }}</span></li>
                </ul>
              </div>
            </div>

            <div class="col-lg-12 mb-3">
              <div class="v-preview-widget flex-ul">
                <h6>Employment Details</h6>
                <ul>
                  <li><label>Employment Type :</label> <span>{{ ucfirst($teacher->employment_type) }}</span></li>
                  <li><label>Joining Date :</label> <span>{{ $teacher->date_of_joining?->format('d-m-Y') ?? '-' }}</span>
                  </li>
                  <li><label>Salary :</label> <span>Rs. {{ number_format((float) $teacher->salary, 2) }}</span></li>
                </ul>
              </div>
            </div>

            <div class="col-lg-12 mb-3">
              <div class="v-document-view">
                <h6>Documents</h6>

                <div class="row">
                  @forelse ($teacher->documents as $document)
                    <div class="col-lg-4 mb-3">
                      <div class="v-doc-preview s-doc-preview">
                        <p>{{ $document->document_type }}</p>
                        <img src="{{ asset('assets/img/fle.png') }}" alt="">
                        <a href="#!" class="mb-3 teacher-detail-document-view"
                          data-view-url="{{ route('teachers.documents.show', [$teacher, $document]) }}">View</a>
                        <small>
                          @if ($document->verified_at)
                            Verified at {{ $document->verified_at->format('d-m-Y') }}
                          @else
                            {{ $document->verification_status }}
                          @endif
                        </small>
                      </div>
                    </div>
                  @empty
                    <div class="col-lg-12">
                      <p class="mb-0">No documents uploaded.</p>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>

            <div class="col-lg-12 d-flex justify-content-center">
              <a href="{{ route('teachers.index') }}" class="btn btn-danger">Back</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const documentViewerModal = new bootstrap.Modal(document.getElementById('documentViewerModal'));

      document.querySelectorAll('.teacher-detail-document-view').forEach(function (button) {
        button.addEventListener('click', function (event) {
          event.preventDefault();

          fetch(button.dataset.viewUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (response) {
              if (!response.ok) {
                throw new Error('Unable to load document');
              }

              return response.json();
            })
            .then(function (data) {
              const url = data.document_file_url;
              const lowerUrl = url.toLowerCase();

              document.getElementById('documentViewerTitle').textContent = data.document_type;
              document.getElementById('documentViewerMeta').innerHTML =
                '<div class="col-lg-4"><strong>Document Type</strong><div>' + escapeHtml(data.document_type) + '</div></div>' +
                '<div class="col-lg-4"><strong>Verification Status</strong><div>' + escapeHtml(data.verification_status) + '</div></div>' +
                '<div class="col-lg-4"><strong>File Name</strong><div>' + escapeHtml(data.file_name) + '</div></div>';
              document.getElementById('documentViewerContent').innerHTML = lowerUrl.endsWith('.pdf')
                ? '<iframe src="' + url + '" class="w-100 teacher-document-frame"></iframe>'
                : '<img src="' + url + '" alt="' + escapeHtml(data.document_type) + '" class="img-fluid d-block mx-auto">';

              documentViewerModal.show();
            })
            .catch(function () {
              Swal.fire('Error', 'Unable to open document.', 'error');
            });
        });
      });

      function escapeHtml(value) {
        return String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }
    });
  </script>
@endpush
