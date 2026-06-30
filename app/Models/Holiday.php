<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code',
    'holiday_name',
    'holiday_type',
    'academic_year_id',
    'holiday_date',
    'start_date',
    'end_date',
    'applicable_branch',
    'applicable_classes',
    'is_active',
    'description',
])]
class Holiday extends Model
{
    use HasFactory;

    public const HOLIDAY_TYPES = [
        'National',
        'Festival',
        'School Event',
        'Local Holiday',
        'Exam Break',
        'Vacation',
        'Other',
    ];

    public const APPLICABLE_CLASSES = [
        'All Classes',
        'Primary',
        'Middle School',
        'High School',
        'Higher Secondary',
        'Selected Classes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
