<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassroomsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $classrooms)
    {
    }

    public function collection(): Collection
    {
        return $this->classrooms;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Room Name',
            'Building',
            'Floor',
            'Room Type',
            'Seating Capacity',
            'Department',
            'Equipment',
            'Status',
            'Remarks',
        ];
    }

    public function map($classroom): array
    {
        return [
            $classroom->code,
            $classroom->room_name,
            $classroom->building,
            $classroom->floor,
            $classroom->room_type,
            $classroom->seating_capacity,
            $classroom->department?->department_name ?? '-',
            collect($classroom->equipment)->filter()->implode(', ') ?: '-',
            $classroom->is_active ? 'Active' : 'Inactive',
            $classroom->remarks ?? '-',
        ];
    }
}
