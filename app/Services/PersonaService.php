<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PersonaService
{
    /**
     * Listado con filtros
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Persona::with(['user']);        
        if (!empty($filtros['tipo_persona'])) {
            $query->where('tipo_persona', $filtros['tipo_persona']);
        }        
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }        
        if (!empty($filtros['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }        
        if (!empty($filtros['busqueda'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('nombre', 'like', "%{$filtros['busqueda']}%")
                  ->orWhere('apellido', 'like', "%{$filtros['busqueda']}%")
                  ->orWhere('razon_social', 'like', "%{$filtros['busqueda']}%")
                  ->orWhere('identificacion_principal', 'like', "%{$filtros['busqueda']}%");
            });
        }        
        $orderBy = $filtros['order_by'] ?? 'created_at';
        $orderDir = $filtros['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Listar solo personas físicas
     */
    public function listarFisicas(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $filtros['tipo_persona'] = 'FISICA';
        return $this->listar($filtros, $perPage);
    }

    /**
     * Listar solo personas morales
     */
    public function listarMorales(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $filtros['tipo_persona'] = 'MORAL';
        return $this->listar($filtros, $perPage);
    }

    /**
     * Crear nueva persona
     */
    public function crear(array $data, int $usuarioId): Persona
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $persona = Persona::create($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERSONA_CREADA',
                'entidad_type' => Persona::class,
                'entidad_id' => $persona->id,
                'valores_nuevos' => $data
            ]);

            return $persona;
        });
    }

    /**
     * Obtener persona con todas sus relaciones
     */
    public function obtenerConRelaciones(int $id): Persona
    {
        return Persona::with([
            'user',
            'contactos',
            'domicilios',
            'archivos'
        ])->findOrFail($id);
    }

    /**
     * Actualizar persona
     */
    public function actualizar(int $id, array $data, int $usuarioId): Persona
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $persona = Persona::findOrFail($id);
            $valoresAnteriores = $persona->toArray();

            $persona->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERSONA_ACTUALIZADA',
                'entidad_type' => Persona::class,
                'entidad_id' => $persona->id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data
            ]);

            return $persona->fresh();
        });
    }

    /**
     * Cambiar estado
     */
    public function cambiarEstado(int $id, string $estado, ?string $motivo, int $usuarioId): Persona
    {
        return DB::transaction(function () use ($id, $estado, $motivo, $usuarioId) {
            $persona = Persona::findOrFail($id);
            $estadoAnterior = $persona->estado;

            $persona->estado = $estado;
            $persona->save();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERSONA_ESTADO_CAMBIADO',
                'entidad_type' => Persona::class,
                'entidad_id' => $persona->id,
                'valores_anteriores' => ['estado' => $estadoAnterior],
                'valores_nuevos' => ['estado' => $estado, 'motivo' => $motivo]
            ]);

            return $persona;
        });
    }

    /**
     * Eliminar (soft delete)
     */
    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $persona = Persona::findOrFail($id);
                    
            if ($persona->user) {
                throw new \Exception('No se puede eliminar una persona con usuario asociado');
            }

            $persona->delete();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERSONA_ELIMINADA',
                'entidad_type' => Persona::class,
                'entidad_id' => $id
            ]);

            return true;
        });
    }

    /**
     * Restaurar (soft delete)
     */
    public function restaurar(int $id, int $usuarioId): Persona
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $persona = Persona::withTrashed()->findOrFail($id);
            $persona->restore();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERSONA_RESTAURADA',
                'entidad_type' => Persona::class,
                'entidad_id' => $id
            ]);

            return $persona;
        });
    }
}