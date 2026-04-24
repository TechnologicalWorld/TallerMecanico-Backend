<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('id');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId)
            ],
            'guard_name' => 'sometimes|string|in:web,api',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permisos,id',
        ];
    }
}