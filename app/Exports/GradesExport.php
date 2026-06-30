<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GradesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $grades)
    {
    }

    public function collection(): Collection
    {
        return $this->grades;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Grade',
            'Capacity',
            'Academic Year',
            'Status',
        ];
    }

    public function map($grade): array
    {
        return [
            $grade->code,
            $grade->grade,
            $grade->capacity,
            $grade->academicYear?->academic_year ?? '-',
            $grade->is_active ? 'Active' : 'Inactive',
        ];
    }
}
