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
            'Status',
            'Description',
        ];
    }

    public function map($designation): array
    {
        return [
            $designation->code,
            $designation->designation_name,
            $designation->is_active ? 'Active' : 'Inactive',
            $designation->description ?? '-',
        ];
    }

}
