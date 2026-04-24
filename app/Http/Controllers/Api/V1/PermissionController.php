<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\Permission\PermissionResource;
use App\Services\RolePermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RolePermissionService $permissionService
    ) {}

    /**
     * Listar permisos
     */
    public function index(Request $request)
    {
        $this->authorize('permisos.ver');

        $filtros = $request->only(['guard_name', 'modulo', 'busqueda']);
        $permisos = $this->permissionService->listarPermisos($filtros);

        return $this->success([
            'total' => $permisos->count(),
            'items' => PermissionResource::collection($permisos)
        ]);
    }

    /**
     * Obtener permisos agrupados por módulo
     */
    public function agrupados(Request $request)
    {
        $this->authorize('permisos.ver');

        $permisos = $this->permissionService->getPermisosAgrupados();

        return $this->success($permisos);
    }

    /**
     * Crear permiso
     */
    public function store(StorePermissionRequest $request)
    {
        $this->authorize('permisos.crear');

        $permiso = $this->permissionService->crearPermiso(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new PermissionResource($permiso),
            'Permiso creado exitosamente'
        );
    }

    /**
     * Ver permiso
     */
    public function show($id)
    {
        $this->authorize('permisos.ver');

        $permiso = Permission::findOrFail($id);

        return $this->success(new PermissionResource($permiso));
    }

    /**
     * Editar permiso
     */
    public function update(UpdatePermissionRequest $request, $id)
    {
        $this->authorize('permisos.editar');

        $permiso = $this->permissionService->actualizarPermiso(
            $id,
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            new PermissionResource($permiso),
            'Permiso actualizado exitosamente'
        );
    }

    /**
     * Eliminar permiso
     */
    public function destroy($id)
    {
        $this->authorize('permisos.eliminar');

        try {
            $this->permissionService->eliminarPermiso($id, request()->user()->id);
            return $this->success(null, 'Permiso eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}