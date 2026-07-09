<?php

namespace Database\Seeders;

use App\Models\ProjectWeek;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectWeekEntrySeeder extends Seeder
{
    /**
     * Seed generated project week timetable entries.
     */
    public function run(): void
    {
        $projectWeek = ProjectWeek::query()
            ->where('code', app(PrefixCodeService::class)->format('project_week', 1))
            ->with('divisions')
            ->first();

        if (! $projectWeek) {
            return;
        }

        $timetable = Timetable::query()
            ->where('academic_year_id', $projectWeek->academic_year_id)
            ->where('grade_id', $projectWeek->grade_id)
            ->where('status', 'published')
            ->where('applicable_from', '<=', $projectWeek->applicable_from)
            ->where('applicable_to', '>=', $projectWeek->applicable_to)
            ->whereHas('entries')
            ->when($projectWeek->divisions->isNotEmpty(), function ($query) use ($projectWeek): void {
                foreach ($projectWeek->divisions->pluck('id') as $divisionId) {
                    $query->whereHas('divisions', fn ($divisionQuery) => $divisionQuery->whereKey($divisionId));
                }
            })
            ->latest('id')
            ->first();

        $teachers = Teacher::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id']);

        if (! $timetable || $teachers->isEmpty()) {
            return;
        }

        $sourceEntries = $timetable->entries()
            ->where('entry_type', 'period')
            ->orderBy('period_no')
            ->get()
            ->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)
            ->values();

        if ($sourceEntries->isEmpty()) {
            return;
        }

        $entries = $sourceEntries
            ->groupBy('day')
            ->flatMap(function ($dayEntries, string $day) use ($projectWeek, $teachers): array {
                return $dayEntries
                    ->take((int) $projectWeek->total_periods)
                    ->values()
                    ->map(function (TimetableEntry $entry, int $index) use ($teachers): array {
                        $teacher = $teachers->get($index % $teachers->count());

                        return [
                            'timetable_entry_id' => $entry->id,
                            'day' => $entry->day,
                            'period_no' => $entry->period_no,
                            'teacher_1_id' => $teacher?->id,
                            'teacher_2_id' => null,
                        ];
                    })
                    ->all();
            })
            ->values()
            ->all();

        DB::transaction(function () use ($projectWeek, $timetable, $entries): void {
            $projectWeek->forceFill(['source_timetable_id' => $timetable->id])->save();
            $projectWeek->entries()->delete();
            $projectWeek->entries()->createMany($entries);
        });
    }
}
