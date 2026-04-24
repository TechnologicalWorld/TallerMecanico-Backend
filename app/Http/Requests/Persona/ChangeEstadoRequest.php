<?php

namespace App\Http\Requests\Persona;

use App\Enums\EstadoPersonaEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ChangeEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', new Enum(EstadoPersonaEnum::class)],
            'motivo' => 'nullable|string|max:255',
        ];
    }
}