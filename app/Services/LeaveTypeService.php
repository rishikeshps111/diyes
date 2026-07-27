<?php

namespace App\Services;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

class LeaveTypeService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return LeaveType::query()
            ->with('role')
            ->when(isset($filters['leave_type']) && $filters['leave_type'] !== '', fn (Builder $query) => $query->where('leave_type', $filters['leave_type']))
            ->when(isset($filters['applicable_for']) && $filters['applicable_for'] !== '', fn (Builder $query) => $query->where('applicable_for', $filters['applicable_for']))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']));
    }

    public function selectedForExport(array $ids): Collection
    {
        return LeaveType::query()->with('role')->whereKey($ids)->orderBy('leave_name')->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('leave_type', LeaveType::class);
    }

    public function roles(): Collection
    {
        return Role::query()->where('name', '!=', 'admin')->orderBy('name')->get(['id', 'name']);
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::create($this->payload($data));
    }

    public function update(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($this->payload($data));

        return $leaveType;
    }

    public function delete(LeaveType $leaveType): void
    {
        $leaveType->delete();
    }

    private function payload(array $data): array
    {
        return [
            ...Arr::only($data, [
                'code', 'leave_name', 'leave_type', 'max_leaves_per_year',
                'carry_forward_allowed', 'max_carry_forward_limit', 'applicable_for',
                'role_id', 'gender_specific', 'max_leave_days_per_request',
                'advance_notice_days', 'allow_half_day', 'requires_approval',
                'encashment_allowed', 'status', 'description',
            ]),
            'max_carry_forward_limit' => $data['carry_forward_allowed'] ? $data['max_carry_forward_limit'] : null,
            'role_id' => $data['applicable_for'] === 'role' ? $data['role_id'] : null,
            // Keep the legacy leave calculation fields synchronized.
            'total_days' => $data['max_leaves_per_year'],
            'is_lop' => $data['leave_type'] === 'unpaid',
        ];
    }
}
