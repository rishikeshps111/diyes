<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'teacher_id',
    'document_type',
    'document_file',
    'verification_status',
    'verified_by',
    'verified_at',
])]
class TeacherDocument extends Model
{
    use HasFactory;

    public const DOCUMENT_TYPES = ['Educational Certificate', 'Aadhaar', 'Experience Certificate'];

    public const VERIFICATION_STATUSES = ['Pending', 'Verified'];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function fileUrl(): string
    {
        return Storage::url($this->document_file);
    }
}
