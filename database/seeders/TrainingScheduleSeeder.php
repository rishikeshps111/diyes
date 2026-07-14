<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TrainerCategory;
use App\Models\TrainerType;
use App\Models\TrainingSchedule;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingScheduleSeeder extends Seeder
{
    /**
     * Seed a sample published training schedule.
     */
    public function run(): void
    {
        $trainerType = TrainerType::query()->where('title', 'Academic')->first();
        $trainerCategory = TrainerCategory::query()->where('title', 'Academic')->first();

        if (! $trainerType || ! $trainerCategory) {
            return;
        }

        $startDate = now()->addWeek()->startOfWeek();
        $endDate = $startDate->copy()->addDay();

        DB::transaction(function () use ($trainerType, $trainerCategory, $startDate, $endDate): void {
            $trainingSchedule = TrainingSchedule::query()->updateOrCreate(
                ['title' => 'Effective Teaching Strategies'],
                [
                    'code' => app(PrefixCodeService::class)->format('training_schedule', 1),
                    'trainer_type_id' => $trainerType->id,
                    'trainer_category_id' => $trainerCategory->id,
                    'conducted_by' => 'diyes',
                    'resource_person_trainer' => 'Academic Training Team',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'per_day_hours' => 3,
                    'mode' => 'offline',
                    'venue' => 'Training Hall',
                    'total_count' => 30,
                    'applicable' => 'teachers',
                    'training_objectives' => 'Strengthen lesson planning, classroom engagement, and learner assessment practices.',
                    'training_description' => 'A practical two-day program covering effective teaching strategies and classroom application.',
                    'remarks' => 'Participants should bring a sample lesson plan.',
                    'status' => 'published',
                ],
            );

            $trainingSchedule->subjects()->sync(Subject::query()->active()->orderBy('id')->limit(3)->pluck('id'));
            $trainingSchedule->sessions()->delete();
            $trainingSchedule->sessions()->createMany([
                [
                    'session_no' => 1,
                    'session_date' => $startDate->toDateString(),
                    'time_from' => '09:00',
                    'time_to' => '12:00',
                    'topic_module' => 'Student-Centred Teaching Methods',
                    'duration_hours' => 3,
                ],
                [
                    'session_no' => 2,
                    'session_date' => $endDate->toDateString(),
                    'time_from' => '09:00',
                    'time_to' => '12:00',
                    'topic_module' => 'Assessment and Constructive Feedback',
                    'duration_hours' => 3,
                ],
            ]);
        });
    }
}
