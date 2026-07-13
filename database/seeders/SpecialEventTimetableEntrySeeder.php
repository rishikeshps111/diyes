<?php

namespace Database\Seeders;

use App\Models\ProjectWeek;
use App\Models\SpecialEvent;
use App\Models\TimetableEntry;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialEventTimetableEntrySeeder extends Seeder
{
    /**
     * Seed a generated special event timetable from the Project Week timetable.
     */
    public function run(): void
    {
        $projectWeek = ProjectWeek::query()
            ->where('code', app(PrefixCodeService::class)->format('project_week', 1))
            ->with([
                'project',
                'divisions',
                'sourceTimetable.entries.subject',
                'sourceTimetable.entries.teacherOne',
                'sourceTimetable.entries.teacherTwo',
                'entries.teacherOne',
                'entries.teacherTwo',
            ])
            ->first();

        $specialEvent = SpecialEvent::query()
            ->where('event_code', app(PrefixCodeService::class)->format('special_event', 1))
            ->first();

        if (! $projectWeek?->sourceTimetable || ! $specialEvent || $projectWeek->divisions->isEmpty()) {
            return;
        }

        $division = $projectWeek->divisions->first();
        $projectEntries = $projectWeek->entries->keyBy('timetable_entry_id');
        $sourceEntries = $projectWeek->sourceTimetable->entries
            ->whereIn('entry_type', ['period', 'short_break', 'lunch_break'])
            ->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)
            ->values();

        if ($sourceEntries->isEmpty()) {
            return;
        }

        $rows = $sourceEntries->map(function (TimetableEntry $entry) use ($specialEvent, $projectWeek, $projectEntries, $division): array {
            $projectEntry = $projectEntries->get($entry->id);
            $isEventPeriod = $entry->entry_type === 'period' && in_array((int) $entry->period_no, [1, 2], true);
            $teachers = $projectEntry
                ? collect([$projectEntry->teacherOne?->name, $projectEntry->teacherTwo?->name])->filter()->values()->all()
                : collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter()->values()->all();

            return [
                'grade_id' => $projectWeek->grade_id,
                'division_id' => $division->id,
                'project_week_id' => $projectWeek->id,
                'timetable_entry_id' => $entry->id,
                'day' => $entry->day,
                'period_no' => $entry->period_no,
                'entry_type' => $entry->entry_type,
                'subject_name' => $isEventPeriod
                    ? $specialEvent->event_title
                    : ($projectEntry ? ($projectWeek->project?->project_title ?? 'Project Period') : $entry->subject?->subject_name),
                'teacher_names' => $teachers,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
                'duration_minutes' => $entry->duration_minutes,
                'is_event_period' => $isEventPeriod,
            ];
        });

        DB::transaction(function () use ($specialEvent, $projectWeek, $division, $rows): void {
            $projectWeek->forceFill(['status' => 'publish'])->save();
            $specialEvent->forceFill([
                'academic_year_id' => $projectWeek->academic_year_id,
                'event_start_date' => $projectWeek->applicable_from,
                'event_end_date' => $projectWeek->applicable_to,
                'days' => $projectWeek->applicable_from->diffInDays($projectWeek->applicable_to) + 1,
            ])->save();
            $specialEvent->grades()->syncWithoutDetaching([$projectWeek->grade_id]);
            $specialEvent->divisions()->syncWithoutDetaching([$division->id]);
            $specialEvent->timetableEntries()->delete();
            $specialEvent->timetableEntries()->createMany($rows->all());
        });
    }
}
