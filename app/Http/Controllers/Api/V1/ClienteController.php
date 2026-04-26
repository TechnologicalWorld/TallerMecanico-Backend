<?php

// app/Http/Controllers/Api/V1/ClienteController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Http\Requests\Cliente\UpdateClienteRequest;
use App\Http\Resources\Cliente\ClienteResource;
use App\Http\Resources\Cliente\ClienteListResource;
use App\Models\Cliente;
use App\Services\ClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ClienteService $clienteService
    ) {}

    /**
     * Listado de clientes 
     */
    public function index(Request $request)
    {
        $this->authorize('clientes.ver');

        $filtros = $request->all();
        $clientes = $this->clienteService->listar($request->all(), $request->get('per_page', 15));

        return $this->success([
            'items' => ClienteListResource::collection($clientes),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'total' => $clientes->total(),
            ]
        ], 'Lista de clientes recuperada.');
    }

    /**
     * Crear cliente
     */
    public function store(StoreClienteRequest $request)
    {
        $this->authorize('clientes.crear');

        try {
            $cliente = $this->clienteService->crear(
                $request->validated(), 
                Auth::id()
            );
            
            return $this->created(
                new ClienteResource($cliente->load('persona')),
                'Cliente registrado correctamente.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Detalle de un cliente
     */
    public function show(int $id)
    {
        $this->authorize('clientes.ver');

        try {
            $cliente = $this->clienteService->obtenerConRelaciones($id);
            return $this->success(new ClienteResource($cliente));
        } catch (Exception $e) {
            return $this->error('Cliente no encontrado.', 404);
        }
    }

    /**
     * Actualizar cliente
     */
    public function update(UpdateClienteRequest $request, int $id)
    {
        $this->authorize('clientes.editar');

        try {
            $clienteActualizado = $this->clienteService->actualizar(
                $id, 
                $request->validated(), 
                Auth::id()
            );
            
            return $this->success(
                new ClienteResource($clienteActualizado->load('persona')),
                'Información del cliente actualizada exitosamente.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Activar/Desactivar cliente
     */
    public function toggleStatus(Request $request, int $id)
    {
        $this->authorize('clientes.editar');

        $request->validate(['activo' => 'required|boolean']);

        try {
            $cliente = $this->clienteService->cambiarEstado(
                $id, 
                $request->activo, 
                Auth::id()
            );
            
            $statusText = $request->activo ? 'activado' : 'desactivado';

            return $this->success(
                new ClienteResource($cliente->load('persona')),
                "Cliente $statusText correctamente."
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Borrado lógico
     */
    public function destroy(int $id)
    {
        $this->authorize('clientes.eliminar');

        try {
            $this->clienteService->eliminar($id, Auth::id());
            return $this->success(null, 'Cliente eliminado correctamente.');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Restaurar cliente eliminado
     */
    public function restore(int $id)
    {
        $this->authorize('clientes.editar');

        try {
            $cliente = $this->clienteService->restaurar($id, Auth::id());
            
            return $this->success(
                new ClienteResource($cliente->load('persona')),
                'Cliente restaurado correctamente.'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}