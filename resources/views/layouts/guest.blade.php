<!DOCTYPE html>
<html lang="en">

<head>
  @include('layouts.partials.head')
</head>

<body class="@yield('body_class')">
  @yield('content')

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  @include('layouts.partials.scripts')
</body>

</html>
