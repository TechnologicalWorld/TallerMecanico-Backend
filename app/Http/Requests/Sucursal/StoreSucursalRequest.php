<?php

namespace App\Http\Requests\Sucursal;

use Illuminate\Foundation\Http\FormRequest;

class StoreSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'codigo' => 'required|string|max:50|unique:sucursales,codigo',
            'email' => 'nullable|email|max:150',
            'descripcion' => 'nullable|string|max:255',
            'horario_apertura' => 'nullable|date_format:H:i',
            'horario_cierre' => 'nullable|date_format:H:i|after:horario_apertura',
            'direccion' => 'nullable|string|max:255',
            'activa' => 'sometimes|boolean',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sucursal es requerido',
            'codigo.required' => 'El código de la sucursal es requerido',
            'codigo.unique' => 'Este código ya está en uso',
            'horario_cierre.after' => 'El horario de cierre debe ser posterior al de apertura',
            'logo.image' => 'El archivo debe ser una imagen',
            'logo.max' => 'La imagen no puede ser mayor a 2MB',
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
