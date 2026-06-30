<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class VenueService
{
    public function query(array $filters = []): Builder
    {
        return Venue::query()
            ->when($filters['building'] ?? null, function (Builder $query, string $building): void {
                $query->where('building', 'like', "%{$building}%");
            })
            ->when($filters['venue_type'] ?? null, function (Builder $query, string $venueType): void {
                $query->where('venue_type', $venueType);
            })
            ->when($filters['capacity'] ?? null, function (Builder $query, string $capacity): void {
                $query->where('capacity', $capacity);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Venue::query()
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function venueTypes(): array
    {
        return Venue::VENUE_TYPES;
    }

    public function facilityOptions(): array
    {
        return [
            'Stage',
            'Sound System',
            'Projector',
            'Lighting',
            'Air Conditioner',
            'Wi-Fi',
            'Podium',
            'Seating',
            'Green Room',
            'Parking',
        ];
    }

    public function nextCode(): string
    {
        $lastCode = Venue::query()
            ->where('code', 'like', 'VEN%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'VEN'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Venue
    {
        return Venue::create([
            ...Arr::only($data, [
                'venue_name',
                'venue_type',
                'building',
                'capacity',
                'facilities',
                'contact_person',
                'is_active',
                'remarks',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Venue $venue, array $data): Venue
    {
        $venue->update(Arr::only($data, [
            'venue_name',
            'venue_type',
            'building',
            'capacity',
            'facilities',
            'contact_person',
            'is_active',
            'remarks',
        ]));

        return $venue;
    }

    public function toggleStatus(Venue $venue): Venue
    {
        $venue->forceFill(['is_active' => ! $venue->is_active])->save();

        return $venue;
    }

    public function delete(Venue $venue): void
    {
        $venue->delete();
    }
}
