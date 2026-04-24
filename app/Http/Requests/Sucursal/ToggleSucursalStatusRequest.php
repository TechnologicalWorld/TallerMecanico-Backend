<?php

namespace App\Http\Requests\Sucursal;

use Illuminate\Foundation\Http\FormRequest;

class ToggleSucursalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activa' => 'required|boolean',
            'motivo' => 'nullable|string|max:255',
        ];
    }
}