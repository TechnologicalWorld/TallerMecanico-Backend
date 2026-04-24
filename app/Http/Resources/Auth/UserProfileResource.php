<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Persona\PersonaResource;
use App\Http\Resources\Sucursal\SucursalDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sucursalActual = $this->currentBranch ?? 
                         $this->sucursales()->wherePivot('activo', true)->first();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'activo' => $this->activo,
            'persona' => new PersonaResource($this->whenLoaded('persona')),
            'roles' => $this->getRoleNames(),
            'permisos' => $this->getAllPermissions()->pluck('name'),
            'contexto' => [
                'tipo' => $sucursalActual ? 'business' : 'system',
                'business_actual' => $sucursalActual ? new SucursalDetailResource($sucursalActual) : null,
                'business_ids' => $this->sucursales()
                    ->wherePivot('activo', true)
                    ->pluck('sucursales.id')
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}