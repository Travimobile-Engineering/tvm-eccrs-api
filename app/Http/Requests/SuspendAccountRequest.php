<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuspendAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'reason' => 'required|string|max:255',
            'explanation' => 'nullable|string',
            'indefinite' => 'required|boolean',
            'end_date' => 'nullable|date|required_if:indefinite,false',
        ];
    }
}
