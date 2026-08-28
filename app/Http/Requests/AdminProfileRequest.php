<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $admin = $this->route('admin');
        $userId = $admin?->id;

        $rules = [
            'name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'contact_number' => [
                'required',
                'digits:10',
                Rule::unique('users', 'contact_number')->ignore($userId),
            ],
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Admin name must contain only alphabet characters and spaces.',
            'age.integer' => 'Admin age must contain only numbers.',
            'contact_number.digits' => 'Admin contact number must be exactly 10 digits.',
            'password.min' => 'Admin password must be at least 8 characters.',
            'password.confirmed' => 'Admin password confirmation does not match.',
        ];
    }
}
