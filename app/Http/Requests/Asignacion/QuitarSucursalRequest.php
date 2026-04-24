<?php

namespace App\Http\Requests\Asignacion;

use Illuminate\Foundation\Http\FormRequest;

class AsignarSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => 'required|integer|exists:users,id',
            'sucursal_id' => 'required|integer|exists:sucursales,id',
        ];
    }

    public function messages(): array
    {
        return [
            'usuario_id.required' => 'El usuario es requerido',
            'usuario_id.exists' => 'El usuario no existe',
            'sucursal_id.required' => 'La sucursal es requerida',
            'sucursal_id.exists' => 'La sucursal no existe',
        ];
    }
}