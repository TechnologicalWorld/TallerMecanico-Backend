<?php

namespace App\Http\Resources\Persona;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonaListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'foto' => $this->foto_path ? asset('storage/' . $this->foto_path) : null,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'razon_social' => $this->razon_social,
            'nombre_completo' => $this->nombre_completo,
            'display_name' => $this->display_name,
            'identificacion_principal' => $this->identificacion_principal,
            'tipo_persona' => $this->tipo_persona,
            'tipo_texto' => $this->tipo_persona?->label(),
            'estado' => $this->estado,
            'estado_texto' => $this->estado?->label(),
            'estado_color' => $this->getEstadoColor(),
            'fecha_registro' => $this->created_at?->format('Y-m-d'),
            'fecha_registro_humano' => $this->created_at?->diffForHumans(),
            'usuario_asociado' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email
            ]),
        ];
    }

    private function getEstadoColor(): string
    {
        return match($this->estado?->value) {
            'ACTIVO' => 'green',
            'INACTIVO' => 'yellow',
            'BLOQUEADO' => 'red',
            default => 'gray'
        };
    }
}