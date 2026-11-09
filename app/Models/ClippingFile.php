<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClippingFile extends Model
{
    protected $fillable = ['news_clipping_id', 'file_path', 'original_name', 'mime_type', 'size'];

    public function clipping(): BelongsTo
    {
        return $this->belongsTo(NewsClipping::class, 'news_clipping_id');
    }
}
