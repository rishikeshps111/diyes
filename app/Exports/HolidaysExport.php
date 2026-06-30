<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HolidaysExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $holidays)
    {
    }

    public function collection(): Collection
    {
        return $this->holidays;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Holiday',
            'Holiday Type',
            'Academic Year',
            'Holiday Date',
            'Start Date',
            'End Date',
            'Branch',
            'Applicable Classes',
            'Status',
            'Description',
        ];
    }

    public function map($holiday): array
    {
        return [
            $holiday->code,
            $holiday->holiday_name,
            $holiday->holiday_type,
            $holiday->academicYear?->academic_year ?? '-',
            $holiday->holiday_date?->format('d M Y') ?? '-',
            $holiday->start_date?->format('d M Y') ?? '-',
            $holiday->end_date?->format('d M Y') ?? '-',
            $holiday->applicable_branch ?? '-',
            $holiday->applicable_classes ?? '-',
            $holiday->is_active ? 'Active' : 'Inactive',
            $holiday->description ?? '-',
        ];
    }
}
