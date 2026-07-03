<?php

namespace App\Services;

use App\Models\ModulePrefix;
use Illuminate\Database\Eloquent\Builder;

class ModulePrefixService
{
    public function query(array $filters = []): Builder
    {
        return ModulePrefix::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('module', 'like', "%{$search}%")
                        ->orWhere('prefix', 'like', "%{$search}%");
                });
            });
    }

    public function update(ModulePrefix $modulePrefix, array $data): ModulePrefix
    {
        $modulePrefix->update([
            'prefix' => strtoupper($data['prefix']),
        ]);

        return $modulePrefix;
    }

}
