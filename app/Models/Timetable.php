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
    'timetable_name',
    'timetable_category_id',
    'applicable_from',
    'applicable_to',
    'academic_year_id',
    'grade_id',
    'total_periods_per_day',
    'period_duration_minutes',
    'short_break_minutes',
    'lunch_break_minutes',
    'timetable_incharge_id',
    'description',
    'prepared_by_id',
    'prepared_at',
    'status',
])]
class Timetable extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    protected function casts(): array
    {
        return [
            'applicable_from' => 'date',
            'applicable_to' => 'date',
            'total_periods_per_day' => 'integer',
            'period_duration_minutes' => 'integer',
            'short_break_minutes' => 'integer',
            'lunch_break_minutes' => 'integer',
            'prepared_at' => 'datetime',
        ];
    }

    public function timetableCategory(): BelongsTo
    {
        return $this->belongsTo(TimeTableCategory::class);
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
        return $this->belongsToMany(Division::class, 'timetable_division')->withTimestamps();
    }

    public function incharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'timetable_incharge_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
