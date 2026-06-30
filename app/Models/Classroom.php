<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code',
    'room_name',
    'building',
    'floor',
    'room_type',
    'seating_capacity',
    'department_id',
    'equipment',
    'is_active',
    'remarks',
])]
class Classroom extends Model
{
    use HasFactory;

    public const ROOM_TYPES = [
        'Smart Classroom',
        'Laboratory',
        'Lecture Hall',
        'Computer Lab',
        'Library Room',
        'Activity Room',
        'Seminar Hall',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'equipment' => 'array',
            'is_active' => 'boolean',
            'seating_capacity' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
