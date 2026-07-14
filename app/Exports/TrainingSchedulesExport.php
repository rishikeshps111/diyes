<?php

namespace App\Exports;

use App\Models\TrainingSchedule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TrainingSchedulesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $trainingSchedules) {}

    public function collection(): Collection
    {
        return $this->trainingSchedules;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Category',
            'Type',
            'Conducted By',
            'Resource Person / Trainer',
            'Start Date',
            'End Date',
            'Per Day Hours',
            'Mode',
            'Venue',
            'Total Count',
            'Applicable',
            'Teaching Staff Subjects',
            'Training Objectives',
            'Training Description',
            'Remarks',
            'Status',
        ];
    }

    public function map($trainingSchedule): array
    {
        return [
            $trainingSchedule->code,
            $trainingSchedule->title,
            $trainingSchedule->trainerCategory?->title ?? '-',
            $trainingSchedule->trainerType?->title ?? '-',
            TrainingSchedule::CONDUCTED_BY_OPTIONS[$trainingSchedule->conducted_by] ?? ucfirst($trainingSchedule->conducted_by),
            $trainingSchedule->resource_person_trainer,
            $trainingSchedule->start_date?->format('d M Y') ?? '-',
            $trainingSchedule->end_date?->format('d M Y') ?? '-',
            $trainingSchedule->per_day_hours,
            TrainingSchedule::MODES[$trainingSchedule->mode] ?? ucfirst($trainingSchedule->mode),
            $trainingSchedule->venue ?? '-',
            $trainingSchedule->total_count,
            TrainingSchedule::APPLICABLE_OPTIONS[$trainingSchedule->applicable] ?? ucfirst($trainingSchedule->applicable),
            $trainingSchedule->subjects->pluck('subject_name')->implode(', ') ?: '-',
            $trainingSchedule->training_objectives ?? '-',
            $trainingSchedule->training_description ?? '-',
            $trainingSchedule->remarks ?? '-',
            TrainingSchedule::STATUSES[$trainingSchedule->status] ?? ucfirst($trainingSchedule->status),
        ];
    }
}
