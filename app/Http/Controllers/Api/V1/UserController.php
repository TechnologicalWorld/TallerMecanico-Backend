<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\SwitchBranchRequest;
use App\Http\Requests\User\ToggleUserStatusRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Auth\UserProfileResource;
use App\Http\Resources\User\UserBranchResource;
use App\Http\Resources\User\UserDetailResource;
use App\Http\Resources\User\UserListResource;
use App\Services\UserBranchService;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected UserBranchService $branchService,
        protected UserService $userService
    ) {}

    public function current(Request $request)
    {
        $user = $request->user()->load([
            'persona',
            'currentBranch',
            'sucursales' => function ($q) {
                $q->wherePivot('activo', true);
            },
        ]);

        $contexto = $this->branchService->getCurrentContext($user);

        return $this->success([
            'perfil' => new UserProfileResource($user),
            'contexto' => $contexto,
        ]);
    }

    public function branches(Request $request)
    {
        $branches = $this->branchService->getAvailableBranches($request->user());

        return $this->success([
            'total' => $branches->count(),
            'items' => UserBranchResource::collection($branches),
            'sucursal_actual' => $request->user()->current_branch_id,
        ]);
    }

    public function switchBranch(Request $request, $id)
    {
        $result = $this->branchService->switchBranch(
            $request->user(),
            $id,
            $request->ip()
        );

        return $this->success($result, $result['mensaje']);
    }

    public function switchBranchPost(SwitchBranchRequest $request)
    {
        $result = $this->branchService->switchBranch(
            $request->user(),
            $request->sucursal_id,
            $request->ip()
        );

        return $this->success($result, $result['mensaje']);
    }

    /**
     * Listar usuarios
     */
    public function index(Request $request)
    {
        $this->authorize('usuarios.ver');

        $filtros = $request->only([
            'activo',
            'rol',
            'sucursal_id',
            'fecha_inicio',
            'fecha_fin',
            'busqueda',
            'order_by',
            'order_dir',
        ]);

        $perPage = $request->input('per_page', 15);
        $usuarios = $this->userService->listar($filtros, $perPage);

        return $this->success([
            'items' => UserListResource::collection($usuarios),
            'pagination' => [
                'total' => $usuarios->total(),
                'per_page' => $usuarios->perPage(),
                'current_page' => $usuarios->currentPage(),
                'last_page' => $usuarios->lastPage(),
                'from' => $usuarios->firstItem(),
                'to' => $usuarios->lastItem(),
            ],
        ]);
    }

    /**
     * Crear usuario
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('usuarios.crear');

        $user = $this->userService->crear(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new UserDetailResource($user),
            'Usuario creado exitosamente'
        );
    }

    /**
     * Ver usuario
     */
    public function show($id)
    {
        $this->authorize('usuarios.ver');

        $user = $this->userService->obtenerConRelaciones($id);

        return $this->success(new UserDetailResource($user));
    }

    /**
     * Editar usuario
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $this->authorize('usuarios.editar');

        $user = $this->userService->actualizar(
            $id,
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            new UserDetailResource($user),
            'Usuario actualizado exitosamente'
        );
    }

    /**
     * Activar/desactivar usuario
     */
    public function toggleStatus(ToggleUserStatusRequest $request, $id)
    {
        $this->authorize('usuarios.editar');

        try {
            $user = $this->userService->toggleStatus(
                $id,
                $request->activo,
                $request->motivo,
                $request->user()->id
            );

            $mensaje = $user->activo ? 'Usuario activado' : 'Usuario desactivado';

            return $this->success([
                'id' => $user->id,
                'activo' => $user->activo,
            ], $mensaje);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Asignar rol a un usuario
     * POST /api/v1/users/{id}/assign-role
     */
    public function assignRole(Request $request, $id)
    {
        $this->authorize('usuarios.editar');

        $request->validate([
            // 'role_id' => 'required|integer|exists:roles,id',
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
        ]);
            Log::info("QUE RARO",[$id]);

        $user = $this->userService->assignRole(
            $id,
            $request->role_id,
            $request->user()->id
        );

        return $this->success(
            new UserDetailResource($user),
            'Rol asignado exitosamente'
        );
    }

    /**
     * Quitar rol de un usuario
     * DELETE /api/v1/users/{id}/remove-role
     */
    public function removeRole(Request $request, $id)
    {
        $this->authorize('usuarios.editar');

        $request->validate([
            // 'role_id' => 'required|integer|exists:roles,id',
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
        ]);

        $user = $this->userService->removeRole(
            $id,
            $request->role_id,
            $request->user()->id
        );

        return $this->success(
            new UserDetailResource($user),
            'Rol removido exitosamente'
        );
    }
}
