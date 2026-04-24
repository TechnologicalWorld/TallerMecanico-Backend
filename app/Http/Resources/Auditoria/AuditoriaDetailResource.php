<?php

namespace App\Http\Resources\Auditoria;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditoriaDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario' => $this->whenLoaded('usuario', function() {
                return [
                    'id' => $this->usuario?->id,
                    'username' => $this->usuario?->username,
                    'email' => $this->usuario?->email,
                    'nombre' => $this->usuario?->persona?->nombre_completo ?? $this->usuario?->username,
                ];
            }),
            'accion' => $this->accion,
            'entidad_type' => $this->entidad_type,
            'entidad_nombre' => class_basename($this->entidad_type),
            'entidad_id' => $this->entidad_id,
            'valores_anteriores' => $this->formatValues($this->valores_anteriores),
            'valores_nuevos' => $this->formatValues($this->valores_nuevos),
            'diferencias' => $this->getDiferencias(),
            'ip' => $this->valores_nuevos['ip'] ?? $this->valores_anteriores['ip'] ?? null,
            'user_agent' => $this->valores_nuevos['user_agent'] ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_humano' => $this->created_at?->diffForHumans(),
            'fecha_completa' => $this->created_at?->format('d/m/Y H:i:s'),
        ];
    }

    private function formatValues($values): ?array
    {
        if (!$values) {
            return null;
        }

        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        $sensibles = ['password', 'password_confirmation', 'remember_token'];
        foreach ($sensibles as $campo) {
            if (isset($values[$campo])) {
                $values[$campo] = '********';
            }
        }

        return $values;
    }

    private function getDiferencias(): array
    {
        $anteriores = $this->formatValues($this->valores_anteriores) ?? [];
        $nuevos = $this->formatValues($this->valores_nuevos) ?? [];
        
        $diferencias = [];
        
        foreach ($nuevos as $key => $value) {
            if (isset($anteriores[$key]) && $anteriores[$key] != $value) {
                $diferencias[$key] = [
                    'anterior' => $this->formatValue($anteriores[$key]),
                    'nuevo' => $this->formatValue($value),
                ];
            } elseif (!isset($anteriores[$key])) {
                $diferencias[$key] = [
                    'anterior' => null,
                    'nuevo' => $this->formatValue($value),
                ];
            }
        }
        
        foreach ($anteriores as $key => $value) {
            if (!isset($nuevos[$key])) {
                $diferencias[$key] = [
                    'anterior' => $this->formatValue($value),
                    'nuevo' => null,
                ];
            }
        }
        
        return $diferencias;
    }

    private function formatValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }
        
        return $value;
    }
}