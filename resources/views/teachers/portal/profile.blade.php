@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
  <div class="page-title">
    <h3>My Profile</h3>
  </div>
  <section class="section">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom-0 pt-3 px-4">
        <ul class="nav nav-tabs" id="teacherProfileTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $errors->any() ? '' : 'active' }}" id="details-tab" data-bs-toggle="tab"
              data-bs-target="#details-pane" type="button" role="tab" aria-controls="details-pane"
              aria-selected="{{ $errors->any() ? 'false' : 'true' }}">
              <i class="fa-solid fa-user me-1"></i> Basic Details
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $errors->any() ? 'active' : '' }}" id="password-tab" data-bs-toggle="tab"
              data-bs-target="#password-pane" type="button" role="tab" aria-controls="password-pane"
              aria-selected="{{ $errors->any() ? 'true' : 'false' }}">
              <i class="fa-solid fa-lock me-1"></i> Change Password
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body p-4">
        <div class="tab-content" id="teacherProfileTabContent">
          <div class="tab-pane fade {{ $errors->any() ? '' : 'show active' }}" id="details-pane" role="tabpanel"
            aria-labelledby="details-tab" tabindex="0">
            <div class="d-flex align-items-center gap-3 mb-4"><img
                src="{{ $teacher->imageUrl() ?: asset('assets/img/profile-img.jpg') }}"
                class="rounded-circle object-fit-cover" width="90" height="90" alt="{{ $teacher->name }}">
              <div>
                <h4 class="mb-1">{{ $teacher->name }}</h4><span
                  class="badge bg-success">{{ ucfirst($teacher->status) }}</span>
                <div class="text-muted mt-1">{{ $teacher->employee_id }}</div>
              </div>
            </div>
            <div class="row g-3">
              @foreach(['Email' => $teacher->email, 'Phone' => $teacher->phone, 'Department' => $teacher->department?->department_name, 'Qualification' => $teacher->qualification, 'Date of Joining' => $teacher->date_of_joining?->format('d M Y'), 'Employment Type' => ucfirst($teacher->employment_type)] as $label => $value)
                <div class="col-md-6"><small
                    class="text-muted d-block">{{ $label }}</small><strong>{{ $value ?: '—' }}</strong></div>
              @endforeach
            </div>
          </div>
          <div class="tab-pane fade {{ $errors->any() ? 'show active' : '' }}" id="password-pane" role="tabpanel"
            aria-labelledby="password-tab" tabindex="0">
            <div class="row">
              <div class="col-lg-6">
                <h5 class="mb-3">Change Password</h5>
                <form method="POST" action="{{ route('teacher.profile.password') }}" id="passwordForm">@csrf
                  @method('PUT')
                  <div class="mb-3"><label class="form-label">Current Password</label><input type="password"
                      name="current_password" class="form-control @error('current_password') is-invalid @enderror"
                      required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                  <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password"
                      class="form-control @error('password') is-invalid @enderror" required>@error('password')<div
                      class="invalid-feedback">{{ $message }}</div>@enderror</div>
                  <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password"
                      name="password_confirmation" class="form-control" required></div>
                  <button class="submit-btn" id="passwordSubmit" data-loading-text="Updating...">Update Password</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
@push('scripts')
  <script>document.getElementById('passwordForm').addEventListener('submit', () => { const b = document.getElementById('passwordSubmit'); b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + b.dataset.loadingText; });</script>
@endpush