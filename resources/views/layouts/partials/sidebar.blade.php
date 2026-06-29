<aside id="sidebar" class="sidebar">
  <div class="sidebar-blur">
    <div class="sidebar-cont">
      <div class="app-logo">
        <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
          <img src="{{ asset('assets/img/logo.png') }}" alt="">
        </a>
      </div>

      <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
          <a class="nav-link " href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
          </a>
        </li>


      </ul>
    </div>
  </div>
</aside>