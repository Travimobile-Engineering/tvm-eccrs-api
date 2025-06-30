<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', Rule::unique('authuser.users', 'unique_id')],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('authuser.users', 'email')],
            'phone_number' => ['required', 'string', 'max:15'],
            'nin' => ['nullable', 'string', 'max:11'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'zone_id' => ['required', 'integer'],
            'role_id' => ['required', 'integer', Rule::exists('authuser.roles', 'id')],
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ];
    }
}
