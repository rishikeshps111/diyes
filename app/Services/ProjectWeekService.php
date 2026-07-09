<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Project;
use App\Models\ProjectWeek;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectWeekService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return ProjectWeek::query()
            ->with(['project', 'academicYear', 'grade', 'divisions'])
            ->withCount('entries')
            ->when($filters['grade_id'] ?? null, fn (Builder $query, string $id) => $query->where('grade_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    public function selectedForExport(array $ids): Collection
    {
        return ProjectWeek::query()
            ->with(['project', 'academicYear', 'grade', 'divisions'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('project_week', ProjectWeek::class);
    }

    public function create(array $data): ProjectWeek
    {
        return DB::transaction(function () use ($data): ProjectWeek {
            $projectWeek = ProjectWeek::create([
                ...Arr::only($data, $this->fillableFields()),
                'code' => $this->nextCode(),
                'created_by_id' => Auth::id(),
            ]);

            $projectWeek->divisions()->sync([$data['division_id']]);

            return $projectWeek;
        });
    }

    public function update(ProjectWeek $projectWeek, array $data): ProjectWeek
    {
        return DB::transaction(function () use ($projectWeek, $data): ProjectWeek {
            $projectWeek->update(Arr::only($data, $this->fillableFields()));
            $projectWeek->divisions()->sync([$data['division_id']]);

            return $projectWeek;
        });
    }

    public function delete(ProjectWeek $projectWeek): void
    {
        $projectWeek->delete();
    }

    public function projects(): Collection
    {
        return Project::query()->orderBy('project_title')->get(['id', 'project_code', 'project_title']);
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

    private function fillableFields(): array
    {
        return [
            'project_id',
            'applicable_from',
            'applicable_to',
            'academic_year_id',
            'grade_id',
            'total_periods',
            'description',
            'status',
        ];
    }
}
