<?php

namespace App\Http\Resources\Cliente;

use App\Http\Resources\Persona\PersonaListResource; 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // Atributos propios de la tabla Clientes
            'id'             => $this->id,
            'codigo_cliente' => $this->codigo_cliente,
            'activo'         => (bool) $this->activo,
            
            // Reutilización del Resource de la plantilla
            'persona'        => new PersonaListResource($this->whenLoaded('persona')),
            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}