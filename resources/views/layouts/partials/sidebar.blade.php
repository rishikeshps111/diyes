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
          <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
          </a>
        </li>

        @can('view.department')
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('departments.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarMasters" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-database"></i><span>Masters</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarMasters"
              class="nav-content collapse sub-menu {{ request()->routeIs('departments.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              <li>
                <a href="{{ route('departments.index') }}"
                  class="{{ request()->routeIs('departments.*') ? 'sub-active' : '' }}">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Departments</span>
                </a>
              </li>
            </ul>
          </li>
        @endcan

      </ul>
    </div>
  </div>
</aside>