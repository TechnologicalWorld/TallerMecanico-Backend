<?php
// app/Http/Resources/Vehiculo/VehiculoResource.php
namespace App\Http\Resources\Vehiculo;

use App\Http\Resources\Cliente\ClienteListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculoListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio,
            'tipo' => $this->tipo,
            'estado' => $this->estado->value,
            'estado_texto' => $this->estado->label(),
            
            // Reutilizamos el listado de cliente para ver quién es el dueño
            'propietario' => new ClienteListResource($this->whenLoaded('cliente')),
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}