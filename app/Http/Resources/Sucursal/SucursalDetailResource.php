<?php

namespace App\Http\Resources\Sucursal;

use App\Http\Resources\User\UserListResource;
use App\Http\Resources\ContactoResource;
use App\Http\Resources\DomicilioResource;
use App\Http\Resources\ArchivoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SucursalDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'activa' => $this->activa,
            'email' => $this->email,
            'descripcion' => $this->descripcion,
            'horario_apertura' => $this->horario_apertura?->format('H:i'),
            'horario_cierre' => $this->horario_cierre?->format('H:i'),
            'horario_completo' => $this->horario_formateado,
            'direccion' => $this->direccion,
            'logo' => $this->logo_path ? asset('storage/' . $this->logo_path) : null,
            'logo_path' => $this->logo_path,
            'usuarios' => UserListResource::collection($this->whenLoaded('usuarios')),
            'usuarios_count' => $this->whenCounted('usuarios', $this->usuarios_count),
            'administradores' => $this->whenLoaded('usuarios', function() {
                return $this->usuarios->filter(fn($u) => $u->pivot->es_administrador)->values();
            }),
            'contactos' => ContactoResource::collection($this->whenLoaded('contactos')),
            'domicilios' => DomicilioResource::collection($this->whenLoaded('domicilios')),
            'archivos' => ArchivoResource::collection($this->whenLoaded('archivos')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}