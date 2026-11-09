<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PressRelease extends Model
{
    protected $fillable = [
        'author_id', 'title', 'summary', 'body', 'category',
        'published_at', 'status', 'attachment_path',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }
}
