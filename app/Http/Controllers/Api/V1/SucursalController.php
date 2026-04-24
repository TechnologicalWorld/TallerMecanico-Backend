<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sucursal\StoreSucursalRequest;
use App\Http\Requests\Sucursal\UpdateSucursalRequest;
use App\Http\Requests\Sucursal\ToggleSucursalStatusRequest;
use App\Http\Resources\Sucursal\SucursalListResource;
use App\Http\Resources\Sucursal\SucursalDetailResource;
use App\Models\Sucursal;
use App\Services\SucursalService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SucursalController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SucursalService $sucursalService
    ) {}

    /**
     * Listado de sucursales
     */
    public function index(Request $request)
    {
        $this->authorize('sucursales.ver');

        $filtros = $request->only([
            'activa',
            'busqueda',
            'ciudad',
            'fecha_inicio',
            'fecha_fin',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $sucursales = $this->sucursalService->listar($filtros, $perPage);

        return $this->success([
            'items' => SucursalListResource::collection($sucursales),
            'pagination' => [
                'total' => $sucursales->total(),
                'per_page' => $sucursales->perPage(),
                'current_page' => $sucursales->currentPage(),
                'last_page' => $sucursales->lastPage(),
                'from' => $sucursales->firstItem(),
                'to' => $sucursales->lastItem(),
            ]
        ]);
    }

    /**
     * Obtener sucursales para selector (dropdown)
     */
    public function getParaSelector(Request $request)
    {
        $this->authorize('sucursales.ver');
        \Log::info("ENTRO");
        $sucursales = $this->sucursalService->getParaSelector();
        \Log::info("ENTRO",$sucursales);

        return $this->success($sucursales);
    }


    /**
     * Verificar si un código está disponible
     */
    public function verificarCodigo(Request $request, $codigo)
    {
        $this->authorize('sucursales.ver');

        $excluirId = $request->input('excluir_id');
        $existe = $this->sucursalService->codigoExiste($codigo, $excluirId);

        return $this->success([
            'codigo' => $codigo,
            'disponible' => !$existe,
            'mensaje' => $existe ? 'El código no está disponible' : 'El código está disponible'
        ]);
    }

    /**
     * Crear sucursal
     */
    public function store(StoreSucursalRequest $request)
    {
        $this->authorize('sucursales.crear');

        $sucursal = $this->sucursalService->crear(
            $request->except('logo'),
            $request->file('logo'),
            $request->user()
        );

        return $this->created(
            new SucursalDetailResource($sucursal),
            'Sucursal creada exitosamente'
        );
    }

    /**
     * Ver sucursal
     */
    public function show($id)
    {
        $this->authorize('sucursales.ver');

        $sucursal = $this->sucursalService->obtenerConRelaciones((int)$id);

        return $this->success(new SucursalDetailResource($sucursal));
    }

    /**
     * Editar sucursal
     */
    public function update(UpdateSucursalRequest $request, $id)
    {
        $this->authorize('sucursales.editar');

        $sucursal = $this->sucursalService->actualizar(
            $id,
            $request->except('logo'),
            $request->file('logo'),
            $request->user()->id
        );

        return $this->success(
            new SucursalDetailResource($sucursal),
            'Sucursal actualizada exitosamente'
        );
    }

    /**
     * Activar/desactivar sucursal
     */
    public function toggleStatus(ToggleSucursalStatusRequest $request, $id)
    {
        $this->authorize('sucursales.editar');

        $sucursal = $this->sucursalService->toggleStatus(
            $id,
            $request->activa,
            $request->motivo,
            $request->user()->id
        );

        $mensaje = $sucursal->activa ? 'Sucursal activada' : 'Sucursal desactivada';

        return $this->success([
            'id' => $sucursal->id,
            'nombre' => $sucursal->nombre,
            'activa' => $sucursal->activa,
        ], $mensaje);
    }

    /**
     * Obtener usuarios de una sucursal
     */
    public function getUsuarios(Request $request, $id)
    {
        $this->authorize('sucursales.ver');

        $sucursal = Sucursal::with(['usuarios' => function ($q) {
            $q->with('persona')->orderBy('username');
        }])->findOrFail($id);

        $usuarios = $sucursal->usuarios->map(fn($user) => [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'nombre' => $user->persona?->nombre_completo,
            'es_administrador' => $user->pivot->es_administrador,
            'asignacion_activa' => $user->pivot->activo,
        ]);

        return $this->success([
            'sucursal' => $sucursal->nombre,
            'total' => $usuarios->count(),
            'items' => $usuarios
        ]);
    }
}