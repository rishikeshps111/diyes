<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Services\PrefixCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimetableEntrySeeder extends Seeder
{
    /**
     * Seed generated entries for the sample regular timetable.
     */
    public function run(): void
    {
        $timetable = Timetable::query()
            ->where('code', app(PrefixCodeService::class)->format('timetable', 1))
            ->first();

        if (! $timetable) {
            return;
        }

        $subjects = Subject::query()
            ->where('grade_id', $timetable->grade_id)
            ->orderBy('subject_name')
            ->get(['id']);
        $teachers = Teacher::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id']);

        if ($subjects->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $entries = collect(TimetableEntry::DAYS)
            ->flatMap(fn (string $day, int $dayIndex): array => $this->entriesForDay($timetable, $day, $dayIndex, $subjects, $teachers))
            ->all();

        DB::transaction(function () use ($timetable, $entries): void {
            $timetable->forceFill(['status' => 'published'])->save();
            $timetable->entries()->delete();
            $timetable->entries()->createMany($entries);
        });
    }

    private function entriesForDay(Timetable $timetable, string $day, int $dayIndex, $subjects, $teachers): array
    {
        $entries = [];
        $start = Carbon::createFromFormat('H:i', '09:00');

        for ($period = 1; $period <= (int) $timetable->total_periods_per_day; $period++) {
            $end = $start->copy()->addMinutes((int) $timetable->period_duration_minutes);
            $subject = $subjects->get(($dayIndex + $period - 1) % $subjects->count());
            $teacher = $teachers->get(($dayIndex + $period - 1) % $teachers->count());

            $entries[] = [
                'day' => $day,
                'period_no' => $period,
                'entry_type' => 'period',
                'subject_id' => $subject?->id,
                'teacher_1_id' => $teacher?->id,
                'teacher_2_id' => null,
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'duration_minutes' => (int) $timetable->period_duration_minutes,
            ];

            $start = $end->copy();

            if ($period === 2) {
                $entries[] = $this->breakEntry($day, $period, 'short_break', $start, (int) $timetable->short_break_minutes);
                $start->addMinutes((int) $timetable->short_break_minutes);
            }

            if ($period === 4) {
                $entries[] = $this->breakEntry($day, $period, 'lunch_break', $start, (int) $timetable->lunch_break_minutes);
                $start->addMinutes((int) $timetable->lunch_break_minutes);
            }

            if ($period === 6) {
                $entries[] = $this->breakEntry($day, $period, 'short_break', $start, (int) $timetable->short_break_after_lunch_minutes);
                $start->addMinutes((int) $timetable->short_break_after_lunch_minutes);
            }
        }

        return $entries;
    }

    private function breakEntry(string $day, int $period, string $type, Carbon $start, int $duration): array
    {
        return [
            'day' => $day,
            'period_no' => $period,
            'entry_type' => $type,
            'subject_id' => null,
            'teacher_1_id' => null,
            'teacher_2_id' => null,
            'start_time' => $start->format('H:i'),
            'end_time' => $start->copy()->addMinutes($duration)->format('H:i'),
            'duration_minutes' => $duration,
        ];
    }
}
