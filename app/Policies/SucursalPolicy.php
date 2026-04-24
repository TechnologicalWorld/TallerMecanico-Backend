<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sucursal;

class SucursalPolicy
{
    /**
     * Determinar si el usuario puede editar la sucursal
     */
    public function update(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasRole('Administrador de Sucursal')) {
            return $user->current_branch_id === $sucursal->id;
        }

        return false;
    }

    /**
     * Determinar si el usuario puede ver la sucursal
     */
    public function view(User $user, Sucursal $sucursal): bool
    {
        return $user->hasPermissionTo('sucursales.ver');
    }
}