<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class RequireActiveBranch
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('No autenticado', 401);
        }
        if (!$user->current_branch_id) {
            $sucursales = $user->sucursales()->wherePivot('activo', true)->get();
            
            if ($sucursales->isEmpty()) {
                return $this->error(
                    'No tienes ninguna sucursal asignada. Contacta al administrador.',
                    403
                );
            }

            if ($sucursales->count() === 1) {
                $user->current_branch_id = $sucursales->first()->id;
                $user->save();
                
                setPermissionsTeamId($user->current_branch_id);
                
                return $next($request);
            }

            return $this->error(
                'Debes seleccionar una sucursal para continuar',
                403,
                [
                    'sucursales_disponibles' => $sucursales->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'nombre' => $s->nombre,
                            'codigo' => $s->codigo
                        ];
                    })
                ]
            );
        }

        $sucursal = $user->currentBranch;
        if (!$sucursal || !$sucursal->activa) {
            $user->current_branch_id = null;
            $user->save();
            
            return $this->error(
                'La sucursal seleccionada ya no está activa. Selecciona otra.',
                403
            );
        }

        $tieneAcceso = $user->sucursales()
            ->where('sucursal_id', $user->current_branch_id)
            ->wherePivot('activo', true)
            ->exists();

        if (!$tieneAcceso) {
            $user->current_branch_id = null;
            $user->save();
            
            return $this->error(
                'Has perdido acceso a la sucursal actual. Selecciona otra.',
                403
            );
        }

        setPermissionsTeamId($user->current_branch_id);

        return $next($request);
    }
}