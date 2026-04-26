<?php
// app/Http/Controllers/Api/V1/vehiculoController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehiculo\StoreVehiculoRequest;
use App\Http\Requests\Vehiculo\UpdateVehiculoRequest;
use App\Http\Resources\Vehiculo\VehiculoResource;
use App\Http\Resources\Vehiculo\VehiculoListResource;
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class VehiculoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VehiculoService $vehiculoService
    ) {}

    /**
     * Listado de vehículos
     */
    public function index(Request $request)
    {
        $this->authorize('vehiculos.ver');

        $filtros = $request->all();
        $filtros['sucursal_id'] = $request->header('X-Branch-Id') ?? $filtros['sucursal_id'] ?? null;

        $vehiculos = $this->vehiculoService->listar($filtros, $request->get('per_page', 15));

        return $this->success([
            'items' => VehiculoListResource::collection($vehiculos),
            'meta' => [
                'current_page' => $vehiculos->currentPage(),
                'last_page' => $vehiculos->lastPage(),
                'total' => $vehiculos->total(),
            ]
        ], 'Lista de vehículos recuperada.');
    }

    /**
     * Registrar un nuevo vehículo
     */
    public function store(StoreVehiculoRequest $request)
    {
        $this->authorize('vehiculos.crear');

        try {
            $vehiculo = $this->vehiculoService->crear(
                $request->validated(), 
                Auth::id()
            );
            
            return $this->created(
                new VehiculoResource($vehiculo->load(['cliente.persona', 'sucursal'])),
                'Vehículo registrado correctamente.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Detalle de un vehículo específico
     */
    public function show(int $id)
    {
        try {
            $vehiculo = $this->vehiculoService->obtenerPorId($id);
            $this->authorize('vehiculos.ver', $vehiculo);

            return $this->success(new VehiculoResource($vehiculo));
        } catch (Exception $e) {
            return $this->error('Vehículo no encontrado.', 404);
        }
    }

    /**
     * Actualizar vehículo
     */
    public function update(UpdateVehiculoRequest $request, int $id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $this->authorize('vehiculos.editar', $vehiculo);

            $vehiculoActualizado = $this->vehiculoService->actualizar(
                $id, 
                $request->validated(), 
                Auth::id()
            );
            
            return $this->success(
                new VehiculoResource($vehiculoActualizado->load(['cliente.persona', 'sucursal'])),
                'Vehículo actualizado exitosamente.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Cambiar estado (Activar/Desactivar)
     */
    public function toggleStatus(Request $request, int $id)
    {
        $request->validate(['estado' => 'required|string']);
        
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $this->authorize('vehiculos.editar', $vehiculo);

            $vehiculoActualizado = $this->vehiculoService->cambiarEstado(
                $id, 
                $request->estado, 
                Auth::id()
            );

            return $this->success(
                new VehiculoResource($vehiculoActualizado->load('cliente.persona')),
                'Estado del vehículo actualizado.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Eliminación lógica del vehículo
     */
    public function destroy(int $id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $this->authorize('vehiculos.eliminar', $vehiculo);

            $this->vehiculoService->eliminar($id, Auth::id());
            
            return $this->success(null, 'Vehículo eliminado correctamente.');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Restaurar un vehículo eliminado
     */
    public function restore(int $id)
    {
        try {
            $vehiculo = Vehiculo::onlyTrashed()->findOrFail($id);
            $this->authorize('restore', $vehiculo);

            $vehiculoRestaurado = $this->vehiculoService->restaurar($id, Auth::id());

            return $this->success(
                new VehiculoResource($vehiculoRestaurado->load(['cliente.persona'])),
                'Vehículo restaurado exitosamente.'
            );
        } catch (Exception $e) {
            return $this->error('No se pudo restaurar el vehículo o no fue encontrado.', 404);
        }
    }
}