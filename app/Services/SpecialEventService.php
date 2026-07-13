<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\EventType;
use App\Models\Grade;
use App\Models\SpecialEvent;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpecialEventService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return SpecialEvent::query()
            ->with(['eventType', 'academicYear', 'grades', 'divisions', 'staffCoordinators', 'teacherCoordinators'])
            ->withCount('timetableEntries')
            ->when($filters['event_type_id'] ?? null, fn (Builder $query, string $id) => $query->where('event_type_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    public function selectedForExport(array $ids): Collection
    {
        return SpecialEvent::query()
            ->with(['eventType', 'academicYear', 'grades', 'divisions', 'staffCoordinators', 'teacherCoordinators'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('special_event', SpecialEvent::class, 'event_code');
    }

    public function create(array $data): SpecialEvent
    {
        return DB::transaction(function () use ($data): SpecialEvent {
            $banner = $data['banner_image'] ?? null;
            $attachments = $data['attachments'] ?? [];
            unset($data['banner_image'], $data['attachments']);

            $specialEvent = SpecialEvent::create([
                ...$this->payload($data),
                'event_code' => $this->nextCode(),
                'banner_image' => $banner instanceof UploadedFile ? $banner->store('special-events/banners', 'public') : null,
                'created_by_id' => Auth::id(),
            ]);

            $this->syncRelations($specialEvent, $data);
            $this->storeAttachments($specialEvent, $attachments);

            return $specialEvent;
        });
    }

    public function update(SpecialEvent $specialEvent, array $data): SpecialEvent
    {
        return DB::transaction(function () use ($specialEvent, $data): SpecialEvent {
            $banner = $data['banner_image'] ?? null;
            $attachments = $data['attachments'] ?? [];
            unset($data['banner_image'], $data['attachments']);

            $payload = $this->payload($data);

            if ($banner instanceof UploadedFile) {
                if ($specialEvent->banner_image) {
                    Storage::disk('public')->delete($specialEvent->banner_image);
                }

                $payload['banner_image'] = $banner->store('special-events/banners', 'public');
            }

            $specialEvent->update($payload);
            $this->syncRelations($specialEvent, $data);
            $this->storeAttachments($specialEvent, $attachments);

            return $specialEvent;
        });
    }

    public function delete(SpecialEvent $specialEvent): void
    {
        DB::transaction(function () use ($specialEvent): void {
            if ($specialEvent->banner_image) {
                Storage::disk('public')->delete($specialEvent->banner_image);
            }

            $specialEvent->attachments->each(fn ($attachment) => Storage::disk('public')->delete($attachment->file_path));
            $specialEvent->delete();
        });
    }

    public function eventTypes(): Collection
    {
        return EventType::query()->active()->orderBy('title')->get(['id', 'title']);
    }

    public function academicYears(): Collection
    {
        return AcademicYear::query()->orderByDesc('start_date')->get(['id', 'academic_year']);
    }

    public function grades(): Collection
    {
        return Grade::query()->active()->with('academicYear')->orderBy('grade')->get(['id', 'grade', 'academic_year_id']);
    }

    public function divisions(): Collection
    {
        return Division::query()->active()->with('grade')->orderBy('division')->get(['id', 'division', 'grade_id']);
    }

    public function staff(): Collection
    {
        return User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'employee_code']);
    }

    public function teachers(): Collection
    {
        return Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'employee_id']);
    }

    private function payload(array $data): array
    {
        return [
            ...Arr::only($data, [
                'event_title',
                'event_type_id',
                'academic_year_id',
                'event_start_date',
                'event_end_date',
                'media_coverable',
                'venue',
                'organized_by',
                'incharge',
                'contact_no',
                'participants',
                'outside_candidates',
                'objective',
                'event_details',
                'status',
            ]),
            'days' => $this->daysBetween($data['event_start_date'], $data['event_end_date']),
        ];
    }

    private function syncRelations(SpecialEvent $specialEvent, array $data): void
    {
        $specialEvent->grades()->sync($data['grade_ids'] ?? []);
        $specialEvent->divisions()->sync($data['division_ids'] ?? []);
        $specialEvent->staffCoordinators()->sync($data['staff_coordinator_ids'] ?? []);
        $specialEvent->teacherCoordinators()->sync($data['teacher_coordinator_ids'] ?? []);

        $specialEvent->timings()->delete();
        $specialEvent->timings()->createMany(collect($data['timings'] ?? [])
            ->map(fn (array $timing): array => Arr::only($timing, [
                'day_number',
                'event_date',
                'day_label',
                'start_time',
                'end_time',
            ]))
            ->values()
            ->all());
    }

    private function storeAttachments(SpecialEvent $specialEvent, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (! $attachment instanceof UploadedFile) {
                continue;
            }

            $specialEvent->attachments()->create([
                'file_path' => $attachment->store('special-events/attachments', 'public'),
                'file_name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getClientMimeType(),
                'file_size' => $attachment->getSize(),
            ]);
        }
    }

    private function daysBetween(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }
}
