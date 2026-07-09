<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectWeeksExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $projectWeeks)
    {
    }

    public function collection(): Collection
    {
        return $this->projectWeeks;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Project',
            'Applicable From',
            'Applicable To',
            'Academic Year',
            'Grade',
            'Division',
            'Total Periods',
            'Status',
            'Description',
        ];
    }

    public function map($projectWeek): array
    {
        return [
            $projectWeek->code,
            $projectWeek->project?->project_title ?? '-',
            $projectWeek->applicable_from?->format('d M Y') ?? '-',
            $projectWeek->applicable_to?->format('d M Y') ?? '-',
            $projectWeek->academicYear?->academic_year ?? '-',
            $projectWeek->grade?->grade ?? '-',
            $projectWeek->divisions->pluck('division')->implode(', ') ?: '-',
            $projectWeek->total_periods,
            ucfirst($projectWeek->status),
            $projectWeek->description ?? '-',
        ];
    }
}
