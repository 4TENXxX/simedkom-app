<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKeywordSettingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $keywords = preg_split('/\r\n|\r|\n/', (string) $this->input('keywords.0', ''));
        $this->merge(['keywords' => $keywords]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['super_admin', 'admin'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'keywords' => ['required', 'array', 'min:1'],
            'keywords.*' => ['required', 'string', 'max:255'],
        ];
    }
}
