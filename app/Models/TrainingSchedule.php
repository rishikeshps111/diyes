<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'title',
    'trainer_type_id',
    'trainer_category_id',
    'conducted_by',
    'resource_person_trainer',
    'start_date',
    'end_date',
    'per_day_hours',
    'mode',
    'venue',
    'total_count',
    'applicable',
    'training_objectives',
    'training_description',
    'remarks',
    'status',
    'created_by_id',
])]
class TrainingSchedule extends Model
{
    use HasFactory;

    public const CONDUCTED_BY_OPTIONS = [
        'diyes' => 'Diyes',
        'others' => 'Others',
    ];

    public const MODES = [
        'online' => 'Online',
        'offline' => 'Offline',
    ];

    public const APPLICABLE_OPTIONS = [
        'teachers' => 'Teachers',
        'student' => 'Student',
        'staff' => 'Staff',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'per_day_hours' => 'decimal:2',
            'total_count' => 'integer',
        ];
    }

    public function trainerType(): BelongsTo
    {
        return $this->belongsTo(TrainerType::class);
    }

    public function trainerCategory(): BelongsTo
    {
        return $this->belongsTo(TrainerCategory::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_training_schedule')->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingScheduleSession::class)->orderBy('session_no');
    }

    public function trainerAssignments(): HasMany
    {
        return $this->hasMany(TrainingScheduleTrainer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
