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

        @canany(['view.academic-year', 'view.grade', 'view.division'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('academic-years.*', 'grades.*', 'divisions.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarAcademicManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-graduation-cap"></i><span>Academic Management</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarAcademicManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('academic-years.*', 'grades.*', 'divisions.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.academic-year')
                <li>
                  <a href="{{ route('academic-years.index') }}"
                    class="{{ request()->routeIs('academic-years.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Academic Year</span>
                  </a>
                </li>
              @endcan
              @can('view.grade')
                <li>
                  <a href="{{ route('grades.index') }}" class="{{ request()->routeIs('grades.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Grades</span>
                  </a>
                </li>
              @endcan
              @can('view.division')
                <li>
                  <a href="{{ route('divisions.index') }}"
                    class="{{ request()->routeIs('divisions.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Divisions</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany

        @canany(['view.department', 'view.designation', 'view.classroom', 'view.venue', 'view.holiday'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('departments.*', 'designations.*', 'classrooms.*', 'venues.*', 'holidays.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarMasters" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-database"></i><span>Masters</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarMasters"
              class="nav-content collapse sub-menu {{ request()->routeIs('departments.*', 'designations.*', 'classrooms.*', 'venues.*', 'holidays.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.department')
                <li>
                  <a href="{{ route('departments.index') }}"
                    class="{{ request()->routeIs('departments.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Departments</span>
                  </a>
                </li>
              @endcan
              @can('view.designation')
                <li>
                  <a href="{{ route('designations.index') }}"
                    class="{{ request()->routeIs('designations.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Designations</span>
                  </a>
                </li>
              @endcan
              @can('view.classroom')
                <li>
                  <a href="{{ route('classrooms.index') }}"
                    class="{{ request()->routeIs('classrooms.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Classrooms</span>
                  </a>
                </li>
              @endcan
              @can('view.venue')
                <li>
                  <a href="{{ route('venues.index') }}"
                    class="{{ request()->routeIs('venues.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Venues</span>
                  </a>
                </li>
              @endcan
              @can('view.holiday')
                <li>
                  <a href="{{ route('holidays.index') }}"
                    class="{{ request()->routeIs('holidays.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Holidays</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany

      </ul>
    </div>
  </div>
</aside>
