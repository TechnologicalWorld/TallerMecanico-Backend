<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'persona_id' => [
                'sometimes',
                'integer',
                'exists:personas,id',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($value) {
                        $exists = \App\Models\User::where('persona_id', $value)
                            ->where('id', '!=', $userId)
                            ->exists();
                        if ($exists) {
                            $fail('La persona seleccionada ya tiene un usuario asociado.');
                        }
                    }
                },
            ],
            'email' => [
                'sometimes',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'sometimes',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'password' => 'sometimes|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password|string',
            'activo' => 'sometimes|boolean',
        ];
    }
}
