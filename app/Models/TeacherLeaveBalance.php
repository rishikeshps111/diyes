<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'teacher_id',
    'leave_type_id',
    'allocated_days',
    'used_days',
    'remaining_days'
])]
class TeacherLeaveBalance extends Model
{
    use HasFactory;

  public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
   

}
