<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Document;

class UpdateMediaSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return $this->user()?->role === 'media'
            && $submission?->user_id === $this->user()->id
            && $submission?->status === 'perbaikan';
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
            'documents' => ['nullable', 'array:'.implode(',', [...array_keys(Document::types()), 'Proposal'])],
            ...collect([...array_keys(Document::types()), 'Proposal'])->mapWithKeys(fn (string $type): array => [
                'documents.'.$type => ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
            ])->all(),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->hasFile('documents')) {
                    $validator->errors()->add('documents', 'Unggah minimal satu dokumen untuk mengirim perbaikan.');
                }
            },
        ];
    }
}
