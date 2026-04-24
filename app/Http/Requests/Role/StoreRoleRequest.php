<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'sometimes|string|in:web,api',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permisos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es requerido',
            'name.unique' => 'Ya existe un rol con ese nombre',
        ];
    }
}