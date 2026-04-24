<?php

namespace App\Http\Requests\Persona;

use App\Enums\TipoPersonaEnum;
use App\Enums\EstadoPersonaEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class UpdatePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $personaId = $this->route('persona');

        return [
            'tipo_persona' => ['sometimes', new Enum(TipoPersonaEnum::class)],
            
            'nombre' => 'nullable|string|max:150',
            'apellido' => 'nullable|string|max:150',
            'razon_social' => 'nullable|string|max:255',
            
            'identificacion_principal' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('personas', 'identificacion_principal')->ignore($personaId)
            ],
            'fecha_nacimiento' => 'nullable|date|before:today',
            'genero' => 'nullable|string|max:20',
            'foto_path' => 'nullable|string|max:255',
            'estado' => ['sometimes', new Enum(EstadoPersonaEnum::class)],
        ];
    }
}