<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SwitchBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sucursal_id' => 'required|integer|exists:sucursales,id'
        ];
    }

    public function messages(): array
    {
        return [
            'sucursal_id.required' => 'Debes especificar una sucursal',
            'sucursal_id.exists' => 'La sucursal no existe'
        ];
    }
}