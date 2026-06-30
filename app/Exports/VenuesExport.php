<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VenuesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $venues)
    {
    }

    public function collection(): Collection
    {
        return $this->venues;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Venue Name',
            'Venue Type',
            'Building',
            'Capacity',
            'Facilities',
            'Contact Person',
            'Status',
            'Remarks',
        ];
    }

    public function map($venue): array
    {
        return [
            $venue->code,
            $venue->venue_name,
            $venue->venue_type,
            $venue->building,
            $venue->capacity,
            collect($venue->facilities)->filter()->implode(', ') ?: '-',
            $venue->contact_person,
            $venue->is_active ? 'Active' : 'Inactive',
            $venue->remarks ?? '-',
        ];
    }
}
