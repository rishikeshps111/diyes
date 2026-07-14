<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'session_no',
    'session_date',
    'time_from',
    'time_to',
    'topic_module',
    'duration_hours',
])]
class TrainingScheduleSession extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'duration_hours' => 'decimal:2',
        ];
    }

    public function trainingSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class);
    }
}
