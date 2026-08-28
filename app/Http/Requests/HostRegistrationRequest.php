<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class HostRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'contact_number' => ['required', 'digits:10', Rule::unique('users', 'contact_number')->where(fn ($query) => $query->where('status', '!=', 'rejected'))],
            'reason_type' => ['required', Rule::in(['School', 'College', 'Random'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($query) => $query->where('status', '!=', 'rejected'))],
            'password' => ['required', 'confirmed', Password::min(8)],
            'reason_message' => ['required', 'string', function (string $attribute, mixed $value, $fail) {
                $message = strip_tags((string) $value);
                $wordCount = str_word_count($message);

                if (! preg_match('/[A-Za-z]/', $message)) {
                    $fail('Why you want to host election must contain text.');
                }

                if ($wordCount < 10) {
                    $fail('Why you want to host election must be at least 10 words.');
                }

                if ($wordCount > 150) {
                    $fail('Why you want to host election must not be more than 150 words.');
                }
            }],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Host name must contain only alphabet characters and spaces.',
            'contact_number.digits' => 'Host contact number must be exactly 10 digits.',
            'reason_type.required' => 'Please select the reason for host.',
            'reason_type.in' => 'Please select a valid reason for host.',
            'email.email' => 'Please enter a valid host email address.',
            'password.confirmed' => 'Host password confirmation does not match.',
            'image.mimes' => 'Profile picture must be a jpg, jpeg, or png file.',
        ];
    }
}
