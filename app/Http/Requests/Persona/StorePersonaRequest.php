<?php

namespace App\Http\Requests\Persona;

use App\Enums\TipoPersonaEnum;
use App\Enums\EstadoPersonaEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_persona' => ['required', new Enum(TipoPersonaEnum::class)],                        
            'nombre' => 'required_if:tipo_persona,FISICA|string|max:150|nullable',
            'apellido' => 'required_if:tipo_persona,FISICA|string|max:150|nullable',                        
            'razon_social' => 'required_if:tipo_persona,MORAL|string|max:255|nullable',
            
            'identificacion_principal' => 'required|string|max:100|unique:personas,identificacion_principal',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'genero' => 'nullable|string|max:20',
            'foto_path' => 'nullable|string|max:255',
            'estado' => ['sometimes', new Enum(EstadoPersonaEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required_if' => 'El nombre es obligatorio para persona física',
            'apellido.required_if' => 'El apellido es obligatorio para persona física',
            'razon_social.required_if' => 'La razón social es obligatoria para persona moral',
            'identificacion_principal.unique' => 'Esta identificación ya está registrada',
        ];
    }
}