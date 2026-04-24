<?php

namespace App\Http\Requests\Contacto;

use App\Enums\TipoContactoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['sometimes', new Enum(TipoContactoEnum::class)],
            'valor' => 'sometimes|string|max:255',
        ];
    }
}