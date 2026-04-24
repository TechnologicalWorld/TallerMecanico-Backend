<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\User;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Dashboard general (requiere permisos de admin)
     */
    public function index(Request $request)
    {
        // Verificar permisos
        if (!$request->user()->can('dashboard.ver')) {
            return $this->error('No tienes permisos para ver el dashboard', 403);
        }

        $metrics = $this->dashboardService->getMetrics();
        $actividad = $this->dashboardService->getActividadReciente(15);

        $data = array_merge($metrics, [
            'actividad_reciente' => $actividad
        ]);

        return $this->success($data);
    }

    /**
     * Dashboard resumido para usuarios normales
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        
        $data = [
            'mis_sucursales' => $user->sucursales()->count(),
            'mi_ultima_sesion' => $user->sesiones()
                ->where('activa', true)
                ->latest('login_at')
                ->first()?->login_at?->diffForHumans(),
            'tiene_sucursal_activa' => !is_null($user->current_branch_id),
            'sucursal_actual' => $user->currentBranch?->nombre,
            'total_sesiones_activas' => $user->sesiones()->where('activa', true)->count()
        ];

        return $this->success($data);
    }

    /**
     * Dashboard por sucursal (para administradores de sucursal)
     */
    public function branch(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->tieneSucursalActiva($id)) {
            return $this->error('No tienes acceso a esta sucursal', 403);
        }

        $actividad = $this->dashboardService->getActividadPorSucursal($id, 10);

        $data = [
            'sucursal_id' => $id,
            'actividad_reciente' => $actividad,
            'estadisticas' => [
                'usuarios_en_sucursal' => User::whereHas('sucursales', function ($q) use ($id) {
                    $q->where('sucursal_id', $id);
                })->count(),
                'documentos_sucursal' => Archivo::where('entidad_type', 'App\Models\Sucursal')
                    ->where('entidad_id', $id)
                    ->count()
            ]
        ];

        return $this->success($data);
    }
}