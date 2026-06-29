<!DOCTYPE html>
<html lang="en">

<head>
  @include('layouts.partials.head')
  <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">
</head>

<body class="@yield('body_class', 'body-img-background')">
  @include('layouts.partials.header')
  @include('layouts.partials.sidebar')

  <main id="main" class="main">
    @yield('content')
    @include('layouts.partials.footer')
  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  @include('layouts.partials.scripts')
  <script src="{{ asset('assets/js/calendar.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js"></script>
  <script src="https://kit.fontawesome.com/111740f521.js" crossorigin="anonymous"></script>
</body>

</html>
