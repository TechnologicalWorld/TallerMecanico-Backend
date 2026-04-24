<?php

namespace App\Http\Resources\Persona;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonaFisicaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'nombre_completo' => $this->nombre_completo,
            'identificacion_principal' => $this->identificacion_principal,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'genero' => $this->genero,
            'foto' => $this->foto_path ? asset('storage/' . $this->foto_path) : null,
            'estado' => $this->estado,
        ];
    }
}