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
    'project_id',
    'applicable_from',
    'applicable_to',
    'academic_year_id',
    'grade_id',
    'total_periods',
    'description',
    'status',
    'created_by_id',
    'source_timetable_id',
])]
class ProjectWeek extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'publish' => 'Publish',
    ];

    protected function casts(): array
    {
        return [
            'applicable_from' => 'date',
            'applicable_to' => 'date',
            'total_periods' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'division_project_week')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function sourceTimetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class, 'source_timetable_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProjectWeekEntry::class);
    }
}
