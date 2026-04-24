<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'metrics' => [
                'personas' => $this->resource['personas'],
                'usuarios' => $this->resource['usuarios'],
                'sucursales' => $this->resource['sucursales'],
                'sesiones' => $this->resource['sesiones'],
                'documentos' => $this->resource['documentos']
            ],
            'actividad_reciente' => $this->resource['actividad_reciente'],
            'alertas' => $this->getAlertas($this->resource),
            'fecha_actualizacion' => now()->format('Y-m-d H:i:s')
        ];
    }

    private function getAlertas($data): array
    {
        $alertas = [];

        if (($data['documentos']['vencidos']['total'] ?? 0) > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'mensaje' => "Hay {$data['documentos']['vencidos']['total']} documentos vencidos",
                'icono' => 'alert-triangle'
            ];
        }

        if (($data['documentos']['por_vencer']['total'] ?? 0) > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "{$data['documentos']['por_vencer']['total']} documentos por vencer en 30 días",
                'icono' => 'clock'
            ];
        }

        // Alertas de usuarios sin sucursal
        if (($data['usuarios']['sin_sucursal'] ?? 0) > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => "{$data['usuarios']['sin_sucursal']} usuarios sin sucursal asignada",
                'icono' => 'users'
            ];
        }

        return $alertas;
    }
}