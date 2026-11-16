<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id'     => ['required', 'integer', 'exists:enrollments,id'],
            'installment_id'    => ['nullable', 'integer', 'exists:enrollment_installments,id'],
            'amount'            => ['required', 'numeric', 'min:1'],
            'currency'          => ['required', 'string', 'size:3'],
            'payment_method'    => ['required', 'string', 'in:bank_transfer,stripe,card,cash'],
            'gateway_reference' => ['nullable', 'string', 'max:255'],
            'proof_file_id'     => ['nullable', 'integer', 'exists:files,id'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }
}
