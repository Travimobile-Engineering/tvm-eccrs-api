<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePhoneNumberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|max:15',
            'code' => 'required|string|max:5',
        ];
    }
}
