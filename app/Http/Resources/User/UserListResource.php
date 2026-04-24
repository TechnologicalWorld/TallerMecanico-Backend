<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Persona\PersonaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'activo' => $this->activo,
            'persona' => new PersonaResource($this->whenLoaded('persona')),
            'sucursales_count' => $this->whenCounted('sucursales', $this->sucursales_count),
            'ultimo_acceso' => $this->sesiones()
                ->where('activa', true)
                ->latest('login_at')
                ->first()?->login_at?->diffForHumans(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_humano' => $this->created_at?->diffForHumans(),
        ];
    }
}