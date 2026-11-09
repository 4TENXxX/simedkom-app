<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordMatch extends Model
{
    protected $fillable = ['news_clipping_id', 'keyword', 'match_count'];

    public function clipping(): BelongsTo
    {
        return $this->belongsTo(NewsClipping::class, 'news_clipping_id');
    }
}
