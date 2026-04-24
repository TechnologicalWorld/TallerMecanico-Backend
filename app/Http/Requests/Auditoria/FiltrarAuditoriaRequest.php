<?php

namespace App\Http\Requests\Auditoria;

use Illuminate\Foundation\Http\FormRequest;

class FiltrarAuditoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => 'nullable|integer|exists:users,id',
            'accion' => 'nullable|string|max:100',
            'entidad_type' => 'nullable|string|max:100',
            'entidad_id' => 'nullable|integer',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'busqueda' => 'nullable|string|max:255',
            'order_by' => 'nullable|string|in:id,created_at,accion,usuario_id',
            'order_dir' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio',
        ];
    }
}