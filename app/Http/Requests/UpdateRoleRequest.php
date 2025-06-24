<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', 'integer', Rule::exists('authuser.permissions', 'id')],
        ];
    }
}
