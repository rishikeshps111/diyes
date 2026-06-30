<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DivisionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $divisions)
    {
    }

    public function collection(): Collection
    {
        return $this->divisions;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Grade',
            'Division',
            'Class Teacher',
            'Capacity',
            'Room Number',
            'Status',
        ];
    }

    public function map($division): array
    {
        return [
            $division->code,
            $this->gradeWithYear($division),
            $division->division,
            $division->class_teacher ?? '-',
            $division->capacity,
            $division->room_number ?? '-',
            $division->is_active ? 'Active' : 'Inactive',
        ];
    }

    private function gradeWithYear($division): string
    {
        if (! $division->grade) {
            return '-';
        }

        $academicYear = $division->grade->academicYear?->academic_year;

        return $academicYear
            ? $division->grade->grade.' - '.$academicYear
            : $division->grade->grade;
    }
}
