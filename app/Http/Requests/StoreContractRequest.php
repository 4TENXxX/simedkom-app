<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'media_profile_id' => ['required', 'exists:media_profiles,id'],
            'nomor_kontrak' => ['required', 'string', 'max:100', 'unique:contracts,nomor_kontrak'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'nilai_kontrak' => ['required', 'numeric', 'min:0'],
            'kuota_berita' => ['required', 'integer', 'min:1'],
            'harga_per_publikasi' => ['required', 'numeric', 'min:0'],
            'jenis_layanan' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,aktif,selesai,dibatalkan,kedaluwarsa'],
            'dokumen_kontrak' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10 * 1024)],
        ];
    }
}
