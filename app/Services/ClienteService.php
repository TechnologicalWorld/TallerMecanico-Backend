<?php
// app/Services/ClienteService.php
namespace App\Services;

use App\Models\Cliente;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ClienteService
{
    /**
     * Listado con filtros, uniendo con la tabla personas
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Cliente::with(['persona']);

        // Filtro por estado activo/inactivo del cliente
        if (isset($filtros['activo'])) {
            $query->where('activo', $filtros['activo']);
        }

        // Búsqueda en la tabla relacionada 'personas'
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $query->whereHas('persona', function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('apellido', 'like', "%{$busqueda}%")
                  ->orWhere('razon_social', 'like', "%{$busqueda}%")
                  ->orWhere('identificacion_principal', 'like', "%{$busqueda}%");
            })->orWhere('codigo_cliente', 'like', "%{$busqueda}%");
        }

        $orderBy = $filtros['order_by'] ?? 'created_at';
        $orderDir = $filtros['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Crear nuevo cliente
     */
    public function crear(array $data, int $usuarioId): Cliente
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $cliente = Cliente::create($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CLIENTE_CREADO',
                'entidad_type' => Cliente::class,
                'entidad_id' => $cliente->id,
                'valores_nuevos' => $data
            ]);

            return $cliente;
        });
    }

    /**
     * Obtener cliente con su persona y relaciones
     */
    public function obtenerConRelaciones(int $id): Cliente
    {
        return Cliente::with(['persona.contactos', 'persona.domicilios', 'vehiculos'])
            ->findOrFail($id);
    }

    /**
     * Actualizar cliente
     */
    public function actualizar(int $id, array $data, int $usuarioId): Cliente
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $cliente = Cliente::findOrFail($id);
            $valoresAnteriores = $cliente->toArray();

            $cliente->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CLIENTE_ACTUALIZADO',
                'entidad_type' => Cliente::class,
                'entidad_id' => $cliente->id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data
            ]);

            return $cliente->fresh();
        });
    }

    /**
     * Cambiar estado de activación del cliente
     */
    public function cambiarEstado(int $id, bool $activo, int $usuarioId): Cliente
    {
        return DB::transaction(function () use ($id, $activo, $usuarioId) {
            $cliente = Cliente::findOrFail($id);
            $estadoAnterior = $cliente->activo;

            $cliente->activo = $activo;
            $cliente->save();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CLIENTE_ESTADO_CAMBIADO',
                'entidad_type' => Cliente::class,
                'entidad_id' => $cliente->id,
                'valores_anteriores' => ['activo' => $estadoAnterior],
                'valores_nuevos' => ['activo' => $activo]
            ]);

            return $cliente;
        });
    }

    /**
     * Eliminar (soft delete)
     */
    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $cliente = Cliente::findOrFail($id);
            
            // Lógica de no eliminar si tiene vehículos o servicios pendientes
            if ($cliente->vehiculos()->count() > 0) {
                throw new \Exception('No se puede eliminar un cliente que tiene vehículos registrados.');
            }

            $cliente->delete();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CLIENTE_ELIMINADO',
                'entidad_type' => Cliente::class,
                'entidad_id' => $id
            ]);

            return true;
        });
    }

    /**
     * Restaurar (soft delete)
     */
    public function restaurar(int $id, int $usuarioId): Cliente
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $cliente = Cliente::withTrashed()->findOrFail($id);
            $cliente->restore();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CLIENTE_RESTAURADO',
                'entidad_type' => Cliente::class,
                'entidad_id' => $id
            ]);

            return $cliente;
        });
    }
}