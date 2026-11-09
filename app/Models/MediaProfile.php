<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nama_media', 'jenis_media', 'alamat', 'no_whatsapp', 'link_website'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function newsClippings(): HasMany
    {
        return $this->hasMany(NewsClipping::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function journalists(): HasMany
    {
        return $this->hasMany(Journalist::class);
    }
}
