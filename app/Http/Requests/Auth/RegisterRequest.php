<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'                      => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password'                   => ['required', 'string', 'min:8'],
            'first_name'                 => ['required', 'string', 'max:100'],
            'last_name'                  => ['required', 'string', 'max:100'],
            'birth_date'                 => ['nullable', 'date'],
            'phone'                      => ['nullable', 'string', 'max:40'],
            'dial_code'                  => ['nullable', 'string', 'max:12'],
            'phone_number'               => ['nullable', 'string', 'max:40'],
            'country_code'               => ['nullable', 'string', 'max:10'],
            'preferred_locale'           => ['nullable', 'string', 'max:5'],
            'level_id'                   => ['nullable'],
            'level_choice'               => ['nullable', 'string', 'max:100'],
            'study_mode'                 => ['nullable', 'string', 'max:100'],
            'study_purpose'              => ['nullable', 'string', 'max:120'],
            'rhythm'                     => ['nullable', 'string', 'in:day,evening'],
            'brazil_city_id'             => ['nullable'],
            'city_choice'                => ['nullable', 'string', 'max:100'],
            'target_university'          => ['nullable', 'string', 'max:190'],
            'country'                    => ['nullable', 'string', 'max:100'],
            'previous_level_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
