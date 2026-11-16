<?php

namespace App\Http\Requests\Visa;

use Illuminate\Foundation\Http\FormRequest;

class DeclareVisaOutcomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visa_application_id'   => ['required', 'integer', 'exists:visa_applications,id'],
            'outcome'               => ['required', 'string', 'in:granted,refused'],
            'refusal_reason'        => ['nullable', 'string', 'max:1000'],
            'refusal_proof_file_id' => ['nullable', 'integer', 'exists:files,id'],
            'student_bank_details'  => ['nullable', 'string', 'max:1000'],
        ];
    }
}
