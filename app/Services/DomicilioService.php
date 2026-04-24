<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Domicilio;
use Illuminate\Support\Facades\DB;

class DomicilioService
{
    /**
     * Listar domicilios por entidad
     */
    public function listarPorEntidad(string $entidadType, int $entidadId)
    {
        return Domicilio::where('entidad_type', $entidadType)
            ->where('entidad_id', $entidadId)
            ->orderBy('principal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Crear domicilio
     */
    public function crear(array $data, int $usuarioId): Domicilio
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            if (! empty($data['principal']) && $data['principal']) {
                Domicilio::where('entidad_type', $data['entidad_type'])
                    ->where('entidad_id', $data['entidad_id'])
                    ->update(['principal' => false]);
            }

            $domicilio = Domicilio::create($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'DOMICILIO_CREADO',
                'entidad_type' => $domicilio->entidad_type,
                'entidad_id' => $domicilio->entidad_id,
                'valores_nuevos' => [
                    'domicilio_id' => $domicilio->id,
                    'direccion' => $domicilio->direccion,
                    'principal' => $domicilio->principal,
                ],
            ]);

            return $domicilio;
        });
    }

    /**
     * Actualizar domicilio
     */
    public function actualizar(int $id, array $data, int $usuarioId): Domicilio
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $domicilio = Domicilio::findOrFail($id);

            if (isset($data['principal']) && $data['principal'] && ! $domicilio->principal) {
                Domicilio::where('entidad_type', $domicilio->entidad_type)
                    ->where('entidad_id', $domicilio->entidad_id)
                    ->where('id', '!=', $id)
                    ->update(['principal' => false]);
            }

            $valoresAnteriores = $domicilio->toArray();
            $domicilio->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'DOMICILIO_ACTUALIZADO',
                'entidad_type' => $domicilio->entidad_type,
                'entidad_id' => $domicilio->entidad_id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $domicilio->toArray(),
            ]);

            return $domicilio;
        });
    }

    /**
     * Eliminar domicilio (lógico)
     */
    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $domicilio = Domicilio::findOrFail($id);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'DOMICILIO_ELIMINADO',
                'entidad_type' => $domicilio->entidad_type,
                'entidad_id' => $domicilio->entidad_id,
                'valores_anteriores' => $domicilio->toArray(),
            ]);

            return $domicilio->delete();
        });
    }

    /**
     * Restaurar domicilio eliminado
     */
    public function restaurar(int $id, int $usuarioId): Domicilio
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $domicilio = Domicilio::withTrashed()->findOrFail($id);
            $domicilio->restore();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'DOMICILIO_RESTAURADO',
                'entidad_type' => $domicilio->entidad_type,
                'entidad_id' => $domicilio->entidad_id,
                'valores_nuevos' => $domicilio->toArray(),
            ]);

            return $domicilio;
        });
    }

    /**
     * Obtener domicilio principal de una entidad
     */
    public function obtenerPrincipal(string $entidadType, int $entidadId): ?Domicilio
    {
        return Domicilio::where('entidad_type', $entidadType)
            ->where('entidad_id', $entidadId)
            ->where('principal', true)
            ->first();
    }
}
