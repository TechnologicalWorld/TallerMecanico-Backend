<?php

namespace App\Http\Requests\Domicilio;

use App\Enums\TipoDomicilioEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDomicilioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['nullable', new Enum(TipoDomicilioEnum::class)],
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'direccion' => 'sometimes|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'principal' => 'sometimes|boolean',
        ];
    }
}