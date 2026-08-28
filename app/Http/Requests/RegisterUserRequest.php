<?php

namespace App\Http\Requests;

use App\Models\Election;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('election_name') && $this->filled('election_id')) {
            $this->merge([
                'election_name' => Election::query()->find($this->input('election_id'))?->name,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'contact_number' => ['required', 'digits:10', Rule::unique('users', 'contact_number')->where(fn ($query) => $query->where('status', '!=', 'rejected'))],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($query) => $query->where('status', '!=', 'rejected'))],
            'password' => ['required', 'confirmed', Password::min(8)],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'election_name' => ['required', 'string', 'max:255'],
            'election_id' => ['required', 'exists:elections,id'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('election_id') || ! $this->filled('election_name')) {
                return;
            }

            $election = Election::query()->find($this->input('election_id'));

            if (! $election || $election->name !== $this->input('election_name')) {
                $validator->errors()->add('election_id', 'Please select a valid election place.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'User name must contain only alphabet characters and spaces.',
            'contact_number.digits' => 'User contact number must be exactly 10 digits.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'Confirm your both password.',
            'date_of_birth.before_or_equal' => 'You are not eligible to register. Your age must be 18 or above.',
            'election_id.required' => 'Please select your election place.',
            'election_name.required' => 'Please select your election.',
            'image.mimes' => 'Current image must be a jpg, jpeg, or png file.',
        ];
    }
}
