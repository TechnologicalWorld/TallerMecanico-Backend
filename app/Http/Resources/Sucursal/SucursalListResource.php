<?php

namespace App\Http\Resources\Sucursal;

use App\Http\Resources\User\UserListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SucursalListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'activa' => $this->activa,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'horario' => $this->horario_formateado,
            'logo' => $this->logo_path ? asset('storage/' . $this->logo_path) : null,
            'usuarios_count' => $this->whenCounted('usuarios', $this->usuarios_count),
            'administradores_count' => $this->whenLoaded('usuarios', function() {
                return $this->usuarios->filter(fn($u) => $u->pivot->es_administrador)->count();
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_humano' => $this->created_at?->diffForHumans(),
        ];
    }
}