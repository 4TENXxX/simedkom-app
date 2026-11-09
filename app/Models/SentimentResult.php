<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentimentResult extends Model
{
    protected $fillable = ['news_clipping_id', 'label', 'confidence', 'engine_version', 'analyzed_text', 'analyzed_at'];

    protected function casts(): array
    {
        return ['confidence' => 'decimal:2', 'analyzed_at' => 'datetime'];
    }

    public function clipping(): BelongsTo
    {
        return $this->belongsTo(NewsClipping::class, 'news_clipping_id');
    }
}
