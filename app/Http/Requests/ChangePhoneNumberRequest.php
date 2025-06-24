<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePhoneNumberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('authuser.users', 'id')],
            'phone_number' => 'required|string|max:15',
        ];
    }
}
