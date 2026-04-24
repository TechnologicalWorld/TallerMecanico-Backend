<?php

namespace App\Http\Requests\Domicilio;

use App\Enums\TipoDomicilioEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDomicilioRequest extends FormRequest
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
            'tipo' => ['nullable', new Enum(TipoDomicilioEnum::class)],
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'direccion' => 'required|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'principal' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'direccion.required' => 'La dirección es requerida',
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