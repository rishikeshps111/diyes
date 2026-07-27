<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'employee_id',
    'teacher_image',
    'name',
    'gender',
    'date_of_birth',
    'phone_country_code',
    'phone',
    'alternative_phone_country_code',
    'alternative_phone',
    'email',
    'qualification',
    'experience',
    'date_of_joining',
    'department_id',
    'designation_id',
    'subject',
    'class_in_charge_id',
    'is_class_in_charge',
    'country_id',
    'state_id',
    'district_id',
    'address',
    'pincode',
    'employment_type',
    'salary',
    'status',
    'is_verified',
])]
class Teacher extends Model
{
    use HasFactory;

    public const GENDERS = ['Male', 'Female', 'Others'];

    public const EMPLOYMENT_TYPES = ['permanent', 'temporary'];

    public const STATUSES = ['active', 'on leave', 'Training', 'suspended'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_joining' => 'date',
            'experience' => 'integer',
            'salary' => 'decimal:2',
            'is_verified' => 'boolean',
            'is_class_in_charge' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function classInCharge(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'class_in_charge_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function imageUrl(): ?string
    {
        return $this->teacher_image ? Storage::url($this->teacher_image) : null;
    }
}
