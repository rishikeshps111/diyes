<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Timetable;
use App\Models\TimeTableCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

class TimetableService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Timetable::query()
            ->with(['academicYear', 'grade', 'divisions', 'timetableCategory', 'incharge', 'preparedBy'])
            ->withCount('entries')
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, string $id) => $query->where('academic_year_id', $id))
            ->when($filters['grade_id'] ?? null, fn (Builder $query, string $id) => $query->where('grade_id', $id))
            ->when($filters['division_id'] ?? null, function (Builder $query, string $id): void {
                $query->whereHas('divisions', fn (Builder $query) => $query->whereKey($id));
            })
            ->when($filters['timetable_category_id'] ?? null, fn (Builder $query, string $id) => $query->where('timetable_category_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    public function selectedForExport(array $ids): Collection
    {
        return Timetable::query()
            ->with(['academicYear', 'grade', 'divisions', 'timetableCategory', 'incharge', 'preparedBy'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('timetable', Timetable::class);
    }

    public function create(array $data, User $user): Timetable
    {
        $timetable = Timetable::create([
            ...Arr::only($data, $this->fillableFields()),
            'code' => $this->nextCode(),
            'prepared_by_id' => $user->id,
            'prepared_at' => now(),
        ]);

        $timetable->divisions()->sync([$data['division_id']]);

        return $timetable;
    }

    public function update(Timetable $timetable, array $data): Timetable
    {
        $timetable->update(Arr::only($data, $this->fillableFields()));
        $timetable->divisions()->sync([$data['division_id']]);

        return $timetable;
    }

    public function delete(Timetable $timetable): void
    {
        $timetable->delete();
    }

    public function academicYears(): Collection
    {
        return AcademicYear::query()->orderByDesc('start_date')->get(['id', 'academic_year']);
    }

    public function grades(): Collection
    {
        return Grade::query()->with('academicYear')->orderBy('grade')->get(['id', 'grade', 'academic_year_id']);
    }

    public function divisions(): Collection
    {
        return Division::query()->with('grade.academicYear')->orderBy('division')->get(['id', 'division', 'grade_id']);
    }

    public function timetableCategories(): Collection
    {
        return TimeTableCategory::query()->active()->orderBy('title')->get(['id', 'title']);
    }

    public function incharges(): Collection
    {
        $role = Role::query()->where('name', 'Academic Supervisor')->where('guard_name', 'web')->first();

        return User::query()
            ->when($role, fn (Builder $query) => $query->role($role))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function fillableFields(): array
    {
        return [
            'timetable_name',
            'timetable_category_id',
            'applicable_from',
            'applicable_to',
            'academic_year_id',
            'grade_id',
            'total_periods_per_day',
            'period_duration_minutes',
            'short_break_minutes',
            'lunch_break_minutes',
            'short_break_after_lunch_minutes',
            'timetable_incharge_id',
            'description',
            'status',
        ];
    }
}
