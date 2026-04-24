<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'modulo' => explode('.', $this->name)[0] ?? 'general',
            'accion' => explode('.', $this->name)[1] ?? $this->name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}