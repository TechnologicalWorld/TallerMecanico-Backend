<?php

namespace App\Http\Resources\Role;

use App\Http\Resources\Permission\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sucursal_actual' => getPermissionsTeamId(),
            'guard_name' => $this->guard_name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'users_count' => $this->whenCounted('users', $this->users_count),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
        ];
    }
}