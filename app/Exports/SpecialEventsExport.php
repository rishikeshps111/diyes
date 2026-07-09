<?php

namespace App\Exports;

use App\Models\SpecialEvent;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SpecialEventsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $specialEvents)
    {
    }

    public function collection(): Collection
    {
        return $this->specialEvents;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Event Type',
            'Start Date',
            'End Date',
            'Coordinators',
            'Applicable Classes',
            'Status',
        ];
    }

    public function map($specialEvent): array
    {
        return [
            $specialEvent->event_code,
            $specialEvent->event_title,
            $specialEvent->eventType?->title ?? '-',
            $specialEvent->event_start_date?->format('d M Y') ?? '-',
            $specialEvent->event_end_date?->format('d M Y') ?? '-',
            $this->coordinators($specialEvent),
            $specialEvent->grades->pluck('grade')->implode(', ') ?: '-',
            SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status),
        ];
    }

    private function coordinators($specialEvent): string
    {
        return collect()
            ->merge($specialEvent->staffCoordinators->pluck('name'))
            ->merge($specialEvent->teacherCoordinators->pluck('name'))
            ->filter()
            ->implode(', ') ?: '-';
    }
}
