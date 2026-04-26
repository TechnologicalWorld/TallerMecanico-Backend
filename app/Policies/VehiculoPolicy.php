<?php

namespace App\Policies;

use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiculoPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver la lista de vehículos.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('vehiculos.ver');
    }

    /**
     * Determinar si el usuario puede ver un vehículo específico.
     */
    public function view(User $user, Vehiculo $vehiculo): bool
    {
        return $user->hasPermissionTo('vehiculos.ver');
    }

    /**
     * Determinar si el usuario puede registrar vehículos.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('vehiculos.crear');
    }

    /**
     * Determinar si el usuario puede editar la información de un vehículo.
     */
    public function update(User $user, Vehiculo $vehiculo): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('vehiculos.editar');
    }

    /**
     * Determinar si el usuario puede eliminar un vehículo.
     */
    public function delete(User $user, Vehiculo $vehiculo): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('vehiculos.eliminar');
    }

    /**
     * Determinar si el usuario puede restaurar un vehículo eliminado.
     */
    public function restore(User $user, Vehiculo $vehiculo): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('vehiculos.eliminar');
    }
}