<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    /**
     * ROLES
     */

    /**
     * Listar roles
     */
    /**
     * Listar roles
     */
    public function listarRoles(array $filtros = [])
    {
        $query = Role::with('permissions');

        if (! empty($filtros['guard_name'])) {
            $query->where('guard_name', $filtros['guard_name']);
        }

        if (! empty($filtros['busqueda'])) {
            $query->where('name', 'like', "%{$filtros['busqueda']}%");
        }

        $roles = $query->where('sucursal_id', getPermissionsTeamId())->orWhere('sucursal_id', null)->orderBy('name')->get();

        // // Cargar el conteo de usuarios manualmente si es necesario
        // foreach ($roles as $role) {
        //     $role->users_count = $role->users()->count();
        // }

        return $roles;
    }

    /**
     * Crear rol
     */
    public function crearRol(array $data, int $usuarioId): Role
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $rol = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ]);

            if (! empty($data['permissions'])) {
                $rol->syncPermissions($data['permissions']);
            }

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ROL_CREADO',
                'entidad_type' => Role::class,
                'entidad_id' => $rol->id,
                'valores_nuevos' => [
                    'name' => $rol->name,
                    'guard' => $rol->guard_name,
                    'permissions' => $data['permissions'] ?? [],
                ],
            ]);

            return $rol->load('permissions');
        });
    }

    /**
     * Actualizar rol
     */
    public function actualizarRol(int $id, array $data, int $usuarioId): Role
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $rol = Role::findOrFail($id);
            $valoresAnteriores = $rol->toArray();

            if (isset($data['name'])) {
                $rol->name = $data['name'];
            }
            if (isset($data['guard_name'])) {
                $rol->guard_name = $data['guard_name'];
            }
            $rol->save();

            if (isset($data['permissions'])) {
                $rol->syncPermissions($data['permissions']);
            }

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ROL_ACTUALIZADO',
                'entidad_type' => Role::class,
                'entidad_id' => $id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data,
            ]);

            return $rol->load('permissions');
        });
    }

    /**
     * Eliminar rol
     */
    public function eliminarRol(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $rol = Role::findOrFail($id);

            // Verificar si tiene usuarios asignados
            if ($rol->users()->count() > 0) {
                throw new \Exception('No se puede eliminar un rol que tiene usuarios asignados');
            }

            // No permitir eliminar roles del sistema
            if (in_array($rol->name, ['Super Admin', 'Administrador de Sucursal', 'Empleado', 'Cliente'])) {
                throw new \Exception('No se puede eliminar un rol del sistema');
            }

            $rol->delete();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ROL_ELIMINADO',
                'entidad_type' => Role::class,
                'entidad_id' => $id,
                'valores_anteriores' => ['name' => $rol->name],
            ]);

            return true;
        });
    }

    /**
     * Listar permisos
     */
    public function listarPermisos(array $filtros = [])
    {
        $query = Permission::query();

        if (! empty($filtros['guard_name'])) {
            $query->where('guard_name', $filtros['guard_name']);
        }

        if (! empty($filtros['modulo'])) {
            $query->where('name', 'like', $filtros['modulo'] . '.%');
        }

        if (! empty($filtros['busqueda'])) {
            $query->where('name', 'like', "%{$filtros['busqueda']}%");
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Obtener permisos agrupados por módulo
     */
    public function getPermisosAgrupados()
    {
        $permisos = Permission::all();

        return $permisos->groupBy(function ($permiso) {
            $parts = explode('.', $permiso->name);

            return $parts[0] ?? 'general';
        })->map(function ($grupo) {
            return $grupo->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'accion' => explode('.', $p->name)[1] ?? $p->name,
            ])->values();
        });
    }

    /**
     * Crear permiso
     */
    public function crearPermiso(array $data, int $usuarioId): Permission
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $permiso = Permission::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ]);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERMISO_CREADO',
                'entidad_type' => Permission::class,
                'entidad_id' => $permiso->id,
                'valores_nuevos' => [
                    'name' => $permiso->name,
                    'guard' => $permiso->guard_name,
                ],
            ]);

            return $permiso;
        });
    }

    /**
     * Actualizar permiso
     */
    public function actualizarPermiso(int $id, array $data, int $usuarioId): Permission
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $permiso = Permission::findOrFail($id);
            $valoresAnteriores = $permiso->toArray();

            $permiso->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERMISO_ACTUALIZADO',
                'entidad_type' => Permission::class,
                'entidad_id' => $id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data,
            ]);

            return $permiso;
        });
    }

    /**
     * Eliminar permiso
     */
    public function eliminarPermiso(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $permiso = Permission::findOrFail($id);

            // Verificar si está asignado a algún rol
            if ($permiso->roles()->count() > 0) {
                throw new \Exception('No se puede eliminar un permiso que está asignado a roles');
            }

            $permiso->delete();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'PERMISO_ELIMINADO',
                'entidad_type' => Permission::class,
                'entidad_id' => $id,
                'valores_anteriores' => ['name' => $permiso->name],
            ]);

            return true;
        });
    }

    /**
     * Sincronizar permisos de un rol
     */
    public function sincronizarPermisosRol(int $rolId, array $permisosIds, int $usuarioId): Role
    {
        return DB::transaction(function () use ($rolId, $permisosIds, $usuarioId) {
            $rol = Role::findOrFail($rolId);
            $permisosAnteriores = $rol->permissions->pluck('id')->toArray();

            $rol->syncPermissions($permisosIds);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ROL_PERMISOS_SINCRONIZADOS',
                'entidad_type' => Role::class,
                'entidad_id' => $rolId,
                'valores_anteriores' => ['permissions' => $permisosAnteriores],
                'valores_nuevos' => ['permissions' => $permisosIds],
            ]);

            return $rol->load('permissions');
        });
    }

    /**
     * Quitar permiso de un rol
     */
    public function removePermissionFromRole(int $roleId, int $permissionId, int $usuarioId): Role
    {
        return DB::transaction(function () use ($roleId, $permissionId, $usuarioId) {
            $role = Role::findOrFail($roleId);
            $permisosAnteriores = $role->permissions->pluck('id')->toArray();

            $permissionsToRemoveList = Permission::whereIn('id', $permissionId)->get();
            $role->revokePermissionTo($permissionsToRemoveList);


            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ROL_PERMISO_QUITADO',
                'entidad_type' => Role::class,
                'entidad_id' => $roleId,
                'valores_anteriores' => ['permissions' => $permisosAnteriores],
                'valores_nuevos' => ['permissions' => $permissionId],

            ]);

            return $role->load('permissions');
        });
    }
}
