<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreNewsClippingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'media';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'contract_id' => ['required', 'integer', 'exists:contracts,id'],
            'type' => ['required', Rule::in(['online', 'cetak', 'tv', 'radio'])],
            'url' => ['nullable', 'url', 'max:2048', 'required_if:type,online'],
            'title' => ['required', 'string', 'max:255'],
            'published_at' => ['required', 'date'],
            'author' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'files' => ['required_if:type,cetak,tv,radio', 'array'],
            'files.*' => [File::types(['pdf', 'jpg', 'jpeg', 'png', 'mp3', 'mp4'])->max(50 * 1024)],
        ];
    }
}
