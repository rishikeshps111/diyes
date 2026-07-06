<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TimeTableCategoriesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $timeTableCategories)
    {
    }

    public function collection(): Collection
    {
        return $this->timeTableCategories;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Status',
        ];
    }

    public function map($timeTableCategory): array
    {
        return [
            $timeTableCategory->code,
            $timeTableCategory->title,
            $timeTableCategory->is_active ? 'Active' : 'Inactive',
        ];
    }
}
