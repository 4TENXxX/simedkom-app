<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['submission_id', 'jenis_dokumen', 'file_path'];

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            'SuratPermohonan' => 'Surat permohonan penawaran bermaterai',
            'RAB' => 'Usulan Rencana Anggaran Biaya (RAB)',
            'AktaPendirian' => 'Akta pendirian perusahaan',
            'IzinUsaha' => 'Surat izin usaha perusahaan',
            'NIB' => 'Tanda Daftar Perusahaan / Nomor Induk Berusaha',
            'NPWP' => 'NPWP perusahaan',
            'SPT' => 'Bukti lapor SPT tahun berjalan / surat keterangan fiskal',
            'VerifikasiDewanPers' => 'Sertifikat verifikasi / bukti daftar perusahaan pers dari Dewan Pers',
            'SuratPenunjukan' => 'Surat penunjukan kepala biro / perwakilan daerah',
            'SuratKuasa' => 'Surat kuasa penandatangan kerja sama',
            'Rekening' => 'Nomor rekening perusahaan atau rekening yang ditunjuk',
            'FotoKantor' => 'Foto kantor / perusahaan',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
