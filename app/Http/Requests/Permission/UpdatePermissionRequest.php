<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('id');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permisos', 'name')->ignore($permissionId)
            ],
            'guard_name' => 'sometimes|string|in:web,api',
        ];
    }
}