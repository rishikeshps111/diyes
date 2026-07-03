<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeachersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $teachers)
    {
    }

    public function collection(): Collection
    {
        return $this->teachers;
    }

    public function headings(): array
    {
        return [
            'Employee Code',
            'Name',
            'Department',
            'Designation',
            'Email',
            'Phone',
            'Date of Joining',
            'Status',
            'Verification Status',
        ];
    }

    public function map($teacher): array
    {
        return [
            $teacher->employee_id,
            $teacher->name,
            $teacher->department?->department_name ?? '-',
            $teacher->designation?->designation_name ?? '-',
            $teacher->email,
            trim($teacher->phone_country_code.' '.$teacher->phone),
            $teacher->date_of_joining?->format('d M Y') ?? '-',
            ucfirst($teacher->status),
            $teacher->is_verified ? 'Verified' : 'Pending',
        ];
    }
}
