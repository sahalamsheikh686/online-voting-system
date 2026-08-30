<?php

namespace App\Http\Requests;

use App\Models\Election;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateRequest extends FormRequest
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
        $candidateId = $this->route('candidate')?->id;

        return [
            'name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'image' => [$candidateId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'vision' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:4096'],
            'election_name' => ['required', 'string', 'max:255'],
            'election_id' => ['required', 'exists:elections,id'],
            'email' => ['required', 'email', Rule::unique('candidates', 'email')->ignore($candidateId)],
            'position' => ['nullable', 'string', 'max:255'],
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

    public function messages(): array
    {
        return [
            'name.regex' => 'Candidate name must contain only alphabet characters and spaces.',
            'age.integer' => 'Candidate age must contain only numbers.',
            'image.mimes' => 'Candidate image must be jpg, jpeg, or png.',
            'vision.mimes' => 'Candidate vision must be an image, PDF, or Word file.',
            'election_name.required' => 'Please select the candidate election.',
            'election_id.required' => 'Please select the candidate place.',
        ];
    }
}
