<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'applied_date',
    'application_no',
    'applied_by',
    'submitted_by_applicant',
    'admin_viewed_at',
    'teacher_id',
    'applicant_type',
    'user_id',
    'role_id',
    'leave_type_id',
    'from_date',
    'to_date',
    'days',
    'reason',
    'status',
    'approved_by',
    'approved_at',
    'remarks',
    'is_half_day',
    
])]
class LeaveApplication extends Model
{
    use HasFactory;

    public const STATUSES = ['Pending', 'Approved', 'Rejected'];

    protected function casts(): array
    {
        return [
            'applied_date' => 'date',
            'from_date' => 'date',
            'to_date' => 'date',
            'days' => 'decimal:1',
            'approved_at' => 'datetime',
            'is_half_day' => 'boolean',
            'submitted_by_applicant' => 'boolean',
            'admin_viewed_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function getApplicantNameAttribute(): string
    {
        return $this->applicant_type === 'user'
            ? ($this->user?->name ?? '-')
            : ($this->teacher?->name ?? '-');
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, ['Approved', 'Rejected'], true);
    }
}
