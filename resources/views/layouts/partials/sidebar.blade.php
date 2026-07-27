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

        @canany(['view.academic-year', 'view.grade', 'view.subject', 'view.division'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('academic-years.*', 'grades.*', 'subjects.*', 'divisions.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarAcademicManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-graduation-cap"></i><span>Academic Management</span><i
                class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarAcademicManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('academic-years.*', 'grades.*', 'subjects.*', 'divisions.*') ? 'show' : '' }}"
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
              @can('view.subject')
                <li>
                  <a href="{{ route('subjects.index') }}"
                    class="{{ request()->routeIs('subjects.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Subjects</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany

        @canany(['view.department', 'view.designation', 'view.classroom', 'view.venue', 'view.holiday', 'view.time-table-category', 'view.project-category', 'view.event-type', 'view.trainer-type', 'view.trainer-category', 'view.module-prefix'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('departments.*', 'designations.*', 'classrooms.*', 'venues.*', 'holidays.*', 'time-table-categories.*', 'project-categories.*', 'event-types.*', 'trainer-types.*', 'trainer-categories.*', 'module-prefixes.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarMasters" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-database"></i><span>Masters</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarMasters"
              class="nav-content collapse sub-menu {{ request()->routeIs('departments.*', 'designations.*', 'classrooms.*', 'venues.*', 'holidays.*', 'time-table-categories.*', 'project-categories.*', 'event-types.*', 'trainer-types.*', 'trainer-categories.*', 'module-prefixes.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.module-prefix')
                <li>
                  <a href="{{ route('module-prefixes.index') }}"
                    class="{{ request()->routeIs('module-prefixes.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Module Prefixes</span>
                  </a>
                </li>
              @endcan
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
                  <a href="{{ route('venues.index') }}" class="{{ request()->routeIs('venues.*') ? 'sub-active' : '' }}">
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
              @can('view.project-category')
                <li>
                  <a href="{{ route('project-categories.index') }}"
                    class="{{ request()->routeIs('project-categories.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Project Categories</span>
                  </a>
                </li>
              @endcan
              @can('view.event-type')
                <li>
                  <a href="{{ route('event-types.index') }}"
                    class="{{ request()->routeIs('event-types.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Event Types</span>
                  </a>
                </li>
              @endcan
              @can('view.trainer-type')
                <li>
                  <a href="{{ route('trainer-types.index') }}"
                    class="{{ request()->routeIs('trainer-types.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Trainer Types</span>
                  </a>
                </li>
              @endcan
              @can('view.trainer-category')
                <li>
                  <a href="{{ route('trainer-categories.index') }}"
                    class="{{ request()->routeIs('trainer-categories.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Trainer Categories</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany

        @canany(['view.timetable', 'view.project-week', 'view.training-schedule', 'view.special-event'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('timetables.*', 'project-weeks.*', 'training-schedules.*', 'special-events.*', 'generate-timetable.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarTimetableManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-calendar-week"></i><span>Timetable Management</span><i
                class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarTimetableManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('timetables.*', 'project-weeks.*', 'training-schedules.*', 'special-events.*', 'generate-timetable.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.timetable')
                <li>
                  <a href="{{ route('timetables.index') }}"
                    class="{{ request()->routeIs('timetables.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Regular Timetable</span>
                  </a>
                </li>
              @endcan
              @can('view.project-week')
                <li>
                  <a href="{{ route('project-weeks.index') }}"
                    class="{{ request()->routeIs('project-weeks.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Project Week</span>
                  </a>
                </li>
              @endcan
              @can('view.training-schedule')
                <li>
                  <a href="{{ route('training-schedules.index') }}"
                    class="{{ request()->routeIs('training-schedules.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Training Schedule</span>
                  </a>
                </li>
              @endcan
              @can('view.special-event')
                <li>
                  <a href="{{ route('special-events.index') }}"
                    class="{{ request()->routeIs('special-events.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Special Events</span>
                  </a>
                </li>
              @endcan
              @can('view.timetable')
                <li>
                  <a href="{{ route('generate-timetable.index') }}"
                    class="{{ request()->routeIs('generate-timetable.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Generate Timetable</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany

        @canany(['view.teacher'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teachers.*', 'teacher-allotments.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarTeacherManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-chalkboard-user"></i><span>Teacher Management</span><i
                class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarTeacherManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('teachers.*', 'teacher-allotments.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.teacher')
                <li>
                  <a href="{{ route('teachers.index') }}"
                    class="{{ request()->routeIs('teachers.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Teachers</span>
                  </a>
                </li>
                <li>
                  <a href="{{ route('teacher-allotments.index') }}" class="{{ request()->routeIs('teacher-allotments.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-calendar-week"></i><span>Teacher Work Load</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany

        @canany(['view.project'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('projects.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarProjectManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-diagram-project"></i><span>Project Management</span><i
                class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarProjectManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('projects.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.project')
                <li>
                  <a href="{{ route('projects.index') }}"
                    class="{{ request()->routeIs('projects.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Projects</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany
        
         <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('leave-types.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarApprovalManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-users-gear"></i><span>Leave Management</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarApprovalManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('leave-types.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
                <li>
                  <a href="{{ route('leave-types.index') }}" class="{{ request()->routeIs('leave-types.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Leave Type</span>
                  </a>
                </li>
                 <li>
                  <a href="{{ route('leave-applications.index') }}" class="{{ request()->routeIs('leave-applications.index.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Manage Leave</span>
                  </a>
                </li>
            </ul>
          </li>

        @canany(['view.user', 'view.role'])
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.*', 'roles.*') ? '' : 'collapsed' }}"
              data-bs-target="#sidebarUserManagement" data-bs-toggle="collapse" href="#">
              <i class="fa-solid fa-users-gear"></i><span>User Management</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="sidebarUserManagement"
              class="nav-content collapse sub-menu {{ request()->routeIs('users.*', 'roles.*') ? 'show' : '' }}"
              data-bs-parent="#sidebar-nav">
              @can('view.user')
                <li>
                  <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Users</span>
                  </a>
                </li>
              @endcan
              @can('view.role')
                <li>
                  <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'sub-active' : '' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Roles & Permissions</span>
                  </a>
                </li>
              @endcan
            </ul>
          </li>
        @endcanany
        
         <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('user-logs.index') ? '' : 'collapsed' }}" href="{{ route('user-logs.index') }}">
            <i class="bi bi-journal-text"></i>
            <span>User Logs</span>
          </a>
        </li>
        
         <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('activity-logs') ? '' : 'collapsed' }}" href="{{ route('activity-logs') }}">
            <i class="bi bi-activity"></i>
            <span>Activity Logs</span>
          </a>
        </li>

      </ul>
    </div>
  </div>
</aside>
