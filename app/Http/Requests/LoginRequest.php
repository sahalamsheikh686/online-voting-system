<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', function (string $attribute, mixed $value, $fail) {
                $login = (string) $value;

                if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                    return;
                }

                if (preg_match('/^\d{10}$/', $login)) {
                    return;
                }

                $fail('Enter a valid email address or exactly 10 digit contact number.');
            }],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Please enter your contact number or host email.',
        ];
    }
}
