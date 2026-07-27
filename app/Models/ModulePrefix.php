<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'module',
    'prefix',
])]
class ModulePrefix extends Model
{
    public const MODULES = [
        'academic_year' => 'Academic Year',
        'grade' => 'Grade',
        'subject' => 'Subject',
        'division' => 'Division',
        'department' => 'Department',
        'designation' => 'Designation',
        'classroom' => 'Classroom',
        'venue' => 'Venue',
        'holiday' => 'Holiday',
        'leave_type' => 'Leave Type',
        'leave_application' => 'Leave Application',
        'time_table_category' => 'Time Table Category',
        'project_category' => 'Project Category',
        'event_type' => 'Event Type',
        'trainer_type' => 'Trainer Type',
        'trainer_category' => 'Trainer Category',
        'project' => 'Project',
        'project_week' => 'Project Week',
        'training_schedule' => 'Training Schedule',
        'special_event' => 'Special Event',
        'timetable' => 'Timetable',
        'teacher' => 'Teacher',
        'user' => 'User',
    ];

    public function getModuleNameAttribute(): string
    {
        return self::MODULES[$this->module] ?? str($this->module)->headline()->toString();
    }
}
