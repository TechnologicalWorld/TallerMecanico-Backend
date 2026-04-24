<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'persona_id' => [
                'required',
                'integer',
                'exists:personas,id',
                function ($attribute, $value, $fail) {
                    if (\App\Models\User::where('persona_id', $value)->exists()) {
                        $fail('Esta persona ya tiene un usuario asociado. No puede tener múltiples usuarios.');
                    }
                },
            ],            
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email'),
            ],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username'),
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
            'activo' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'persona_id.required' => 'La persona es requerida',
            'persona_id.exists' => 'La persona no existe',
            'email.required' => 'El email es requerido',
            'email.unique' => 'Este email ya está registrado',
            'username.required' => 'El username es requerido',
            'username.unique' => 'Este username ya está en uso',
            'username.alpha_dash' => 'El username solo puede contener letras, números, guiones y guiones bajos',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}
