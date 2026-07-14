<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\TrainerCategory;
use App\Models\TrainerType;
use App\Models\TrainingSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingScheduleService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return TrainingSchedule::query()
            ->with(['trainerType', 'trainerCategory'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['trainer_type_id'] ?? null, fn (Builder $query, string $id) => $query->where('trainer_type_id', $id))
            ->when($filters['trainer_category_id'] ?? null, fn (Builder $query, string $id) => $query->where('trainer_category_id', $id));
    }

    public function selectedForExport(array $ids): Collection
    {
        return TrainingSchedule::query()
            ->with(['trainerType', 'trainerCategory', 'subjects', 'sessions'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('training_schedule', TrainingSchedule::class);
    }

    public function create(array $data): TrainingSchedule
    {
        return DB::transaction(function () use ($data): TrainingSchedule {
            $trainingSchedule = TrainingSchedule::create([
                ...Arr::only($data, $this->fillableFields()),
                'code' => $this->nextCode(),
                'created_by_id' => Auth::id(),
            ]);

            $this->syncRelations($trainingSchedule, $data);

            return $trainingSchedule;
        });
    }

    public function update(TrainingSchedule $trainingSchedule, array $data): TrainingSchedule
    {
        return DB::transaction(function () use ($trainingSchedule, $data): TrainingSchedule {
            $trainingSchedule->update(Arr::only($data, $this->fillableFields()));
            $this->syncRelations($trainingSchedule, $data);

            return $trainingSchedule;
        });
    }

    public function delete(TrainingSchedule $trainingSchedule): void
    {
        $trainingSchedule->delete();
    }

    public function trainerTypes(): Collection
    {
        return TrainerType::query()->active()->orderBy('title')->get(['id', 'code', 'title']);
    }

    public function trainerCategories(): Collection
    {
        return TrainerCategory::query()->active()->orderBy('title')->get(['id', 'code', 'title']);
    }

    public function subjects(): Collection
    {
        return Subject::query()->active()->with('grade')->orderBy('subject_name')->get();
    }

    private function syncRelations(TrainingSchedule $trainingSchedule, array $data): void
    {
        $subjectIds = $data['applicable'] === 'teachers' ? ($data['subject_ids'] ?? []) : [];
        $trainingSchedule->subjects()->sync($subjectIds);
        $trainingSchedule->sessions()->delete();
        $trainingSchedule->sessions()->createMany(
            collect($data['sessions'])->values()->map(fn (array $session, int $index): array => [
                ...Arr::only($session, ['session_date', 'time_from', 'time_to', 'topic_module', 'duration_hours']),
                'session_no' => $index + 1,
            ])->all(),
        );
    }

    private function fillableFields(): array
    {
        return [
            'title',
            'trainer_type_id',
            'trainer_category_id',
            'conducted_by',
            'resource_person_trainer',
            'start_date',
            'end_date',
            'per_day_hours',
            'mode',
            'venue',
            'total_count',
            'applicable',
            'training_objectives',
            'training_description',
            'remarks',
            'status',
        ];
    }
}
