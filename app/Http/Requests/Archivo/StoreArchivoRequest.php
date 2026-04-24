<?php

namespace App\Http\Requests\Archivo;

use App\Enums\TipoArchivoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entidad_type' => 'required|string|in:App\Models\Persona,App\Models\Sucursal',
            'entidad_id' => 'required|integer',
            'tipo' => ['required', new Enum(TipoArchivoEnum::class)],
            'archivo' => 'required|file|max:10240',
            'nombre' => 'nullable|string|max:255',
            'fecha_expiracion' => 'nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debes seleccionar un archivo',
            'archivo.max' => 'El archivo no puede ser mayor a 10MB',
            'fecha_expiracion.after' => 'La fecha de expiración debe ser futura',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('persona_id')) {
            $this->merge([
                'entidad_type' => 'App\Models\Persona',
                'entidad_id' => $this->persona_id,
            ]);
        } elseif ($this->has('sucursal_id')) {
            $this->merge([
                'entidad_type' => 'App\Models\Sucursal',
                'entidad_id' => $this->sucursal_id,
            ]);
        }
    }
}