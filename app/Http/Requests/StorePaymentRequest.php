<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePaymentRequest extends FormRequest
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
            'reference_number' => ['required', 'string', 'max:100'], 'paid_at' => ['required', 'date'], 'amount' => ['required', 'numeric', 'min:0'],
            'proof' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10 * 1024)], 'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
