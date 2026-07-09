<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TimetablesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $timetables)
    {
    }

    public function collection(): Collection
    {
        return $this->timetables;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Timetable Name',
            'Timetable Category',
            'Academic Year',
            'Grade',
            'Division',
            'Total Periods',
            'Application From',
            'Application To',
            'Period Duration',
            'Short Break',
            'Lunch Break',
            'Short Break After Lunch',
            'Time Table Incharge',
            'Prepared By',
            'Prepared Date',
            'Status',
            'Description',
        ];
    }

    public function map($timetable): array
    {
        return [
            $timetable->code,
            $timetable->timetable_name,
            $timetable->timetableCategory?->title ?? '-',
            $timetable->academicYear?->academic_year ?? '-',
            $timetable->grade?->grade ?? '-',
            $timetable->divisions->pluck('division')->implode(', ') ?: '-',
            $timetable->total_periods_per_day,
            $timetable->applicable_from?->format('d M Y') ?? '-',
            $timetable->applicable_to?->format('d M Y') ?? '-',
            $timetable->period_duration_minutes,
            $timetable->short_break_minutes,
            $timetable->lunch_break_minutes,
            $timetable->short_break_after_lunch_minutes,
            $timetable->incharge?->name ?? '-',
            $timetable->preparedBy?->name ?? '-',
            $timetable->prepared_at?->format('d M Y h:i A') ?? '-',
            ucfirst($timetable->status),
            $timetable->description ?? '-',
        ];
    }
}
