<?php

namespace App\Services;

use App\Models\Contacto;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;

class ContactoService
{
    /**
     * Listar contactos por entidad
     */
    public function listarPorEntidad(string $entidadType, int $entidadId)
    {
        return Contacto::where('entidad_type', $entidadType)
                      ->where('entidad_id', $entidadId)
                      ->orderBy('tipo')
                      ->orderBy('created_at', 'desc')
                      ->get();
    }

    /**
     * Crear contacto
     */
    public function crear(array $data, int $usuarioId): Contacto
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $contacto = Contacto::create($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CONTACTO_CREADO',
                'entidad_type' => $contacto->entidad_type,
                'entidad_id' => $contacto->entidad_id,
                'valores_nuevos' => [
                    'contacto_id' => $contacto->id,
                    'tipo' => $contacto->tipo,
                    'valor' => $contacto->valor
                ]
            ]);

            return $contacto;
        });
    }

    /**
     * Actualizar contacto
     */
    public function actualizar(int $id, array $data, int $usuarioId): Contacto
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $contacto = Contacto::findOrFail($id);
            $valoresAnteriores = [
                'tipo' => $contacto->tipo,
                'valor' => $contacto->valor
            ];

            $contacto->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CONTACTO_ACTUALIZADO',
                'entidad_type' => $contacto->entidad_type,
                'entidad_id' => $contacto->entidad_id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => [
                    'tipo' => $contacto->tipo,
                    'valor' => $contacto->valor
                ]
            ]);

            return $contacto;
        });
    }

    /**
     * Eliminar contacto
     */
    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $contacto = Contacto::findOrFail($id);
            
            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'CONTACTO_ELIMINADO',
                'entidad_type' => $contacto->entidad_type,
                'entidad_id' => $contacto->entidad_id,
                'valores_anteriores' => [
                    'contacto_id' => $contacto->id,
                    'tipo' => $contacto->tipo,
                    'valor' => $contacto->valor
                ]
            ]);

            return $contacto->delete();
        });
    }
}