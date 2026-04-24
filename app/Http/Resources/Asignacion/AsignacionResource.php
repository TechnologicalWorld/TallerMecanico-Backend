<?php

namespace App\Http\Resources\Asignacion;

use App\Http\Resources\User\UserListResource;
use App\Http\Resources\Sucursal\SucursalListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsignacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario' => new UserListResource($this->whenLoaded('usuario')),
            'sucursal' => new SucursalListResource($this->whenLoaded('sucursal')),
            'es_administrador' => $this->es_administrador,
            'activo' => $this->activo,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_humano' => $this->created_at?->diffForHumans(),
        ];
    }
}