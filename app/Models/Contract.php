<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'media_profile_id',
        'nomor_kontrak',
        'tanggal_mulai',
        'tanggal_berakhir',
        'nilai_kontrak',
        'kuota_berita',
        'harga_per_publikasi',
        'jenis_layanan',
        'status',
        'dokumen_kontrak',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
            'nilai_kontrak' => 'decimal:2',
            'harga_per_publikasi' => 'decimal:2',
            'kuota_berita' => 'integer',
        ];
    }

    public function mediaProfile(): BelongsTo
    {
        return $this->belongsTo(MediaProfile::class);
    }

    public function newsClippings(): HasMany
    {
        return $this->hasMany(NewsClipping::class);
    }

    public function getKuotaTerpakaiAttribute(): int
    {
        return $this->newsClippings()->where('status', 'valid')->count();
    }

    public function getKuotaTersisaAttribute(): int
    {
        return max(0, $this->kuota_berita - $this->kuota_terpakai);
    }

    public function getPersentaseCapaianAttribute(): float
    {
        return $this->kuota_berita > 0
            ? round(($this->kuota_terpakai / $this->kuota_berita) * 100, 2)
            : 0;
    }
}
