<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_week_id',
    'timetable_entry_id',
    'day',
    'period_no',
    'teacher_1_id',
    'teacher_2_id',
])]
class ProjectWeekEntry extends Model
{
    use HasFactory;

    public function projectWeek(): BelongsTo
    {
        return $this->belongsTo(ProjectWeek::class);
    }

    public function timetableEntry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class);
    }

    public function teacherOne(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_1_id');
    }

    public function teacherTwo(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_2_id');
    }
}
