<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomicilioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'tipo_texto' => $this->tipo?->label(),
            'pais' => $this->pais,
            'ciudad' => $this->ciudad,
            'direccion' => $this->direccion,
            'codigo_postal' => $this->codigo_postal,
            'principal' => $this->principal,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}