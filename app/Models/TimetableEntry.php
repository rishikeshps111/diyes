<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'timetable_id',
    'day',
    'period_no',
    'entry_type',
    'subject_id',
    'teacher_1_id',
    'teacher_2_id',
    'start_time',
    'end_time',
    'duration_minutes',
])]
class TimetableEntry extends Model
{
    use HasFactory;

    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public const TYPES = [
        'period' => 'Period',
        'short_break' => 'Short Break',
        'lunch_break' => 'Lunch Break',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
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
