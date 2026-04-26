<?php

namespace App\Http\Resources\Cliente;

use App\Http\Resources\Persona\PersonaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_cliente' => $this->codigo_cliente,
            'activo' => $this->activo,
            
            // Reutilizamos el resource de la plantilla
            'persona' => new PersonaResource($this->whenLoaded('persona')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}