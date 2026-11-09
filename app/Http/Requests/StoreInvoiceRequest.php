<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreInvoiceRequest extends FormRequest
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
            'contract_id' => ['required', 'exists:contracts,id'], 'invoice_number' => ['required', 'string', 'max:100', 'unique:invoices,invoice_number'],
            'invoice_date' => ['required', 'date'], 'period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'subtotal' => ['required', 'numeric', 'min:0'], 'tax' => ['required', 'numeric', 'min:0'], 'total' => ['required', 'numeric', 'min:0'],
            'invoice' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10 * 1024)], 'tax_document' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10 * 1024)],
        ];
    }
}
