<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapingResult extends Model
{
    protected $fillable = ['news_clipping_id', 'status', 'canonical_url', 'scraped_title', 'scraped_content', 'http_status', 'error_message', 'scraped_at'];

    protected function casts(): array
    {
        return ['scraped_at' => 'datetime'];
    }

    public function clipping(): BelongsTo
    {
        return $this->belongsTo(NewsClipping::class, 'news_clipping_id');
    }
}
