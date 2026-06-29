@extends('layouts.guest')

@section('title', 'Login')

@section('content')
  <section class="application-login-section">
    <div class="auth-left-cs"></div>

    <div class="auth-right">
      <div class="login-field-box login-box-latest ">
        <img src="{{ asset('assets/img/logo.png') }}" alt="">

        <form class="row g-3 needs-validation lg-form" method="POST" action="{{ route('login') }}">
          @csrf
          @if (session('status'))
            <div class="col-12">
              <div class="alert alert-success mb-0">{{ session('status') }}</div>
            </div>
          @endif

          <div class="col-12">
            <div class="input-group has-validation form-login">
              <input type="email" name="email" class="form-control shadow-none @error('email') is-invalid @enderror"
                id="yourEmail" placeholder="User Email" value="{{ old('email', 'admin@gmail.com') }}" required autofocus>
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please enter your Email.</div>
              @enderror
            </div>
          </div>

          <div class="col-12 form-login">
            <input type="password" name="password" class="form-control shadow-none @error('password') is-invalid @enderror"
              id="yourPassword" placeholder=" Password" value="admin@123" required>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
              <div class="invalid-feedback">Please enter your password!</div>
            @enderror
          </div>

          <div class="col-12 my-4">
            <div class="form-check">
              <input class="form-check-input shadow-none" type="checkbox" name="remember" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn--form btn--form-login border-0">Login</button>
          </div>
        </form>
      </div>
    </div>
  </section>
@endsection
