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
        'time_table_category' => 'Time Table Category',
        'timetable' => 'Timetable',
        'teacher' => 'Teacher',
        'user' => 'User',
    ];

    public function getModuleNameAttribute(): string
    {
        return self::MODULES[$this->module] ?? str($this->module)->headline()->toString();
    }
}
