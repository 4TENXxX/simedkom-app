<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NewsClipping extends Model
{
    protected $fillable = [
        'contract_id',
        'media_profile_id',
        'submitted_by',
        'validated_by',
        'type',
        'url',
        'title',
        'published_at',
        'author',
        'category',
        'notes',
        'status',
        'admin_notes',
        'duplicate_status',
        'keyword_status',
        'sentiment_label',
        'sentiment_confidence',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'validated_at' => 'datetime',
            'sentiment_confidence' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function mediaProfile(): BelongsTo
    {
        return $this->belongsTo(MediaProfile::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function scrapingResult(): HasOne
    {
        return $this->hasOne(ScrapingResult::class);
    }

    public function keywordMatches(): HasMany
    {
        return $this->hasMany(KeywordMatch::class);
    }

    public function sentimentResult(): HasOne
    {
        return $this->hasOne(SentimentResult::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ClippingFile::class);
    }
}
