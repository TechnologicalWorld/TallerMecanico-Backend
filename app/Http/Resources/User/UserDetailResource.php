<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Auth\SessionResource;
use App\Http\Resources\Persona\PersonaResource;
use App\Http\Resources\Sucursal\SucursalDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'activo' => $this->activo,
            'persona' => new PersonaResource($this->whenLoaded('persona')),
            'sucursales' => SucursalDetailResource::collection($this->whenLoaded('sucursales')),
            'sucursal_actual' => $this->whenLoaded('currentBranch', fn() => [
                'id' => $this->currentBranch->id,
                'nombre' => $this->currentBranch->nombre,
                'codigo' => $this->currentBranch->codigo,
            ]),
            'roles' => $this->getRoleNames(),
            'roles_detalle' => $this->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ]),
            'permisos' => $this->getAllPermissions()->pluck('name'),
            'sesiones_activas' => SessionResource::collection(
                $this->whenLoaded('sesiones', fn() => 
                    $this->sesiones->where('activa', true)
                )
            ),
            'sesiones_count' => $this->whenCounted('sesiones', $this->sesiones_count),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}