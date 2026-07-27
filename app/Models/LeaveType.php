<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'leave_name',
    'code',
    'total_days',
    'is_lop',
    'status',
    'role_id',
    'designation_id'
])]
class LeaveType extends Model
{
    use HasFactory;

   
    public function balances()
{
    return $this->hasMany(TeacherLeaveBalance::class);
}

public function applications()
{
    return $this->hasMany(LeaveApplication::class);
}
}
