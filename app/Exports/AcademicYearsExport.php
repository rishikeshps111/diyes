<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AcademicYearsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $academicYears)
    {
    }

    public function collection(): Collection
    {
        return $this->academicYears;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Academic Year',
            'Start Date',
            'End Date',
            'Status',
            'Description',
        ];
    }

    public function map($academicYear): array
    {
        return [
            $academicYear->code,
            $academicYear->academic_year,
            $academicYear->start_date?->format('d M Y'),
            $academicYear->end_date?->format('d M Y'),
            $academicYear->is_active ? 'Active' : 'Inactive',
            $academicYear->description,
        ];
    }
}
