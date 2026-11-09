<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = ['contract_id', 'media_profile_id', 'submitted_by', 'reviewed_by', 'invoice_number', 'invoice_date', 'period_start', 'period_end', 'subtotal', 'tax', 'total', 'status', 'review_notes', 'invoice_path', 'tax_document_path'];

    protected function casts(): array
    {
        return ['invoice_date' => 'date', 'period_start' => 'date', 'period_end' => 'date', 'subtotal' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2'];
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
