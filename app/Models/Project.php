<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_code',
    'project_title',
    'description',
    'project_category_id',
    'duration_days',
    'start_date',
    'end_date',
    'venue',
    'timetable_replacement',
    'status',
    'created_by_id',
])]
class Project extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'timetable_replacement' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class)->orderBy('grade');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->orderBy('subject_name');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class)->orderBy('name');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProjectSchedule::class)->orderBy('day_number');
    }
}
