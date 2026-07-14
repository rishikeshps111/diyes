<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TrainerCategory;
use App\Models\TrainerType;
use App\Models\TrainingSchedule;
use App\Models\User;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\DistrictSeeder;
use Database\Seeders\GradeSeeder;
use Database\Seeders\ModulePrefixSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\TrainerCategorySeeder;
use Database\Seeders\TrainerTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TrainingScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_manage_a_training_schedule_with_subjects_and_sessions(): void
    {
        $user = User::factory()->create();
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            Permission::findOrCreate($action.'.training-schedule', 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user);

        $trainerType = TrainerType::query()->create(['code' => 'TRT001', 'title' => 'Academic', 'is_active' => true]);
        $trainerCategory = TrainerCategory::query()->create(['code' => 'TRC001', 'title' => 'Academic', 'is_active' => true]);
        $academicYear = AcademicYear::query()->create([
            'code' => 'AY001', 'academic_year' => '2026-27', 'start_date' => '2026-06-01',
            'end_date' => '2027-05-31', 'is_active' => true,
        ]);
        $grade = Grade::query()->create([
            'code' => 'GR001', 'grade' => 'Grade 1', 'capacity' => 30,
            'academic_year_id' => $academicYear->id, 'is_active' => true,
        ]);
        $subject = Subject::query()->create([
            'subject_code' => 'SUB001', 'subject_name' => 'English', 'grade_id' => $grade->id,
            'color' => '#ffffff', 'is_active' => true, 'priority' => 'medium', 'is_praticals' => false,
        ]);

        $payload = [
            'title' => 'Teaching Excellence',
            'trainer_type_id' => $trainerType->id,
            'trainer_category_id' => $trainerCategory->id,
            'conducted_by' => 'diyes',
            'resource_person_trainer' => 'Training Team',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-11',
            'per_day_hours' => 3,
            'mode' => 'offline',
            'venue' => 'Training Hall',
            'total_count' => 25,
            'applicable' => 'teachers',
            'subject_ids' => [$subject->id],
            'training_objectives' => 'Improve classroom delivery.',
            'training_description' => 'Practical training for teachers.',
            'remarks' => 'Bring a lesson plan.',
            'status' => 'draft',
            'sessions' => [
                [
                    'session_date' => '2026-08-10',
                    'time_from' => '09:00',
                    'time_to' => '12:00',
                    'topic_module' => 'Active Learning',
                    'duration_hours' => 3,
                ],
            ],
        ];

        $this->post(route('training-schedules.store'), $payload)
            ->assertRedirect(route('training-schedules.index'));

        $trainingSchedule = TrainingSchedule::query()->with(['subjects', 'sessions'])->sole();
        $this->assertStringStartsWith('TRS', $trainingSchedule->code);
        $this->assertEquals([$subject->id], $trainingSchedule->subjects->pluck('id')->all());
        $this->assertCount(1, $trainingSchedule->sessions);

        $this->get(route('training-schedules.show', $trainingSchedule))->assertOk();

        $payload['title'] = 'Teaching Excellence Updated';
        $payload['applicable'] = 'staff';
        unset($payload['subject_ids']);
        $payload['status'] = 'published';
        $payload['sessions'][0]['topic_module'] = 'Assessment Skills';

        $this->put(route('training-schedules.update', $trainingSchedule), $payload)
            ->assertRedirect(route('training-schedules.index'));

        $trainingSchedule->refresh()->load(['subjects', 'sessions']);
        $this->assertSame('published', $trainingSchedule->status);
        $this->assertTrue($trainingSchedule->subjects->isEmpty());
        $this->assertSame('Assessment Skills', $trainingSchedule->sessions->first()->topic_module);

        $this->deleteJson(route('training-schedules.destroy', $trainingSchedule))->assertOk();
        $this->assertDatabaseMissing('training_schedules', ['id' => $trainingSchedule->id]);
        $this->assertDatabaseCount('training_schedule_sessions', 0);
    }

    public function test_training_schedule_routes_require_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('training-schedules.index'))->assertForbidden();
        $this->get(route('training-schedules.create'))->assertForbidden();
    }

    public function test_training_schedule_trainer_assignments_support_modal_crud_permissions(): void
    {
        $this->seed([
            ModulePrefixSeeder::class,
            RolePermissionSeeder::class,
            AcademicYearSeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            DistrictSeeder::class,
            TeacherSeeder::class,
            TrainerTypeSeeder::class,
            TrainerCategorySeeder::class,
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::query()->where('name', 'like', '%.training-schedule')->get());
        $this->actingAs($user);

        $trainingSchedule = TrainingSchedule::query()->create([
            'code' => 'TRS2026-270001',
            'title' => 'Trainer Assignment Test',
            'trainer_type_id' => TrainerType::query()->firstOrFail()->id,
            'trainer_category_id' => TrainerCategory::query()->firstOrFail()->id,
            'conducted_by' => 'diyes',
            'resource_person_trainer' => 'Training Team',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-11',
            'per_day_hours' => 3,
            'mode' => 'offline',
            'venue' => 'Training Hall',
            'total_count' => 25,
            'applicable' => 'teachers',
            'training_objectives' => 'Test objectives.',
            'training_description' => 'Test description.',
            'status' => 'draft',
        ]);
        $teacher = Teacher::query()->where('status', 'active')->firstOrFail();
        $subjects = Subject::query()->limit(2)->get();

        $this->get(route('training-schedules.trainers.index', $trainingSchedule))->assertOk();

        $this->postJson(route('training-schedules.trainers.store', $trainingSchedule), [
            'designation_id' => $teacher->designation_id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects->first()->id,
        ])->assertOk();

        $assignment = $trainingSchedule->trainerAssignments()->sole();

        $this->getJson(route('training-schedules.trainers.show', [$trainingSchedule, $assignment]))
            ->assertOk()
            ->assertJsonPath('teacher_id', $teacher->id);

        $this->putJson(route('training-schedules.trainers.update', [$trainingSchedule, $assignment]), [
            'designation_id' => $teacher->designation_id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subjects->last()->id,
        ])->assertOk();

        $this->assertSame($subjects->last()->id, $assignment->refresh()->subject_id);

        $this->deleteJson(route('training-schedules.trainers.destroy', [$trainingSchedule, $assignment]))
            ->assertOk();
        $this->assertDatabaseCount('training_schedule_trainers', 0);
    }
}
