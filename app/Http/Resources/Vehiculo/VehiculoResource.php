<?php
// app/Http/Resources/Vehiculo/VehiculoResource.php

namespace App\Http\Resources\Vehiculo;

use App\Http\Resources\Cliente\ClienteResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'vin' => $this->vin,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio,
            'color' => $this->color,
            'tipo' => $this->tipo,
            'kilometraje' => $this->kilometraje,
            'estado' => $this->estado->value,
            'estado_texto' => $this->estado->label(),
            
            // Información completa del dueño (incluyendo datos de persona)
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            
            // Información de la sucursal
            'sucursal' => $this->whenLoaded('sucursal'),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}