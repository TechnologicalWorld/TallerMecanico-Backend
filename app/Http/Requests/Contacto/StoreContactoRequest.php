<?php

namespace App\Http\Requests\Contacto;

use App\Enums\TipoContactoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreContactoRequest extends FormRequest
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
            'tipo' => ['required', new Enum(TipoContactoEnum::class)],
            'valor' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'entidad_type.required' => 'El tipo de entidad es requerido',
            'entidad_id.required' => 'El ID de la entidad es requerido',
            'tipo.required' => 'El tipo de contacto es requerido',
            'valor.required' => 'El valor del contacto es requerido',
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