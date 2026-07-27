@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
  <div class="page-title">
    <h3>User Logs</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">User Logs</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    

    <div class="col-lg-12 mb-3">
      <div class="main-table-container">
        

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
                        <th>Email</th>
                        <th>Action</th>
                        <th>Date & Time</th>
                        <th>IP Address</th>
                    </tr>
                  </thead>
                  <tbody>
                   @forelse($logs as $log)

                    <tr>
                    
                    <td>{{ $loop->iteration }}</td>
                    
                    <td>{{ $log->name }}</td>
                    <td>{{ $log->email }}</td>
    
                    <td>
                        @if($log->action == 'Login')
                            <span class="badge bg-success">Login</span>
                        @else
                            <span class="badge bg-danger">Logout</span>
                        @endif
                    </td>
    
                    <td>{{ \Carbon\Carbon::parse($log->logged_at)
    ->timezone('Asia/Kolkata')
    ->format('d M Y h:i:s A') }}</td>
                    <td>{{ $log->ip_address }}</td>
                    
                    </tr>
                    
                    @empty
                    <tr>
                        <td colspan="10">No Record Found</td>
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

