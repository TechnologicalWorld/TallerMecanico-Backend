<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asignacion\AsignarSucursalRequest;
use App\Http\Resources\Asignacion\AsignacionResource;
use App\Services\AsignacionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AsignacionService $asignacionService
    ) {}

    /**
     * Asignar usuario a sucursal
     */
    public function store(AsignarSucursalRequest $request)
    {
        $this->authorize('asignaciones.asignar');

        $asignacion = $this->asignacionService->asignar(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            new AsignacionResource($asignacion),
            'Usuario asignado a sucursal exitosamente'
        );
    }

    /**
     * Quitar asignación
     */
    public function destroy($idSucursal,$idUsuario)
    {
        $this->authorize('asignaciones.quitar');
        
        $this->asignacionService->quitar($idSucursal,$idUsuario, request()->user()->id);
        return $this->success(null, 'Asignación eliminada exitosamente');

    }
}
