<?php

namespace App\Services;

use App\Models\User;
use App\Models\Sucursal;
use App\Models\Auditoria;
use Illuminate\Validation\ValidationException;

class UserBranchService
{
    /**
     * Obtener sucursales disponibles para el usuario
     */
    public function getAvailableBranches(User $user)
    {
        return $user->sucursales()
                    ->wherePivot('activo', true)
                    ->orderBy('nombre')
                    ->get();
    }

    /**
     * Cambiar sucursal activa
     */
    public function switchBranch(User $user, int $sucursalId, string $ip)
    {
        $sucursal = $user->sucursales()
                        ->where('sucursal_id', $sucursalId)
                        ->wherePivot('activo', true)
                        ->first();

        if (!$sucursal) {
            throw ValidationException::withMessages([
                'sucursal_id' => ['No tienes acceso a esta sucursal']
            ]);
        }

        if (!$sucursal->activa) {
            throw ValidationException::withMessages([
                'sucursal_id' => ['La sucursal está inactiva']
            ]);
        }

        $sucursalAnterior = $user->currentBranch;

        $user->current_branch_id = $sucursalId;
        $user->save();

        $user->load('currentBranch');

        Auditoria::create([
            'usuario_id' => $user->id,
            'accion' => 'SWITCH_BRANCH',
            'entidad_type' => User::class,
            'entidad_id' => $user->id,
            'valores_anteriores' => ['sucursal_id' => $sucursalAnterior?->id],
            'valores_nuevos' => ['sucursal_id' => $sucursalId],
            'created_at' => now()
        ]);

        return [
            'sucursal_anterior' => $sucursalAnterior,
            'sucursal_actual' => $sucursal,
            'mensaje' => "Sucursal cambiada a {$sucursal->nombre}"
        ];
    }

    /**
     * Verificar si el usuario tiene una sucursal activa
     */
    public function hasActiveBranch(User $user): bool
    {
        return $user->current_branch_id !== null && 
               $user->currentBranch && 
               $user->currentBranch->activa;
    }

    /**
     * Obtener contexto actual del usuario
     */
    public function getCurrentContext(User $user): array
    {
        $sucursalActual = $user->currentBranch;
        
        return [
            'tipo' => $sucursalActual ? 'business' : 'system',
            'business' => $sucursalActual ? [
                'id' => $sucursalActual->id,
                'nombre' => $sucursalActual->nombre,
                'codigo' => $sucursalActual->codigo
            ] : null,
            'tiene_sucursales' => $user->sucursales()->wherePivot('activo', true)->exists()
        ];
    }
}