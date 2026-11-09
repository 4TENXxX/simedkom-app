<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use App\Models\Document;

class StoreMediaSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'media';
    }

    public function rules(): array
    {
        return [
            'nama_media' => ['required', 'string', 'max:255'],
            'jenis_media' => ['required', 'in:online,cetak,tv'],
            'alamat' => ['required', 'string', 'max:2000'],
            'no_whatsapp' => ['required', 'string', 'max:30'],
            'link_website' => ['nullable', 'url', 'max:255'],
            'tahun_anggaran' => ['required', 'integer', 'min:2020', 'max:2100'],
            'documents' => ['required', 'array:'.implode(',', array_keys(Document::types()))],
            ...collect(array_keys(Document::types()))->mapWithKeys(fn (string $type): array => [
                'documents.'.$type => ['required', File::types(['pdf', 'jpg', 'jpeg'])->max(5 * 1024)],
            ])->all(),
        ];
    }
}
