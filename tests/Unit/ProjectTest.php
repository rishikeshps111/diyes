<?php

namespace Tests\Unit;

use App\Models\Project;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    public function test_schedule_day_limit_includes_both_project_dates(): void
    {
        $project = new Project([
            'duration_days' => 2,
            'start_date' => '2026-07-15',
            'end_date' => '2026-07-17',
        ]);

        $this->assertSame(3, $project->scheduleDayLimit());
    }

    public function test_schedule_day_limit_falls_back_to_duration_without_dates(): void
    {
        $project = new Project(['duration_days' => 2]);

        $this->assertSame(2, $project->scheduleDayLimit());
    }
}
