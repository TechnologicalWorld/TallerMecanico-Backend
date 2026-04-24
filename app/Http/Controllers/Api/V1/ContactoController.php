<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contacto\StoreContactoRequest;
use App\Http\Requests\Contacto\UpdateContactoRequest;
use App\Http\Resources\ContactoResource;
use App\Models\Contacto;
use App\Services\ContactoService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ContactoService $contactoService
    ) {}

    /**
     * Listar contactos por entidad
     */
    public function index(Request $request, $tipo, $id)
    {
        $this->authorize('contactos.ver');

        $entidadType = $this->resolveEntidadType($tipo);
        
        $contactos = $this->contactoService->listarPorEntidad($entidadType, $id);

        return $this->success([
            'total' => $contactos->count(),
            'items' => ContactoResource::collection($contactos)
        ]);
    }

    /**
     * Crear contacto
     */
    public function store(StoreContactoRequest $request)
    {
        $this->authorize('contactos.crear');

        $contacto = $this->contactoService->crear(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new ContactoResource($contacto),
            'Contacto creado exitosamente'
        );
    }

    /**
     * Ver contacto
     */
    public function show($id)
    {
        $this->authorize('contactos.ver');

        $contacto = Contacto::findOrFail($id);

        return $this->success(new ContactoResource($contacto));
    }

    /**
     * Editar contacto
     */
    public function update(UpdateContactoRequest $request, $id)
    {
        $this->authorize('contactos.editar');

        $contacto = $this->contactoService->actualizar(
            $id,
            $request->validated(),
            $request->user()->id
        );

        return $this->success(
            new ContactoResource($contacto),
            'Contacto actualizado exitosamente'
        );
    }

    /**
     * Eliminar contacto
     */
    public function destroy($id)
    {
        $this->authorize('contactos.eliminar');

        $this->contactoService->eliminar($id, request()->user()->id);

        return $this->success(null, 'Contacto eliminado exitosamente');
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