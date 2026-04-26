<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientePolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver la lista de clientes
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('clientes.ver');
    }

    /**
     * Determinar si el usuario puede ver un cliente específico
     */
    public function view(User $user, Cliente $cliente): bool
    {
        return $user->hasPermissionTo('clientes.ver');
    }

    /**
     * Determinar si el usuario puede crear clientes
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('clientes.crear');
    }

    /**
     * Determinar si el usuario puede editar un cliente
     */
    public function update(User $user, Cliente $cliente): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('clientes.editar');
    }

    /**
     * Determinar si el usuario puede eliminar un cliente
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('clientes.eliminar');
    }

    /**
     * Determinar si el usuario puede restaurar un cliente 
     */
    public function restore(User $user, Cliente $cliente): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('clientes.editar');
    }
}