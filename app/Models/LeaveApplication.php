<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
'applied_date','application_no',
    'teacher_id',
    'leave_type_id',
    'from_date',
    'to_date',
    'days',
    'reason',
    'status',
    'approved_by',
    'approved_at',
    'remarks',
    
])]
class LeaveApplication extends Model
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
