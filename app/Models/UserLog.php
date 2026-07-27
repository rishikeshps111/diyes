<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'name',
        'email',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'logged_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}