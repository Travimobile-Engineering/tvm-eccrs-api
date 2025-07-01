<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'old_password' => 'required|string|max:255',
            'new_password' => 'required|string|max:255|confirmed',
        ];
    }
}
