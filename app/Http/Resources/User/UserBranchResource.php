<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot;
        
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'es_administrador' => $pivot?->es_administrador ?? false,
            'activa' => $this->activa,
            'asignacion_activa' => $pivot?->activo ?? false,
            'es_actual' => $request->user()->current_branch_id === $this->id,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'horario' => $this->horario_formateado,
            'logo' => $this->logo_path ? asset('storage/' . $this->logo_path) : null
        ];
    }
}