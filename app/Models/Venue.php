<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'venue_name',
    'venue_type',
    'building',
    'capacity',
    'facilities',
    'contact_person',
    'is_active',
    'remarks',
])]
class Venue extends Model
{
    use HasFactory;

    public const VENUE_TYPES = [
        'Auditorium',
        'Hall',
        'Conference Room',
        'Sports Ground',
        'Open Air Theatre',
        'Multipurpose Room',
        'Meeting Room',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'facilities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
