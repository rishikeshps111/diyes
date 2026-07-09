<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'special_event_id',
    'day_number',
    'event_date',
    'day_label',
    'start_time',
    'end_time',
])]
class SpecialEventTiming extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function specialEvent(): BelongsTo
    {
        return $this->belongsTo(SpecialEvent::class);
    }
}
