<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'event_code',
    'event_title',
    'event_type_id',
    'academic_year_id',
    'event_start_date',
    'event_end_date',
    'days',
    'media_coverable',
    'venue',
    'organized_by',
    'incharge',
    'contact_no',
    'participants',
    'outside_candidates',
    'objective',
    'event_details',
    'banner_image',
    'status',
    'created_by_id',
])]
class SpecialEvent extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Active',
        'complete' => 'Complete',
        'inactive' => 'Inactive',
        'postponed' => 'Postponed',
        'cancelled' => 'Cancelled',
    ];

    public const PARTICIPANTS = [
        'staff' => 'Staff',
        'teachers' => 'Teachers',
        'students' => 'Students',
        'non_teaching' => 'Non Teaching',
        'parents' => 'Parents',
        'all' => 'All',
    ];

    protected function casts(): array
    {
        return [
            'event_start_date' => 'date',
            'event_end_date' => 'date',
            'days' => 'integer',
            'media_coverable' => 'boolean',
            'outside_candidates' => 'boolean',
            'participants' => 'array',
        ];
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'grade_special_event')->orderBy('grade');
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'division_special_event')->orderBy('division');
    }

    public function staffCoordinators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'special_event_staff_coordinator');
    }

    public function teacherCoordinators(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'special_event_teacher_coordinator');
    }

    public function timings(): HasMany
    {
        return $this->hasMany(SpecialEventTiming::class)->orderBy('day_number');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SpecialEventAttachment::class);
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(SpecialEventTimetableEntry::class);
    }

    public function bannerUrl(): ?string
    {
        return $this->banner_image ? Storage::url($this->banner_image) : null;
    }
}
