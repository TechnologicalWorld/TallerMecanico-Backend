<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Listar usuarios con filtros
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['persona', 'sucursales'])
            ->withCount(['sucursales', 'sesiones as sesiones_activas_count' => function ($q) {
                $q->where('activa', true);
            }]);
        if (isset($filtros['activo'])) {
            $query->where('activo', filter_var($filtros['activo'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($filtros['rol'])) {
            $query->role($filtros['rol']);
        }
        if (! empty($filtros['sucursal_id'])) {
            $query->whereHas('sucursales', function ($q) use ($filtros) {
                $q->where('sucursal_id', $filtros['sucursal_id']);
            });
        }
        if (! empty($filtros['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }
        if (! empty($filtros['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }
        if (! empty($filtros['busqueda'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('username', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('email', 'like', "%{$filtros['busqueda']}%")
                    ->orWhereHas('persona', function ($persona) use ($filtros) {
                        $persona->where('nombre', 'like', "%{$filtros['busqueda']}%")
                            ->orWhere('apellido', 'like', "%{$filtros['busqueda']}%")
                            ->orWhere('razon_social', 'like', "%{$filtros['busqueda']}%")
                            ->orWhere('identificacion_principal', 'like', "%{$filtros['busqueda']}%");
                    });
            });
        }
        $orderBy = $filtros['order_by'] ?? 'created_at';
        $orderDir = $filtros['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Crear usuario
     */
    public function crear(array $data, int $usuarioId): User
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            $user = User::create([
                'persona_id' => $data['persona_id'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'activo' => $data['activo'] ?? true,
            ]);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'USUARIO_CREADO',
                'entidad_type' => User::class,
                'entidad_id' => $user->id,
                'valores_nuevos' => [
                    'persona_id' => $user->persona_id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'roles' => $data['roles'] ?? [],
                    'sucursales' => $data['sucursales'] ?? [],
                ],
            ]);

            return $user->load(['persona', 'sucursales']);
        });
    }

    /**
     * Obtener usuario con relaciones
     */
    public function obtenerConRelaciones(int $id): User
    {
        return User::with([
            'persona',
            'persona.contactos',
            'persona.domicilios',
            'persona.archivos',
            'sucursales',
            'sesiones' => function ($q) {
                $q->where('activa', true)->latest();
            },
        ])->withCount('sesiones as sesiones_activas_count')->findOrFail($id);
    }

    /**
     * Actualizar usuario
     */
    public function actualizar(int $id, array $data, int $usuarioId): User
    {
        return DB::transaction(function () use ($id, $data, $usuarioId) {
            $user = User::findOrFail($id);
            $valoresAnteriores = $user->toArray();
            $updateData = [];
            if (isset($data['persona_id'])) {
                $updateData['persona_id'] = $data['persona_id'];
            }
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            if (isset($data['username'])) {
                $updateData['username'] = $data['username'];
            }
            if (isset($data['activo'])) {
                $updateData['activo'] = filter_var($data['activo'], FILTER_VALIDATE_BOOLEAN);
            }
            if (! empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'USUARIO_ACTUALIZADO',
                'entidad_type' => User::class,
                'entidad_id' => $id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data,
            ]);

            return $user->load(['persona', 'sucursales']);
        });
    }

    /**
     * Activar/desactivar usuario
     */
    public function toggleStatus(int $id, bool $activo, ?string $motivo, int $usuarioId): User
    {
        return DB::transaction(function () use ($id, $activo, $motivo, $usuarioId) {
            $user = User::findOrFail($id);

            if ($usuarioId === $user->id && ! $activo) {
                throw new \Exception('No puedes desactivar tu propio usuario');
            }

            $estadoAnterior = $user->activo;
            $user->activo = $activo;
            $user->save();
            if (! $activo) {
                $user->tokens()->delete();
                $user->sesiones()->where('activa', true)->update([
                    'activa' => false,
                    'logout_at' => now(),
                ]);
            }
            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => $activo ? 'USUARIO_ACTIVADO' : 'USUARIO_DESACTIVADO',
                'entidad_type' => User::class,
                'entidad_id' => $id,
                'valores_anteriores' => ['activo' => $estadoAnterior],
                'valores_nuevos' => ['activo' => $activo, 'motivo' => $motivo],
            ]);

            return $user;
        });
    }

    /**
     * Asignar rol a un usuario
     */
    public function assignRole(int $userId, array $rolesIds, int $usuarioId): User
    {
            Log::info("QUE RARO2",[$userId]);
        return DB::transaction(function () use ($userId, $rolesIds, $usuarioId) {

            $user = User::findOrFail($userId);
            $roles = Role::whereIn('id', $rolesIds)->get();

            $sucursalId = getPermissionsTeamId();
            $assignedRoles = [];

            foreach ($roles as $role) {
                $user->assignRole($role);
                $assignedRoles[] = $role->name;
            }

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'USUARIO_ROLES_ASIGNADO',
                'entidad_type' => User::class,
                'entidad_id' => $userId,
                'valores_nuevos' => [
                    'role' => $role->name,
                    'sucursal_id' => $sucursalId,
                ]
            ]);

            return $user->load('roles');
        });
    }

    /**
     * Quitar rol a un usuario
     */
    public function removeRole(int $userId, array $rolesIds, int $usuarioId): User
    {
        return DB::transaction(function () use ($userId, $rolesIds, $usuarioId) {
            $user = User::findOrFail($userId);
            $roles = Role::whereIn('id', $rolesIds)->get();

            $sucursalId = getPermissionsTeamId();
            $removedRoles = [];

            foreach ($roles as $role) {
                $user->removeRole($role, $sucursalId);
                $removedRoles[] = $role->name;
            }


            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'USUARIO_ROL_QUITADO',
                'entidad_type' => User::class,
                'entidad_id' => $userId,
                'valores_anteriores' => [
                    'role' => $role->name,
                    'sucursal_id' => $sucursalId,
                ]
            ]);

            return $user->load('roles');
        });
    }

    /**
     * Obtener usuarios por rol
     */
    public function getUsuariosPorRol(int $roleId, ?int $sucursalId = null): array
    {
        $role = Role::with('users.persona')->findOrFail($roleId);

        $query = $role->users();

        if ($sucursalId) {
            $query->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });
        }

        $usuarios = $query->get();

        return [
            'rol' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ],
            'total' => $usuarios->count(),
            'usuarios' => $usuarios->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nombre' => $user->persona?->nombre_completo ?? $user->persona?->razon_social,
                    'activo' => $user->activo,
                    'sucursales' => $user->sucursales->map(fn($s) => [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'codigo' => $s->codigo,
                    ]),
                ];
            }),
        ];
    }

    /**
     * Obtener usuario con sus roles y sucursales
     */
    public function obtenerConRolesYSucursales(int $userId): User
    {
        return User::with([
            'persona',
            'roles',
            'sucursales' => function ($q) {
                $q->withPivot('es_administrador', 'activo');
            }
        ])->findOrFail($userId);
    }

    /**
     * Obtener usuarios de una sucursal específica
     */
    public function getUsuariosPorSucursal(int $sucursalId, array $filtros = []): array
    {
        $query = User::with('persona')
            ->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                    ->wherePivot('activo', true);
            });

        if (!empty($filtros['role_id'])) {
            $query->whereHas('roles', function ($q) use ($filtros) {
                $q->where('role_id', $filtros['role_id']);
            });
        }

        if (isset($filtros['activo'])) {
            $query->where('activo', filter_var($filtros['activo'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filtros['busqueda'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('username', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('email', 'like', "%{$filtros['busqueda']}%")
                    ->orWhereHas('persona', function ($p) use ($filtros) {
                        $p->where('nombre', 'like', "%{$filtros['busqueda']}%")
                            ->orWhere('apellido', 'like', "%{$filtros['busqueda']}%")
                            ->orWhere('razon_social', 'like', "%{$filtros['busqueda']}%");
                    });
            });
        }

        $usuarios = $query->orderBy('username')->get();

        $sucursal = Sucursal::find($sucursalId);

        return [
            'sucursal' => [
                'id' => $sucursal?->id,
                'nombre' => $sucursal?->nombre,
                'codigo' => $sucursal?->codigo,
            ],
            'total' => $usuarios->count(),
            'usuarios' => $usuarios->map(function ($user) {
                $pivot = $user->sucursales->first()?->pivot;

                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nombre' => $user->persona?->nombre_completo ?? $user->persona?->razon_social,
                    'activo' => $user->activo,
                    'es_administrador' => $pivot?->es_administrador ?? false,
                    'roles' => $user->roles->map(fn($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                    ]),
                ];
            }),
        ];
    }
}
