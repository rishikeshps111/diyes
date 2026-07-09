<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Project::query()
            ->with(['category', 'grades', 'subjects', 'teachers'])
            ->when($filters['project_category_id'] ?? null, fn (Builder $query, string $id) => $query->where('project_category_id', $id))
            ->when($filters['grade_id'] ?? null, fn (Builder $query, string $id) => $query->whereHas('grades', fn (Builder $gradeQuery) => $gradeQuery->whereKey($id)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    public function selectedForExport(array $ids): Collection
    {
        return Project::query()
            ->with(['category', 'grades', 'subjects', 'teachers'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('project', Project::class, 'project_code');
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data): Project {
            $project = Project::create([
                ...$this->projectPayload($data),
                'created_by_id' => Auth::id(),
            ]);
            $this->syncRelations($project, $data);

            return $project;
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data): Project {
            $project->update($this->projectPayload($data));
            $this->syncRelations($project, $data);

            return $project;
        });
    }

    public function updateStatus(Project $project, string $status): Project
    {
        $project->forceFill(['status' => $status])->save();

        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function categories(): Collection
    {
        return ProjectCategory::query()->active()->orderBy('title')->get(['id', 'title']);
    }

    public function grades(): Collection
    {
        return Grade::query()->active()->orderBy('grade')->get(['id', 'grade']);
    }

    public function subjects(): Collection
    {
        return Subject::query()->active()->orderBy('subject_name')->get(['id', 'subject_name', 'subject_code']);
    }

    public function teachers(): Collection
    {
        return Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'employee_id', 'teacher_image']);
    }

    private function projectPayload(array $data): array
    {
        return [
            ...Arr::only($data, [
                'project_code',
                'project_title',
                'description',
                'project_category_id',
                'duration_days',
                'start_date',
                'end_date',
                'venue',
                'timetable_replacement',
            'status',
            ]),
            'status' => $data['status'] ?? 'draft',
        ];
    }

    private function syncRelations(Project $project, array $data): void
    {
        $project->subjects()->sync($data['subject_ids'] ?? []);
        $project->grades()->sync($data['grade_ids'] ?? []);
        $project->teachers()->sync($data['teacher_ids'] ?? []);
    }
}
