<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

#[Fillable([
    'code',
    'leave_name',
    'leave_type',
    'max_leaves_per_year',
    'total_days',
    'is_lop',
    'carry_forward_allowed',
    'max_carry_forward_limit',
    'applicable_for',
    'role_id',
    'gender_specific',
    'max_leave_days_per_request',
    'advance_notice_days',
    'allow_half_day',
    'requires_approval',
    'encashment_allowed',
    'status',
    'description',
])]
class LeaveType extends Model
{
    use HasFactory;

    public const LEAVE_TYPES = ['paid' => 'Paid', 'unpaid' => 'Unpaid'];
    public const APPLICABLE_FOR = ['all' => 'All', 'teachers' => 'Teachers', 'role' => 'Role'];
    public const GENDERS = ['all' => 'All', 'male' => 'Male', 'female' => 'Female'];
    public const STATUSES = [1 => 'Active', 0 => 'Inactive'];

    protected function casts(): array
    {
        return [
            'max_leaves_per_year' => 'integer',
            'total_days' => 'integer',
            'is_lop' => 'boolean',
            'carry_forward_allowed' => 'boolean',
            'max_carry_forward_limit' => 'integer',
            'max_leave_days_per_request' => 'integer',
            'advance_notice_days' => 'integer',
            'allow_half_day' => 'boolean',
            'requires_approval' => 'boolean',
            'encashment_allowed' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(TeacherLeaveBalance::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function getApplicableForTextAttribute(): string
    {
        if (! $this->applicable_for) {
            return $this->role?->name ?? 'All';
        }

        return $this->applicable_for === 'role'
            ? ($this->role?->name ?? 'Role')
            : (self::APPLICABLE_FOR[$this->applicable_for] ?? ucfirst((string) $this->applicable_for));
    }
}
