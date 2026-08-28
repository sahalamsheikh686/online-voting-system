<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PendingUserActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['accept', 'reject'])],
            'rejection_message' => ['nullable', 'string', 'max:1000', 'required_if:action,reject'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_message.required_if' => 'Please enter a rejection message or correction note.',
        ];
    }
}
