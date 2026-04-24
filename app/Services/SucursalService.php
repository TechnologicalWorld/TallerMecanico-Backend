<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SucursalService
{
    /**
     * Disco de almacenamiento
     */
    protected string $disk = 'public';

    /**
     * Listar sucursales con filtros
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Sucursal::withCount('usuarios');

        if (isset($filtros['activa'])) {
            $query->where('activa', filter_var($filtros['activa'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filtros['busqueda'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('nombre', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('codigo', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('email', 'like', "%{$filtros['busqueda']}%")
                    ->orWhere('direccion', 'like', "%{$filtros['busqueda']}%");
            });
        }

        if (! empty($filtros['ciudad'])) {
            $query->where('direccion', 'like', "%{$filtros['ciudad']}%");
        }

        if (! empty($filtros['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }
        if (! empty($filtros['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }

        $orderBy = $filtros['order_by'] ?? 'created_at';
        $orderDir = $filtros['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Crear sucursal
     */
    public function crear(array $data, ?object $logo, User $creador): Sucursal
    {
        return DB::transaction(function () use ($data, $logo, $creador) {
            if ($logo) {
                $logoPath = $this->subirLogo($logo, $data['codigo']);
                $data['logo_path'] = $logoPath;
            }

            $sucursal = Sucursal::create($data);

            $sucursal->usuarios()->attach($creador->id, [
                'es_administrador' => true,
                'activo' => true,
            ]);
            setPermissionsTeamId($sucursal->id);

            if ($creador->hasRole('Empleado') || ! $creador->hasRole('Administrador de Sucursal')) {
                $rolAdmin = Role::where('name', 'Administrador de Sucursal')
                    ->where('guard_name', 'api')
                    ->first();

                if ($rolAdmin) {
                    $creador->assignRole($rolAdmin);
                }
            }

            if (! $creador->current_branch_id) {
                $creador->current_branch_id = $sucursal->id;
                $creador->save();

            }

            Auditoria::create([
                'usuario_id' => $creador->id,
                'accion' => 'SUCURSAL_CREADA',
                'entidad_type' => Sucursal::class,
                'entidad_id' => $sucursal->id,
                'valores_nuevos' => [
                    'nombre' => $sucursal->nombre,
                    'codigo' => $sucursal->codigo,
                    'email' => $sucursal->email,
                    'creador' => [
                        'id' => $creador->id,
                        'username' => $creador->username,
                        'asignado_como_admin' => true,
                    ],
                ],
            ]);

            $sucursal->load('usuarios');

            return $sucursal;
        });
    }

    /**
     * Obtener sucursal con relaciones
     */
    public function obtenerConRelaciones(int $id): Sucursal
    {
        return Sucursal::with([
            'usuarios' => function ($q) {
                $q->with('persona')->orderBy('username');
            },
            'contactos',
            'domicilios',
            'archivos',
        ])->withCount('usuarios')->findOrFail($id);
    }

    /**
     * Actualizar sucursal
     */
    public function actualizar(int $id, array $data, ?object $logo, int $usuarioId): Sucursal
    {
        return DB::transaction(function () use ($id, $data, $logo, $usuarioId) {
            $sucursal = Sucursal::findOrFail($id);
            $valoresAnteriores = $sucursal->toArray();

            if ($logo) {
                if ($sucursal->logo_path) {
                    $this->eliminarLogo($sucursal->logo_path);
                }

                $logoPath = $this->subirLogo($logo, $sucursal->codigo);
                $data['logo_path'] = $logoPath;
            }

            $sucursal->update($data);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'SUCURSAL_ACTUALIZADA',
                'entidad_type' => Sucursal::class,
                'entidad_id' => $id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $data,
            ]);

            return $sucursal->fresh();
        });
    }

    /**
     * Activar/desactivar sucursal
     */
    public function toggleStatus(int $id, bool $activa, ?string $motivo, int $usuarioId): Sucursal
    {
        return DB::transaction(function () use ($id, $activa, $motivo, $usuarioId) {
            $sucursal = Sucursal::findOrFail($id);
            $estadoAnterior = $sucursal->activa;

            $sucursal->activa = $activa;
            $sucursal->save();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => $activa ? 'SUCURSAL_ACTIVADA' : 'SUCURSAL_DESACTIVADA',
                'entidad_type' => Sucursal::class,
                'entidad_id' => $id,
                'valores_anteriores' => ['activa' => $estadoAnterior],
                'valores_nuevos' => ['activa' => $activa, 'motivo' => $motivo],
            ]);

            return $sucursal;
        });
    }

    /**
     * Subir logo
     */
    private function subirLogo(object $logo, string $codigo): string
    {
        $nombreArchivo = 'logo_' . strtolower($codigo) . '_' . time() . '.' . $logo->getClientOriginalExtension();
        $ruta = 'sucursales/logos/' . $nombreArchivo;

        Storage::disk($this->disk)->put($ruta, file_get_contents($logo));

        return $ruta;
    }

    /**
     * Eliminar logo
     */
    private function eliminarLogo(string $ruta): bool
    {
        if (Storage::disk($this->disk)->exists($ruta)) {
            return Storage::disk($this->disk)->delete($ruta);
        }

        return false;
    }

    /**
     * Obtener sucursales para selector (dropdown)
     */
    public function getParaSelector(): array
    {
        return Sucursal::where('activa', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'codigo' => $s->codigo,
            ])
            ->toArray();
    }

    /**
     * Verificar si el código ya existe
     */
    public function codigoExiste(string $codigo, ?int $excluirId = null): bool
    {
        Log::info($excluirId);
        Log::error($excluirId);
        logger($excluirId);

        $query = Sucursal::where('codigo', $codigo);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
