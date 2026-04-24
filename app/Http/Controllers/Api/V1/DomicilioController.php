<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domicilio\StoreDomicilioRequest;
use App\Http\Requests\Domicilio\UpdateDomicilioRequest;
use App\Http\Resources\DomicilioResource;
use App\Models\Domicilio;
use App\Services\DomicilioService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DomicilioController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DomicilioService $domicilioService
    ) {}

    /**
     * Listar domicilios por entidad
     */
    public function index(Request $request, $tipo, $id)
    {
        $this->authorize('domicilios.ver');

        $entidadType = $this->resolveEntidadType($tipo);
        
        $domicilios = $this->domicilioService->listarPorEntidad($entidadType, $id);

        return $this->success([
            'total' => $domicilios->count(),
            'items' => DomicilioResource::collection($domicilios)
        ]);
    }

    /**
     * Crear domicilio
     */
    public function store(StoreDomicilioRequest $request)
    {
        $this->authorize('domicilios.crear');

        $domicilio = $this->domicilioService->crear(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new DomicilioResource($domicilio),
            'Domicilio creado exitosamente'
        );
    }

    /**
     * Ver domicilio
     */
    public function show($id)
    {
        $this->authorize('domicilios.ver');

        $domicilio = Domicilio::findOrFail($id);

        return $this->success(new DomicilioResource($domicilio));
    }

    /**
     * Editar domicilio
     */
    public function update(UpdateDomicilioRequest $request, $id)
    {
        $this->authorize('domicilios.editar');

        $domicilio = $this->domicilioService->actualizar(
            $id,
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            new DomicilioResource($domicilio),
            'Domicilio actualizado exitosamente'
        );
    }

    /**
     * Eliminar domicilio (lógico)
     */
    public function destroy($id)
    {
        $this->authorize('domicilios.eliminar');

        $this->domicilioService->eliminar($id, request()->user()->id);

        return $this->success(null, 'Domicilio eliminado exitosamente');
    }

    /**
     * Restaurar domicilio eliminado
     */
    public function restore($id)
    {
        $this->authorize('domicilios.eliminar');

        $domicilio = $this->domicilioService->restaurar($id, request()->user()->id);

        return $this->success(
            new DomicilioResource($domicilio),
            'Domicilio restaurado exitosamente'
        );
    }

    /**
     * Obtener domicilio principal de una entidad
     */
    public function principal(Request $request, $tipo, $id)
    {
        $this->authorize('domicilios.ver');

        $entidadType = $this->resolveEntidadType($tipo);
        $domicilio = $this->domicilioService->obtenerPrincipal($entidadType, $id);

        if (!$domicilio) {
            return $this->error('No hay domicilio principal asignado', 404);
        }

        return $this->success(new DomicilioResource($domicilio));
    }

    /**
     * Resolver tipo de entidad
     */
    private function resolveEntidadType(string $tipo): string
    {
        return match($tipo) {
            'personas' => 'App\Models\Persona',
            'sucursales' => 'App\Models\Sucursal',
            default => throw new \InvalidArgumentException('Tipo de entidad no válido')
        };
    }
}