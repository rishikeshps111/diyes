<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['special_event_id', 'grade_id', 'division_id', 'project_week_id', 'timetable_entry_id', 'day', 'period_no', 'entry_type', 'subject_name', 'teacher_names', 'start_time', 'end_time', 'duration_minutes', 'is_event_period'])]
class SpecialEventTimetableEntry extends Model
{
    protected function casts(): array
    {
        return ['teacher_names' => 'array', 'is_event_period' => 'boolean'];
    }

    public function specialEvent(): BelongsTo { return $this->belongsTo(SpecialEvent::class); }
    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
    public function projectWeek(): BelongsTo { return $this->belongsTo(ProjectWeek::class); }
    public function timetableEntry(): BelongsTo { return $this->belongsTo(TimetableEntry::class); }
}
