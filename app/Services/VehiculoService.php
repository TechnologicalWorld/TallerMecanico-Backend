<?php

namespace App\Services;

use App\Models\Vehiculo;
use App\Models\Auditoria;
use App\Enums\VehiculoEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class VehiculoService
{
    /**
     * Listado de vehículos con filtros avanzados
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Vehiculo::with(['cliente.persona', 'sucursal']);

        // Filtro por sucursal (Multi-tenancy)
        if (!empty($filtros['sucursal_id'])) {
            $query->where('sucursal_id', $filtros['sucursal_id']);
        }

        // Filtro por cliente específico
        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        // Filtro por estado del enum
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        // Búsqueda global (Placa, Marca, Modelo o Nombre del Cliente)
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->where('placa', 'like', "%{$busqueda}%")
                  ->orWhere('marca', 'like', "%{$busqueda}%")
                  ->orWhere('modelo', 'like', "%{$busqueda}%")
                  ->orWhereHas('cliente.persona', function ($pq) use ($busqueda) {
                      $pq->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellido', 'like', "%{$busqueda}%")
                        ->orWhere('razon_social', 'like', "%{$busqueda}%");
                  });
            });
        }

        $orderBy = $filtros['order_by'] ?? 'created_at';
        $orderDir = $filtros['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Registrar un nuevo vehículo en el taller
     */
    public function crear(array $data, int $usuarioId): Vehiculo
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $vehiculo = Vehiculo::create($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'VEHICULO_CREADO',
                'entidad_type' => Vehiculo::class,
                'entidad_id' => $vehiculo->id,
                'valores_nuevos' => $data
            ]);

            return $vehiculo;
        });
    }

    /**
     * Obtener detalle completo del vehículo
     */
    public function obtenerPorId(int $id): Vehiculo
    {
        return Vehiculo::with(['cliente.persona', 'sucursal'])->findOrFail($id);
    }

    /**
     * Actualizar datos técnicos o propietario
     */
    public function actualizar(int $id, array $data, int $usuarioId): Vehiculo
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $vehiculo = Vehiculo::findOrFail($id);
            $valoresAnteriores = $vehiculo->toArray();

            $vehiculo->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'VEHICULO_ACTUALIZADO',
                'entidad_type' => Vehiculo::class,
                'entidad_id' => $vehiculo->id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data
            ]);

            return $vehiculo->fresh();
        });
    }

    /**
     * Cambiar estado del vehículo (Activo/Inactivo)
     */
    public function cambiarEstado(int $id, string $estado, int $usuarioId): Vehiculo
    {
        return DB::transaction(function () use ($id, $estado, $usuarioId) {
            $vehiculo = Vehiculo::findOrFail($id);
            $estadoAnterior = $vehiculo->estado->value;

            $vehiculo->estado = $estado;
            $vehiculo->save();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'VEHICULO_ESTADO_CAMBIADO',
                'entidad_type' => Vehiculo::class,
                'entidad_id' => $vehiculo->id,
                'valores_anteriores' => ['estado' => $estadoAnterior],
                'valores_nuevos' => ['estado' => $estado]
            ]);

            return $vehiculo;
        });
    }

    /**
     * Eliminar vehículo (Soft Delete)
     */
    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $vehiculo = Vehiculo::findOrFail($id);
            $vehiculo->delete();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'VEHICULO_ELIMINADO',
                'entidad_type' => Vehiculo::class,
                'entidad_id' => $id
            ]);

            return true;
        });
    }

    /**
     * Restaurar un vehículo eliminado lógicamente
     */
    public function restaurar(int $id, int $usuarioId): Vehiculo
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            // Usamos onlyTrashed() para encontrar registros con deleted_at
            $vehiculo = Vehiculo::onlyTrashed()->findOrFail($id);
            
            $vehiculo->restore();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'VEHICULO_RESTAURADO',
                'entidad_type' => Vehiculo::class,
                'entidad_id' => $vehiculo->id,
                'valores_nuevos' => ['restaurado_at' => now()]
            ]);

            return $vehiculo;
        });
    }
}