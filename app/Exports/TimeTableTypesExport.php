<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TimeTableTypesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $timeTableTypes)
    {
    }

    public function collection(): Collection
    {
        return $this->timeTableTypes;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Status',
        ];
    }

    public function map($timeTableType): array
    {
        return [
            $timeTableType->code,
            $timeTableType->title,
            $timeTableType->is_active ? 'Active' : 'Inactive',
        ];
    }
}
