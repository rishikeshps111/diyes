@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
  <div class="page-title">
    <h3>Activity Logs</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Activity Logs</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    

    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        <div class="row">
          <div class="col-lg-12">
            {{-- <form method="GET">

                <div class="row mb-4">

                    <div class="col-md-2">
                        <input type="text"
                               name="user"
                               value="{{ request('user') }}"
                               class="form-control"
                               placeholder="User">
                    </div>

                    <div class="col-md-2">
                        <input type="text"
                               name="module"
                               value="{{ request('module') }}"
                               class="form-control"
                               placeholder="Module">
                    </div>

                    <div class="col-md-2">

                        <select
                            name="action"
                            class="form-control">

                            <option value="">All Actions</option>

                            <option value="Create">Create</option>

                            <option value="Update">Update</option>

                            <option value="Delete">Delete</option>

                            <option value="Approve">Approve</option>

                            <option value="Reject">Reject</option>

                            <option value="Login">Login</option>

                            <option value="Logout">Logout</option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <input
                            type="date"
                            name="from_date"
                            value="{{ request('from_date') }}"
                            class="form-control">

                    </div>

                    <div class="col-md-2">

                        <input
                            type="date"
                            name="to_date"
                            value="{{ request('to_date') }}"
                            class="form-control">

                    </div>

                    <div class="col-md-2">

                        <button
                            class="btn btn-primary">

                            Search

                        </button>

                        <a
                            href="{{ route('activity-logs') }}"
                            class="btn btn-secondary">

                            Reset

                        </a>

                    </div>

                </div>

            </form> --}}
        </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="mt-3 table-container">
              <div class="row justify-content-end">
                <div class="col-lg-5">
                  <div class="entry-select">
                    <p>Showing</p>
                    <select id="teacherPerPage" class="form-select shadow-none">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                    </select>
                    <p>Entries</p>
                  </div>
                </div>
                <div class="col-lg-7">
                  <!--<div class="table-search">-->
                  <!--  <label for="teacherTableSearch" class="nowrap">Search</label>-->
                  <!--  <input type="text" id="teacherTableSearch" class="form-control shadow-none"-->
                  <!--    placeholder="Name, employee code, email or phone">-->
                  <!--  <form id="teacherExportForm" method="POST" class="d-inline-flex flex-shrink-0">-->
                  <!--    @csrf-->
                  <!--    <button type="button" class="exp-btn" data-loading-text="Exporting..."-->
                  <!--      data-export-url="{{ route('teachers.export.excel') }}">Export Excel</button>-->
                  <!--    <button type="button" class="exp-btn" data-loading-text="Exporting..."-->
                  <!--      data-export-url="{{ route('teachers.export.pdf') }}">Export PDF</button>-->
                  <!--  </form>-->
                  <!--</div>-->
                </div>
              </div>

              <div class="table-over">
                <table id="leaveTypeTable" class="align-middle mb-0 table table-custom mt-3 w-100">
                  <thead>
                    <tr>
                      <th>#</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Record ID</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                   @forelse($logs as $log)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $log->user_name }}</td>

                        <td>{{ $log->module }}</td>

                        <td>

                            @switch($log->action)

                                @case('Create')
                                    <span class="badge bg-success">Create</span>
                                    @break

                                @case('Update')
                                    <span class="badge bg-primary">Update</span>
                                    @break

                                @case('Delete')
                                    <span class="badge bg-danger">Delete</span>
                                    @break

                                @case('Approve')
                                    <span class="badge bg-info">Approve</span>
                                    @break

                                @case('Reject')
                                    <span class="badge bg-warning text-dark">Reject</span>
                                    @break

                                @default
                                    <span class="badge bg-secondary">
                                        {{ $log->action }}
                                    </span>

                            @endswitch

                        </td>

                        <td>{{ $log->record_id }}</td>

                        <td>{{ $log->description }}</td>

                        <td>{{ $log->created_at->format('d M Y h:i A') }}</td>

                        <td>

                            <a
                                href="{{ route('activity-logs.show',$log->id) }}"
                                class="btn btn-info btn-sm">

                                View

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8" class="text-center">

                            No Activity Logs Found

                        </td>

                    </tr>

                @endforelse
                  </tbody>
                </table>
                {{ $logs->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection

