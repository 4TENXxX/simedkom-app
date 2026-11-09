<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePressReleaseRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,terjadwal,dipublikasikan,diarsipkan'],
            'published_at' => ['nullable', 'date'],
            'attachment' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png', 'mp3', 'mp4'])->max(20 * 1024)],
        ];
    }
}
