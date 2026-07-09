<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventTypesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $eventTypes)
    {
    }

    public function collection(): Collection
    {
        return $this->eventTypes;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Status',
        ];
    }

    public function map($eventType): array
    {
        return [
            $eventType->code,
            $eventType->title,
            $eventType->is_active ? 'Active' : 'Inactive',
        ];
    }
}
