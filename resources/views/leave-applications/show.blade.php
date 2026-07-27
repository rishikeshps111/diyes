@extends('layouts.app')

@section('title', 'Leave Application Details')

@php
    $statusClass = match ($leave->status) {
        'Approved' => 'status-green',
        'Rejected' => 'status-red',
        default => 'status-orange',
    };
    $statusIcon = match ($leave->status) {
        'Approved' => 'fa-circle-check',
        'Rejected' => 'fa-circle-xmark',
        default => 'fa-clock',
    };
@endphp

@push('styles')
    <style>
        .leave-hero {
            background: linear-gradient(135deg, #f0fdf4, #f8fafc);
            border: 1px solid #dce9df;
            border-radius: 14px;
            padding: 24px;
        }

        .leave-hero-icon {
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            color: #09650d;
            display: flex;
            font-size: 24px;
            height: 54px;
            justify-content: center;
            width: 54px;
        }

        .leave-detail-card {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 12px;
            height: 100%;
            padding: 18px;
        }

        .leave-detail-label {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .leave-detail-value {
            color: #1e293b;
            font-size: 15px;
            font-weight: 700;
        }

        .leave-section-title {
            color: #09650d;
            font-size: 16px;
            font-weight: 700;
            margin: 24px 0 12px;
        }

        .leave-note {
            background: #f8fafc;
            border-left: 4px solid #09650d;
            border-radius: 8px;
            color: #334155;
            min-height: 90px;
            padding: 16px;
        }

        .leave-timeline {
            border-left: 2px solid #dbe5df;
            margin-left: 10px;
            padding-left: 24px;
        }

        .leave-timeline-item {
            margin-bottom: 18px;
            position: relative;
        }

        .leave-timeline-item::before {
            background: #09650d;
            border: 3px solid #e9f7eb;
            border-radius: 50%;
            content: '';
            height: 14px;
            left: -32px;
            position: absolute;
            top: 3px;
            width: 14px;
        }
    </style>
@endpush

@section('content')
    <div class="page-title">
        <h3>Leave Application Details</h3>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Leave Management</li>
                <li class="breadcrumb-item"><a href="{{ ($teacherView ?? false) ? route('teacher.leave.index') : route('leave-applications.index') }}">{{ ($teacherView ?? false) ? 'My Applications' : 'Manage Leave Application' }}</a>
                </li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="main-table-container">
            <div class="leave-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="leave-hero-icon"><i class="fa-solid fa-file-circle-check"></i></div>
                    <div>
                        <small class="text-muted">Application Number</small>
                        <h3 class="mb-1">{{ $leave->application_no }}</h3>
                        <span class="{{ $statusClass }}"><i
                                class="fa-solid {{ $statusIcon }} me-1"></i>{{ $leave->status }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if(($teacherView ?? false) && $leave->status === 'Pending')
                        <a href="{{ route('teacher.leave.edit', $leave) }}" class="btn btn-primary"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</a>
                    @endif
                    <a href="{{ ($teacherView ?? false) ? route('teacher.leave.index') : route('leave-applications.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>

            <h5 class="leave-section-title">Applicant Information</h5>
            <div class="row g-3">
                @foreach ([
            'Applicant' => $leave->applicant_name,
            'User Type' => $leave->applicant_type === 'user' ? $leave->role?->name ?? 'User' : 'Teacher',
            'Applied By' => $leave->appliedBy?->name,
            'Applied Date' => $leave->applied_date?->format('d M Y'),
        ] as $label => $value)
                    <div class="col-lg-3 col-md-6">
                        <div class="leave-detail-card"><span class="leave-detail-label">{{ $label }}</span><span
                                class="leave-detail-value">{{ $value ?: '-' }}</span></div>
                    </div>
                @endforeach
            </div>

            <h5 class="leave-section-title">Leave Information</h5>
            <div class="row g-3">
                @foreach ([
            'Leave Type' => $leave->leaveType?->leave_name,
            'From Date' => $leave->from_date?->format('d M Y'),
            'To Date' => $leave->to_date?->format('d M Y'),
            'Duration' => $leave->days . ' day(s)' . ($leave->is_half_day ? ' · Half Day' : ''),
        ] as $label => $value)
                    <div class="col-lg-3 col-md-6">
                        <div class="leave-detail-card"><span class="leave-detail-label">{{ $label }}</span><span
                                class="leave-detail-value">{{ $value ?: '-' }}</span></div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <h5 class="leave-section-title">Reason</h5>
                    <div class="leave-note">{{ $leave->reason }}</div>
                </div>
                <div class="col-lg-6">
                    <h5 class="leave-section-title">Processing Remark</h5>
                    <div class="leave-note">{{ $leave->remarks ?: 'No processing remark available.' }}</div>
                </div>
            </div>

            <h5 class="leave-section-title">Application Activity</h5>
            <div class="leave-timeline">
                <div class="leave-timeline-item"><strong>Application submitted</strong><small
                        class="d-block text-muted">{{ $leave->created_at?->format('d M Y h:i A') }}</small></div>
                @if ($leave->isProcessed())
                    <div class="leave-timeline-item"><strong>Application {{ strtolower($leave->status) }}</strong><small
                            class="d-block text-muted">By {{ $leave->approver?->name ?? '-' }} on
                            {{ $leave->approved_at?->format('d M Y h:i A') ?? '-' }}</small></div>
                @else
                    <div class="leave-timeline-item"><strong>Awaiting approval</strong><small class="d-block text-muted">The
                            application is pending review.</small></div>
                @endif
            </div>
        </div>
    </section>
@endsection
