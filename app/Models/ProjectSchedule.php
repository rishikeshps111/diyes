<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'day_number',
    'schedule_date',
    'topic',
    'description',
    'remarks',
])]
class ProjectSchedule extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'day_number' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
