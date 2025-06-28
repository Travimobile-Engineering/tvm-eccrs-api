<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:15'],
            'nin' => ['nullable', 'string', 'max:11'],
            'zone_id' => ['required', 'integer'],
            'role_id' => ['required', 'integer', Rule::exists('authuser.roles', 'id')],
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ];
    }
}
