<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\EventType;
use App\Models\Grade;
use App\Models\SpecialEvent;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SpecialEventSeeder extends Seeder
{
    /**
     * Seed sample special events.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);
        $academicYearId = AcademicYear::query()->where('is_active', true)->orderByDesc('start_date')->value('id')
            ?: AcademicYear::query()->orderByDesc('start_date')->value('id');

        if (! $academicYearId) {
            return;
        }

        $gradeIds = Grade::query()->orderBy('grade')->limit(2)->pluck('id')->all();
        $staffIds = User::query()->where('is_active', true)->orderBy('id')->limit(1)->pluck('id')->all();
        $teacherIds = Teacher::query()->where('status', 'active')->orderBy('id')->limit(1)->pluck('id')->all();

        $events = [
            [
                'event_code' => $codeService->format('special_event', 1),
                'event_title' => 'Annual Sports Day',
                'event_type_id' => EventType::query()->where('title', 'Sports')->value('id'),
                'event_start_date' => now()->addDays(15)->toDateString(),
                'event_end_date' => now()->addDays(15)->toDateString(),
                'media_coverable' => true,
                'venue' => 'School Ground',
                'organized_by' => 'Sports Department',
                'incharge' => 'Sports Coordinator',
                'contact_no' => '9999999999',
                'participants' => ['students', 'teachers', 'parents'],
                'outside_candidates' => false,
                'objective' => 'Encourage fitness and teamwork.',
                'event_details' => 'Annual sports activities and prize distribution.',
                'status' => 'active',
            ],
            [
                'event_code' => $codeService->format('special_event', 2),
                'event_title' => 'Academic Exhibition',
                'event_type_id' => EventType::query()->where('title', 'Academic')->value('id'),
                'event_start_date' => now()->addDays(30)->toDateString(),
                'event_end_date' => now()->addDays(31)->toDateString(),
                'media_coverable' => true,
                'venue' => 'Auditorium',
                'organized_by' => 'Academic Department',
                'incharge' => 'Academic Supervisor',
                'contact_no' => '9999999998',
                'participants' => ['students', 'staff', 'teachers'],
                'outside_candidates' => true,
                'objective' => 'Showcase student academic projects.',
                'event_details' => 'Two-day academic exhibition with student presentations.',
                'status' => 'draft',
            ],
        ];

        foreach ($events as $index => $event) {
            if (! $event['event_type_id']) {
                continue;
            }

            $startDate = Carbon::parse($event['event_start_date']);
            $endDate = Carbon::parse($event['event_end_date']);

            $specialEvent = SpecialEvent::query()->updateOrCreate(
                ['event_title' => $event['event_title']],
                [
                    ...$event,
                    'academic_year_id' => $academicYearId,
                    'days' => $startDate->diffInDays($endDate) + 1,
                    'created_by_id' => $staffIds[0] ?? null,
                ],
            );

            $specialEvent->grades()->sync($gradeIds);
            $specialEvent->staffCoordinators()->sync($staffIds);
            $specialEvent->teacherCoordinators()->sync($teacherIds);
            $specialEvent->timings()->delete();

            for ($date = $startDate->copy(), $day = 1; $date <= $endDate; $date->addDay(), $day++) {
                $specialEvent->timings()->create([
                    'day_number' => $day,
                    'event_date' => $date->toDateString(),
                    'day_label' => 'Day '.$day.' : '.strtoupper($date->format('D')).' '.$date->format('jS'),
                    'start_time' => $index === 0 ? '08:30' : '09:30',
                    'end_time' => $index === 0 ? '12:30' : '15:30',
                ]);
            }
        }
    }
}
