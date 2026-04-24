<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Archivo\StoreArchivoRequest;
use App\Http\Resources\ArchivoResource;
use App\Models\Archivo;
use App\Services\ArchivoService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ArchivoService $archivoService
    ) {}

    /**
     * Listar archivos por entidad
     */
    public function index(Request $request, $tipo, $id)
    {
        $this->authorize('archivos.ver');

        $entidadType = $this->resolveEntidadType($tipo);
        $tipoArchivo = $request->input('tipo');
        
        $archivos = $this->archivoService->listarPorEntidad($entidadType, $id, $tipoArchivo);

        return $this->success([
            'total' => $archivos->count(),
            'items' => ArchivoResource::collection($archivos)
        ]);
    }

    /**
     * Subir archivo
     */
    public function store(StoreArchivoRequest $request)
    {
        $this->authorize('archivos.subir');

        $archivo = $this->archivoService->subir(
            $request->validated(),
            $request->file('archivo'),
            $request->user()->id
        );

        return $this->created(
            new ArchivoResource($archivo),
            'Archivo subido exitosamente'
        );
    }

    /**
     * Ver información del archivo
     */
    public function show($id)
    {
        $this->authorize('archivos.ver');

        $archivo = Archivo::with(['entidad'])->findOrFail($id);

        return $this->success(new ArchivoResource($archivo));
    }

    /**
     * Descargar archivo
     */
    public function download($id)
    {
        $this->authorize('archivos.ver');

        try {
            return $this->archivoService->descargar($id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Obtener URL pública del archivo
     */
    public function url($id)
    {
        $this->authorize('archivos.ver');

        try {
            $url = $this->archivoService->url($id);
            return $this->success(['url' => $url]);
        } catch (\Exception $e) {
            return $this->error('Archivo no encontrado', 404);
        }
    }

    /**
     * Restaurar domicilio eliminado
     */
    public function restore($id)
    {
        $this->authorize('archivos.eliminar');

        $domicilio = $this->archivoService->restaurar($id, request()->user()->id);

        return $this->success(
            new ArchivoResource($domicilio),
            'Archivo restaurado exitosamente'
        );
    }


    /**
     * Eliminar archivo (lógico)
     */
    public function destroy($id)
    {
        $this->authorize('archivos.eliminar');

        try {
            $this->archivoService->eliminar($id, request()->user()->id);
            return $this->success(null, 'Archivo eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Eliminar archivo permanentemente (solo admin)
     */
    public function forceDestroy($id)
    {
        $this->authorize('archivos.eliminar_permanente');

        try {
            $this->archivoService->eliminarPermanente($id, request()->user()->id);
            return $this->success(null, 'Archivo eliminado permanentemente');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
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