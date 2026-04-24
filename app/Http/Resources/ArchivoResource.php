<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchivoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'ruta' => $this->ruta,
            'url' => $this->ruta ? asset('storage/' . $this->ruta) : null,
            'tipo' => $this->tipo,
            'tipo_texto' => $this->tipo?->label() ?? $this->tipo,
            'fecha_expiracion' => $this->fecha_expiracion?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}