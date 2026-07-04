<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubjectsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $subjects)
    {
    }

    public function collection(): Collection
    {
        return $this->subjects;
    }

    public function headings(): array
    {
        return [
            'Subject Code',
            'Subject',
            'Grade',
            'Priority',
            'Practical Required',
            'Status',
        ];
    }

    public function map($subject): array
    {
        return [
            $subject->subject_code,
            $subject->subject_name,
            $subject->grade?->grade ?? '-',
            ucfirst($subject->priority),
            $subject->is_praticals ? 'Yes' : 'No',
            $subject->is_active ? 'Active' : 'Inactive',
        ];
    }
}
