<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
            'logout_others' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.different' => 'La nueva contraseña debe ser diferente a la actual',
            'new_password.confirmed' => 'Las contraseñas no coinciden'
        ];
    }
}