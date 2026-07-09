<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'special_event_id',
    'file_path',
    'file_name',
    'mime_type',
    'file_size',
])]
class SpecialEventAttachment extends Model
{
    use HasFactory;

    public function specialEvent(): BelongsTo
    {
        return $this->belongsTo(SpecialEvent::class);
    }

    public function fileUrl(): string
    {
        return Storage::url($this->file_path);
    }
}
