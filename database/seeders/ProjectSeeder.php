<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Seed a sample academic project.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()->active()->first() ?? AcademicYear::query()->orderByDesc('start_date')->first();
        $grade = Grade::query()->where('academic_year_id', $academicYear?->id)->orderBy('grade')->first();
        $projectCategory = ProjectCategory::query()->where('title', 'Academic')->first() ?? ProjectCategory::query()->active()->first();
        $subjects = Subject::query()->where('grade_id', $grade?->id)->limit(2)->pluck('id');
        $teachers = Teacher::query()->where('status', 'active')->limit(2)->pluck('id');
        $createdBy = User::query()->where('email', 'admin@gmail.com')->first() ?? User::query()->first();

        if (! $academicYear || ! $grade || ! $projectCategory || $subjects->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $project = Project::query()->updateOrCreate(
            ['project_code' => app(PrefixCodeService::class)->format('project', 1)],
            [
                'project_title' => 'Grade 1 Science Exploration Project',
                'description' => 'Hands-on project for students to explore science concepts through observation and simple experiments.',
                'project_category_id' => $projectCategory->id,
                'duration_days' => 5,
                'start_date' => now()->addWeek()->toDateString(),
                'end_date' => now()->addWeek()->addDays(4)->toDateString(),
                'venue' => 'Science Lab',
                'timetable_replacement' => true,
                'status' => 'active',
                'created_by_id' => $createdBy?->id,
            ],
        );

        $project->grades()->sync([$grade->id]);
        $project->subjects()->sync($subjects);
        $project->teachers()->sync($teachers);
    }
}
