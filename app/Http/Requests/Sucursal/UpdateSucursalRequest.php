<?php

namespace App\Http\Requests\Sucursal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sucursalId = $this->route('id');

        return [
            'nombre' => 'sometimes|string|max:150',
            'codigo' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('sucursales', 'codigo')->ignore($sucursalId),
            ],
            'email' => 'nullable|email|max:150',
            'descripcion' => 'nullable|string|max:255',
            'horario_apertura' => 'nullable|date_format:H:i',
            'horario_cierre' => 'nullable|date_format:H:i|after:horario_apertura',
            'direccion' => 'nullable|string|max:255',
            'activa' => 'sometimes|boolean',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    protected function prepareForValidation(): void
    {

        if ($this->has('activa')) {
            $activa = $this->input('activa');
            if (is_string($activa)) {
                $activa = filter_var($activa, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
            if (! is_null($activa)) {
                $this->merge([
                    'activa' => $activa,
                ]);
            }
        }
        if ($this->has('horario_apertura') && empty($this->horario_apertura)) {
            $this->merge(['horario_apertura' => null]);
        }

        if ($this->has('horario_cierre') && empty($this->horario_cierre)) {
            $this->merge(['horario_cierre' => null]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();
        if (isset($validated['activa'])) {
            $validated['activa'] = (bool) $validated['activa'];
        }

        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
