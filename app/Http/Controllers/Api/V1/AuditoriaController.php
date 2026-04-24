<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auditoria\FiltrarAuditoriaRequest;
use App\Http\Resources\Auditoria\AuditoriaDetailResource;
use App\Http\Resources\Auditoria\AuditoriaListResource;
use App\Services\AuditoriaService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuditoriaService $auditoriaService
    ) {}

    /**
     * Listar logs con filtros
     */
    public function index(FiltrarAuditoriaRequest $request)
    {
        $this->authorize('auditoria.ver');

        $filtros = $request->validated();
        $perPage = $request->get('per_page', 15);
        
        $logs = $this->auditoriaService->listar($filtros, $perPage);

        return $this->success([
            'items' => AuditoriaListResource::collection($logs),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ]
        ]);
    }

    /**
     * Ver detalle de un evento
     */
    public function show($id)
    {
        $this->authorize('auditoria.ver');

        $log = $this->auditoriaService->obtenerEvento($id);

        return $this->success(new AuditoriaDetailResource($log));
    }

    /**
     * Exportar logs
     */
    public function exportar(FiltrarAuditoriaRequest $request)
    {
        $this->authorize('auditoria.exportar');

        $filtros = $request->validated();
        $logs = $this->auditoriaService->exportar($filtros);

        return $this->success([
            'total' => count($logs),
            'fecha_exportacion' => now()->format('Y-m-d H:i:s'),
            'data' => $logs
        ]);
    }

    /**
     * Obtener acciones disponibles (para filtros)
     */
    public function acciones(Request $request)
    {
        $this->authorize('auditoria.ver');

        $acciones = \App\Models\Auditoria::select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion')
            ->map(function ($accion) {
            return [
                    'value' => $accion,
                ];
            });

        return $this->success($acciones);
    }

    /**
     * Obtener entidades disponibles (para filtros)
     */
    public function entidades(Request $request)
    {
        $this->authorize('auditoria.ver');

        $entidades = \App\Models\Auditoria::select('entidad_type')
            ->distinct()
            ->orderBy('entidad_type')
            ->pluck('entidad_type')
            ->map(function ($entidad) {
                return [
                    'value' => $entidad,
                    'label' => class_basename($entidad),
                ];
            });

        return $this->success($entidades);
    }
}