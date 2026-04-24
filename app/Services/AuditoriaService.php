<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditoriaService
{
    /**
     * Listar logs con filtros
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Auditoria::with('usuario.persona');

        if (!empty($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        if (!empty($filtros['accion'])) {
            $query->where('accion', $filtros['accion']);
        }

        if (!empty($filtros['entidad_type'])) {
            $query->where('entidad_type', $filtros['entidad_type']);
        }

        if (!empty($filtros['entidad_id'])) {
            $query->where('entidad_id', $filtros['entidad_id']);
        }

        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }

        if (!empty($filtros['busqueda'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('accion', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('entidad_type', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('valores_anteriores', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('valores_nuevos', 'like', "%{$filtros['busqueda']}%")
                    ->orWhereHas('usuario', function ($user) use ($filtros) {
                        $user->where('username', 'like', "%{$filtros['busqueda']}%")
                            ->orWhere('email', 'like', "%{$filtros['busqueda']}%")
                            ->orWhereHas('persona', function ($persona) use ($filtros) {
                                $persona->where('nombre', 'like', "%{$filtros['busqueda']}%")
                                    ->orWhere('apellido', 'like', "%{$filtros['busqueda']}%")
                                    ->orWhere('razon_social', 'like', "%{$filtros['busqueda']}%");
                            });
                    });
            });
        }

        $orderBy = $filtros['order_by'] ?? 'created_at';
        $orderDir = $filtros['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Obtener detalle de un evento
     */
    public function obtenerEvento(int $id): Auditoria
    {
        return Auditoria::with('usuario.persona')->findOrFail($id);
    }
    
    /**
     * Exportar logs (para generar reportes)
     */
    public function exportar(array $filtros = []): array
    {
        $logs = $this->listar($filtros, 1000);

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'fecha' => $log->created_at->format('Y-m-d H:i:s'),
                'usuario' => $log->usuario?->persona?->nombre_completo ?? $log->usuario?->username,
                'email' => $log->usuario?->email,
                'accion' => $log->accion,
                'entidad' => class_basename($log->entidad_type),
                'entidad_id' => $log->entidad_id,
                'ip' => $log->valores_nuevos['ip'] ?? null,
                'cambios' => $this->resumirCambios($log),
            ];
        })->toArray();
    }

    private function resumirCambios(Auditoria $log): string
    {
        $anteriores = $log->valores_anteriores ?? [];
        $nuevos = $log->valores_nuevos ?? [];

        if (empty($anteriores) && empty($nuevos)) {
            return 'Sin cambios registrados';
        }

        if (empty($anteriores)) {
            $campos = array_keys($nuevos);
            return "Creación: " . implode(', ', array_slice($campos, 0, 3));
        }

        $cambios = [];
        foreach ($nuevos as $key => $value) {
            if (isset($anteriores[$key]) && $anteriores[$key] != $value) {
                $cambios[] = $key;
            }
        }

        if (empty($cambios)) {
            return 'Sin cambios detectados';
        }

        return "Modificó: " . implode(', ', array_slice($cambios, 0, 5));
    }
}
