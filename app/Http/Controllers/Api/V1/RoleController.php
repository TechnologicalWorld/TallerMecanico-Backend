<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Services\RolePermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RolePermissionService $roleService
    ) {}

    /**
     * Listar roles
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['guard_name', 'busqueda']);
        $roles = $this->roleService->listarRoles($filtros);

        return $this->success([
            'total' => $roles->count(),
            'items' => RoleResource::collection($roles),
        ]);
    }

    /**
     * Crear rol
     */
    public function store(StoreRoleRequest $request)
    {
        $rol = $this->roleService->crearRol(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new RoleResource($rol),
            'Rol creado exitosamente'
        );
    }

    /**
     * Ver rol
     */
    public function show($id)
    {
        $this->authorize('roles.ver');

        $rol = Role::with('permissions')->findOrFail($id);

        return $this->success(new RoleResource($rol));
    }

    /**
     * Editar rol
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        $this->authorize('roles.editar');

        $rol = $this->roleService->actualizarRol(
            $id,
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            new RoleResource($rol),
            'Rol actualizado exitosamente'
        );
    }

    /**
     * Eliminar rol
     */
    public function destroy($id)
    {
        $this->authorize('roles.eliminar');

        try {
            $this->roleService->eliminarRol($id, request()->user()->id);

            return $this->success(null, 'Rol eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Sincronizar permisos del rol
     */
    public function syncPermissions(Request $request, $id)
    {
        $this->authorize('roles.editar');

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permisos,id',
        ]);

        $rol = $this->roleService->sincronizarPermisosRol(
            $id,
            $request->permissions,
            $request->user()->id
        );

        return $this->success(
            new RoleResource($rol),
            'Permisos sincronizados exitosamente'
        );
    }

    /**
     * Quitar permiso de un rol
     * DELETE /api/v1/roles/{id}/remove-permission
     */
    public function removePermission(Request $request, $id)
    {
        $this->authorize('roles.editar');

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permisos,id',
        ]);

        $role = $this->roleService->removePermissionFromRole(
            $id,
            $request->permission_id,
            $request->user()->id
        );

        return $this->success(
            new RoleResource($role),
            'Permiso removido exitosamente'
        );

    }
}
