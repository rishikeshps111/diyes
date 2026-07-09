<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectCategoriesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $projectCategories)
    {
    }

    public function collection(): Collection
    {
        return $this->projectCategories;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Status',
        ];
    }

    public function map($projectCategory): array
    {
        return [
            $projectCategory->code,
            $projectCategory->title,
            $projectCategory->is_active ? 'Active' : 'Inactive',
        ];
    }
}
