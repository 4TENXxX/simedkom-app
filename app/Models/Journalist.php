<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journalist extends Model
{
    protected $fillable = [
        'user_id',
        'media_profile_id',
        'phone',
        'position',
        'foto_wartawan_path',
        'sertifikat_kompetensi_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediaProfile(): BelongsTo
    {
        return $this->belongsTo(MediaProfile::class);
    }
}
