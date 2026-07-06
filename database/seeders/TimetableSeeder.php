<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Timetable;
use App\Models\TimeTableCategory;
use App\Models\User;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    /**
     * Seed a sample regular timetable.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()->active()->first() ?? AcademicYear::query()->orderByDesc('start_date')->first();
        $grade = Grade::query()->where('academic_year_id', $academicYear?->id)->orderBy('grade')->first();
        $divisions = Division::query()->where('grade_id', $grade?->id)->limit(2)->pluck('id');
        $timetableCategory = TimeTableCategory::query()->where('title', 'Regular')->first();
        $incharge = User::query()->role('Academic Supervisor')->where('is_active', true)->first();
        $preparedBy = User::query()->where('email', 'admin@gmail.com')->first() ?? $incharge;

        if (! $academicYear || ! $grade || $divisions->isEmpty() || ! $timetableCategory || ! $incharge || ! $preparedBy) {
            return;
        }

        $timetable = Timetable::query()->updateOrCreate(
            ['code' => app(PrefixCodeService::class)->format('timetable', 1)],
            [
                'timetable_name' => 'Grade 1 Regular Timetable',
                'timetable_category_id' => $timetableCategory->id,
                'applicable_from' => now()->toDateString(),
                'applicable_to' => now()->addMonths(6)->toDateString(),
                'academic_year_id' => $academicYear->id,
                'grade_id' => $grade->id,
                'total_periods_per_day' => 8,
                'period_duration_minutes' => 40,
                'short_break_minutes' => 10,
                'lunch_break_minutes' => 45,
                'timetable_incharge_id' => $incharge->id,
                'description' => 'Sample regular timetable.',
                'prepared_by_id' => $preparedBy->id,
                'prepared_at' => now(),
                'status' => 'draft',
            ],
        );

        $timetable->divisions()->sync($divisions);
    }
}
