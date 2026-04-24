<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Persona\ChangeEstadoRequest;
use App\Http\Requests\Persona\StorePersonaRequest;
use App\Http\Requests\Persona\UpdatePersonaRequest;
use App\Http\Resources\Persona\PersonaFisicaResource;
use App\Http\Resources\Persona\PersonaListResource;
use App\Http\Resources\Persona\PersonaMoralResource;
use App\Http\Resources\Persona\PersonaResource;
use App\Services\PersonaService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PersonaService $personaService
    ) {}

    /**
     * Listado general con filtros
     */
    public function index(Request $request)
    {
        $this->authorize('personas.ver');

        $filtros = $request->only([
            'tipo_persona',
            'estado',
            'fecha_inicio',
            'fecha_fin',
            'busqueda',
            'order_by',
            'order_dir',
        ]);

        $perPage = $request->input('per_page', 15);
        $personas = $this->personaService->listar($filtros, $perPage);

        return $this->success([
            'items' => PersonaListResource::collection($personas),
            'pagination' => [
                'total' => $personas->total(),
                'per_page' => $personas->perPage(),
                'current_page' => $personas->currentPage(),
                'last_page' => $personas->lastPage(),
                'from' => $personas->firstItem(),
                'to' => $personas->lastItem(),
            ],
        ]);
    }

    /**
     * Listado solo personas físicas
     */
    public function fisicas(Request $request)
    {
        $this->authorize('personas.ver');

        $filtros = $request->except('tipo_persona');
        $perPage = $request->input('per_page', 15);

        $personas = $this->personaService->listarFisicas($filtros, $perPage);

        return $this->success([
            'items' => PersonaFisicaResource::collection($personas),
            'pagination' => [
                'total' => $personas->total(),
                'per_page' => $personas->perPage(),
                'current_page' => $personas->currentPage(),
                'last_page' => $personas->lastPage(),
            ],
        ]);
    }

    /**
     * Listado solo personas morales
     */
    public function morales(Request $request)
    {
        $this->authorize('personas.ver');

        $filtros = $request->except('tipo_persona');
        $perPage = $request->input('per_page', 15);

        $personas = $this->personaService->listarMorales($filtros, $perPage);

        return $this->success([
            'items' => PersonaMoralResource::collection($personas),
            'pagination' => [
                'total' => $personas->total(),
                'per_page' => $personas->perPage(),
                'current_page' => $personas->currentPage(),
                'last_page' => $personas->lastPage(),
            ],
        ]);
    }

    /**
     * Crear nueva persona     
     */
    public function store(StorePersonaRequest $request)
    {
        $this->authorize('personas.crear');

        $persona = $this->personaService->crear(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new PersonaResource($persona->load(['user'])),
            'Persona creada exitosamente'
        );
    }

    /**
     * Ver persona con datos completos     
     */
    public function show($id)
    {
        $this->authorize('personas.ver');

        $persona = $this->personaService->obtenerConRelaciones($id);

        return $this->success(new PersonaResource($persona));
    }

    /**
     * Editar persona     
     */
    public function update(UpdatePersonaRequest $request, $id)
    {
        $this->authorize('personas.editar');

        $persona = $this->personaService->actualizar(
            $id,
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            new PersonaResource($persona->load(['user'])),
            'Persona actualizada exitosamente'
        );
    }

    /**
     * Cambiar estado     
     */
    public function changeEstado(ChangeEstadoRequest $request, $id)
    {
        $this->authorize('personas.editar');

        $persona = $this->personaService->cambiarEstado(
            $id,
            $request->estado,
            $request->motivo,
            $request->user()->id
        );

        return $this->success(
            [
                'id' => $persona->id,
                'estado' => $persona->estado,
                'estado_texto' => $persona->estado?->label(),
            ],
            'Estado actualizado exitosamente'
        );
    }

    /**
     * Eliminar lógico     
     */
    public function destroy($id)
    {
        $this->authorize('personas.eliminar');

        try {
            $this->personaService->eliminar($id, request()->user()->id);

            return $this->success(null, 'Persona eliminada correctamente');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Restaurar persona eliminada     
     */
    public function restore($id)
    {
        $this->authorize('personas.eliminar');

        $persona = $this->personaService->restaurar($id, request()->user()->id);

        return $this->success(
            new PersonaResource($persona),
            'Persona restaurada exitosamente'
        );
    }
}
