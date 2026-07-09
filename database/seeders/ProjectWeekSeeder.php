<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Project;
use App\Models\ProjectWeek;
use App\Models\Timetable;
use App\Models\User;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class ProjectWeekSeeder extends Seeder
{
    /**
     * Seed a sample project week.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()->active()->first() ?? AcademicYear::query()->orderByDesc('start_date')->first();
        $grade = Grade::query()->where('academic_year_id', $academicYear?->id)->orderBy('grade')->first();
        $division = Division::query()->where('grade_id', $grade?->id)->orderBy('division')->first();
        $project = Project::query()->where('project_code', app(PrefixCodeService::class)->format('project', 1))->first()
            ?? Project::query()->orderBy('project_title')->first();
        $sourceTimetable = Timetable::query()->where('grade_id', $grade?->id)->latest('id')->first();
        $createdBy = User::query()->where('email', 'admin@gmail.com')->first() ?? User::query()->first();

        if (! $academicYear || ! $grade || ! $division || ! $project) {
            return;
        }

        $projectWeek = ProjectWeek::query()->updateOrCreate(
            ['code' => app(PrefixCodeService::class)->format('project_week', 1)],
            [
                'project_id' => $project->id,
                'applicable_from' => now()->addWeek()->toDateString(),
                'applicable_to' => now()->addWeek()->addDays(4)->toDateString(),
                'academic_year_id' => $academicYear->id,
                'grade_id' => $grade->id,
                'total_periods' => 5,
                'description' => 'Sample project week generated for Grade 1 Division A.',
                'status' => 'draft',
                'created_by_id' => $createdBy?->id,
                'source_timetable_id' => $sourceTimetable?->id,
            ],
        );

        $projectWeek->divisions()->sync([$division->id]);
    }
}
