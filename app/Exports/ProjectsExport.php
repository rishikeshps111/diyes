<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $projects)
    {
    }

    public function collection(): Collection
    {
        return $this->projects;
    }

    public function headings(): array
    {
        return [
            'Project Code',
            'Project Title',
            'Category',
            'Duration',
            'Classes',
            'Subjects',
            'Allocated Teachers',
            'Venue',
            'Created Date',
            'Timetable Replacement',
            'Status',
        ];
    }

    public function map($project): array
    {
        return [
            $project->project_code,
            $project->project_title,
            $project->category?->title ?? '-',
            $project->duration_days.' day(s)',
            $project->grades->pluck('grade')->implode(', '),
            $project->subjects->pluck('subject_name')->implode(', '),
            $project->teachers->pluck('name')->implode(', '),
            $project->venue ?: '-',
            $project->created_at?->format('d M Y') ?? '-',
            $project->timetable_replacement ? 'Yes' : 'No',
            ucfirst($project->status),
        ];
    }
}
