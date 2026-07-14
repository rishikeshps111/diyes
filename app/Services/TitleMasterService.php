<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class TitleMasterService
{
    /** @param class-string<Model> $modelClass */
    public function __construct(
        private readonly PrefixCodeService $prefixCodeService,
        private readonly string $modelClass,
        private readonly string $prefixModule,
    ) {}

    public function query(array $filters = []): Builder
    {
        return $this->modelClass::query()
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->modelClass::query()->findOrFail($id);
    }

    public function make(array $attributes = []): Model
    {
        return new $this->modelClass($attributes);
    }

    public function selectedForExport(array $ids): Collection
    {
        return $this->modelClass::query()->whereKey($ids)->orderByDesc('created_at')->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next($this->prefixModule, $this->modelClass);
    }

    public function create(array $data): Model
    {
        return $this->modelClass::query()->create([
            ...Arr::only($data, ['title', 'is_active']),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Model $record, array $data): Model
    {
        $record->update(Arr::only($data, ['title', 'is_active']));

        return $record;
    }

    public function toggleStatus(Model $record): Model
    {
        $record->forceFill(['is_active' => ! $record->is_active])->save();

        return $record;
    }

    public function delete(Model $record): void
    {
        $record->delete();
    }
}
