<?php

namespace App\Services;

use App\Models\User;
use App\Models\Sucursal;
use App\Models\UsuarioSucursal;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsignacionService
{

    /**
     * Asignar usuario a sucursal
     */
    public function asignar(array $data, int $usuarioId)
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $sucursal = Sucursal::with("usuarios")->find($data['sucursal_id']);
            $existeUsuario = $sucursal->usuarios()->where("usuario_id",$data['usuario_id'])->exists();
            if ($existeUsuario) {
                throw new \Exception('El usuario ya está asignado a esta sucursal');
            }
            $sucursal->usuarios()->attach($data['usuario_id'],[
                'activo' => $data['activo'] ?? true,
                'es_administrador' => $data['es_administrador'] ?? false,
            ]);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'USUARIO_ASIGNADO_SUCURSAL',
                'entidad_type' => Sucursal::class,
                'entidad_id' => $data["usuario_id"],
                'valores_nuevos' => [
                    'usuario_id' => $data['usuario_id'],
                    'sucursal_id' => $data['sucursal_id'],
                    'es_administrador' => $data['es_administrador'] ?? false,
                ]
            ]);

            return $sucursal->load(['usuarios.persona']);
        });
    }

    /**
     * Quitar asignación (desactivar)
     */
    public function quitar(int $sucursalId, int $asignadoId, int $usuarioId): bool
    {
        return DB::transaction(function () use ($sucursalId, $asignadoId, $usuarioId) {
            $sucursal = Sucursal::with("usuarios")->find($sucursalId);
            $existeUsuario = $sucursal->usuarios()->where("usuario_id",$usuarioId)->exists();
            if (!$existeUsuario) {
                throw new \Exception('El usuario no esta asignado a esta sucursal');
            }
            $usuario = User::find($usuarioId);
            if ($usuario && $usuario->current_branch_id === $sucursal->sucursal_id) {
                $usuario->current_branch_id = null;
                $usuario->save();
            }

            $sucursal->usuarios()->detach($usuario);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'USUARIO_QUITADO_SUCURSAL',
                'entidad_type' => Sucursal::class,
                'entidad_id' => $asignadoId,
                'valores_anteriores' => [
                    'usuario_id' => $usuario->usuario_id,
                    'sucursal_id' => $sucursal->sucursal_id,
                ]
            ]);

            return true;
        });
    }

}