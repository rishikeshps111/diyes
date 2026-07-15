<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'training_schedule_id',
    'training_schedule_trainer_id',
    'teacher_id',
    'subject_id',
    'grade_id',
    'division_id',
    'period_no',
    'timetable_entry_id',
    'substitute_teacher_id',
    'allocation_date',
])]
class SubstituteAllocation extends Model
{
    protected function casts(): array
    {
        return ['allocation_date' => 'date'];
    }

    public function trainingSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class);
    }

    public function trainerAssignment(): BelongsTo
    {
        return $this->belongsTo(TrainingScheduleTrainer::class, 'training_schedule_trainer_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function timetableEntry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class);
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'substitute_teacher_id');
    }
}
