@extends('layouts.app')

@section('title', 'User Details')

@section('content')
  <div class="page-title">
    <h3>User Details</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">User Management</li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
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
                <span @if(!$user->is_active) style="background-color: #dc3545;" @endif>
                  <i class="fa-solid {{ $user->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                  {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>

            <div class="col-lg-3 mb-3">
              <div class="student-preview-profile">
                <img src="{{ $user->profileImageUrl() ?: asset('assets/img/user.png') }}" alt="{{ $user->name }}">
              </div>
            </div>

            <div class="col-lg-9 mb-3">
              <div class="v-preview-widget s-preview-widget">
                <h3>{{ $user->name }}</h3>
                <ul>
                  <li>Employee ID : <span>{{ $user->employee_code }}</span></li>
                  <li>Username : <span>{{ $user->username }}</span></li>
                  <li>Email : <span>{{ $user->email }}</span></li>
                  <li>Phone No : <span>{{ trim($user->phone_country_code . ' ' . $user->phone) }}</span></li>
                  <li>Role : <span>{{ $user->role?->name ? ucfirst($user->role->name) : '-' }}</span></li>
                  <li>Department : <span>{{ $user->department?->department_name ?? '-' }}</span></li>
                  <li>Designation : <span>{{ $user->designation?->designation_name ?? '-' }}</span></li>
                  <li>Last Login : <span>{{ $user->last_login_at?->format('d M Y h:i A') ?? '-' }}</span></li>
                </ul>
              </div>
            </div>

            <div class="col-lg-12 mb-3">
              <div class="v-preview-widget flex-ul">
                <h6>Security & Remarks</h6>
                <ul>
                  <li><label>Status :</label> <span
                      class="{{ $user->is_active ? 'status-green' : 'status-red' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                  </li>
                  <li><label>Two-Factor Authentication :</label>
                    <span>{{ $user->is_two_factor_enabled ? 'Enabled' : 'Disabled' }}</span>
                  </li>
                  <li><label>Remarks :</label> <span>{{ $user->remarks ?: '-' }}</span></li>
                </ul>
              </div>
            </div>

            <div class="col-lg-12 d-flex justify-content-center">
              <a href="{{ route('users.index') }}" class="btn btn-danger">Back</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
