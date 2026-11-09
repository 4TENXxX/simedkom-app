<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return $this->user()?->role === 'admin'
            && $submission?->status !== 'disetujui';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,perbaikan,disetujui,ditolak'],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
