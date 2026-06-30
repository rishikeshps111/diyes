<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DesignationsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $designations)
    {
    }

    public function collection(): Collection
    {
        return $this->designations;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Designation',
            'Department',
            'Grade',
            'Status',
            'Description',
        ];
    }

    public function map($designation): array
    {
        return [
            $designation->code,
            $designation->designation_name,
            $designation->department?->department_name ?? '-',
            $this->gradeWithYear($designation),
            $designation->is_active ? 'Active' : 'Inactive',
            $designation->description ?? '-',
        ];
    }

    private function gradeWithYear($designation): string
    {
        if (! $designation->grade) {
            return '-';
        }

        $academicYear = $designation->grade->academicYear?->academic_year;

        return $academicYear
            ? $designation->grade->grade.' - '.$academicYear
            : $designation->grade->grade;
    }
}
