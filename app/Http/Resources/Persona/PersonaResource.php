<?php

namespace App\Http\Resources\Persona;

use App\Http\Resources\ContactoResource;
use App\Http\Resources\DomicilioResource;
use App\Http\Resources\ArchivoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'tipo_persona' => $this->tipo_persona,
            'tipo_texto' => $this->tipo_persona?->label(),
            'identificacion_principal' => $this->identificacion_principal,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'genero' => $this->genero,
            'foto' => $this->foto_path ? asset('storage/' . $this->foto_path) : null,
            'foto_path' => $this->foto_path,
            'estado' => $this->estado,
            'estado_texto' => $this->estado?->label(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            'usuario' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'activo' => $this->user->activo
            ]),
            
            'contactos' => ContactoResource::collection($this->whenLoaded('contactos')),
            'domicilios' => DomicilioResource::collection($this->whenLoaded('domicilios')),
            'archivos' => ArchivoResource::collection($this->whenLoaded('archivos')),
        ];

        if ($this->tipo_persona?->value === 'FISICA') {
            $data['nombre'] = $this->nombre;
            $data['apellido'] = $this->apellido;
            $data['nombre_completo'] = $this->nombre_completo;
        } else {
            $data['razon_social'] = $this->razon_social;
        }

        return $data;
    }
}