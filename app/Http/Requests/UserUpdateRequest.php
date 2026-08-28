<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Election;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'contact_number' => ['required', 'digits:10', Rule::unique('users', 'contact_number')->ignore($userId)],
            'election_id' => ['required', 'exists:elections,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'User name must contain only alphabet characters and spaces.',
            'contact_number.digits' => 'User contact number must be exactly 10 digits.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! auth()->check() || ! auth()->user()->isHost() || ! $this->filled('election_id')) {
                return;
            }

            $election = Election::query()->find($this->input('election_id'));

            if (! $election || (int) $election->host_id !== (int) auth()->id()) {
                $validator->errors()->add('election_id', 'Please select one of your election places.');
            }
        });
    }
}
