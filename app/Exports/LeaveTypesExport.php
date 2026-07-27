<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveTypesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $leaveTypes) {}

    public function collection(): Collection
    {
        return $this->leaveTypes;
    }

    public function headings(): array
    {
        return ['Code', 'Name', 'Type', 'Max / Year', 'Carry Forward', 'Carry Limit', 'Applicable For', 'Gender', 'Max / Request', 'Advance Notice', 'Half Day', 'Approval', 'Encashment', 'Status', 'Description'];
    }

    public function map($leaveType): array
    {
        return [
            $leaveType->code,
            $leaveType->leave_name,
            ucfirst((string) $leaveType->leave_type),
            $leaveType->max_leaves_per_year,
            $leaveType->carry_forward_allowed ? 'Yes' : 'No',
            $leaveType->max_carry_forward_limit ?? '-',
            $leaveType->applicable_for_text,
            ucfirst($leaveType->gender_specific),
            $leaveType->max_leave_days_per_request,
            $leaveType->advance_notice_days,
            $leaveType->allow_half_day ? 'Yes' : 'No',
            $leaveType->requires_approval ? 'Yes' : 'No',
            $leaveType->encashment_allowed ? 'Yes' : 'No',
            $leaveType->status ? 'Active' : 'Inactive',
            $leaveType->description,
        ];
    }
}
