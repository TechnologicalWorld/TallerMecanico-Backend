<?php

namespace App\Http\Resources\Persona;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonaMoralResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'razon_social' => $this->razon_social,
            'identificacion_principal' => $this->identificacion_principal,
            'fecha_constitucion' => $this->fecha_nacimiento?->format('Y-m-d'),
            'foto' => $this->foto_path ? asset('storage/' . $this->foto_path) : null,
            'estado' => $this->estado,
        ];
    }
}