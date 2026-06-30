<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepartmentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $departments)
    {
    }

    public function collection(): Collection
    {
        return $this->departments;
    }

    public function headings(): array
    {
        return [
            'Department Code',
            'Department Name',
            'Department Head',
            'Teachers',
            'Display Order',
            'Status',
        ];
    }

    public function map($department): array
    {
        return [
            $department->department_code,
            $department->department_name,
            $department->department_head ?? '-',
            $department->teacher_count,
            $department->display_order,
            $department->is_active ? 'Active' : 'Inactive',
        ];
    }
}
