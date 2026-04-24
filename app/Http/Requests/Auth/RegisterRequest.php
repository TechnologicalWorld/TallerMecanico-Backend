<?php

namespace App\Http\Requests\Auth;

use App\Enums\TipoPersonaEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            
            'identificacion_principal' => [
                'required',
                'string',
                'max:100',
                Rule::unique('personas', 'identificacion_principal')
            ],
            'fecha_nacimiento' => 'nullable|date|before:today',
            'genero' => 'nullable|string|max:20',
            
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
            ],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
            
            'sucursal_codigo' => 'nullable|string|exists:sucursales,codigo',
            
            'terms_accepted' => 'required|accepted'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required_if' => 'El nombre es obligatorio para persona física',
            'apellido.required_if' => 'El apellido es obligatorio para persona física',
            'razon_social.required_if' => 'La razón social es obligatoria para persona moral',
            
            'identificacion_principal.unique' => 'Esta identificación ya está registrada',
            'email.unique' => 'Este email ya está registrado',
            'username.unique' => 'Este nombre de usuario ya está en uso',
            'username.alpha_dash' => 'El username solo puede contener letras, números, guiones y guiones bajos',
            
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            
            'terms_accepted.required' => 'Debes aceptar los términos y condiciones',
            'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge([
                'username' => trim($this->username)
            ]);
        }
    }
}