<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ToggleUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activo' => 'required|boolean',
            'motivo' => 'nullable|string|max:255',
        ];
    }
}