<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\User;
use App\Models\Sucursal;
use App\Models\Sesion;
use App\Models\Archivo;
use App\Models\Auditoria;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Obtener todas las métricas del dashboard
     */
    public function getMetrics()
    {
        return [
            'total_personas' => $this->getTotalPersonas(),
            'total_usuarios' => $this->getTotalUsuarios(),
            'total_sucursales' => $this->getTotalSucursales(),
            'sesiones_activas' => $this->getSesionesActivas(),
            'documentos_vencidos' => $this->getDocumentosVencidos(),
            'documentos_por_vencer' => $this->getDocumentosPorVencer(),
            'usuarios_activos_hoy' => $this->getUsuariosActivosHoy(),
            'porcentaje_ocupacion' => $this->getPorcentajeOcupacion()
        ];
    }

    /**
     * Obtener actividad reciente (eventos de auditoría)
     */
    public function getActividadReciente(int $limit = 10)
    {
        return Auditoria::with(['usuario.persona'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($auditoria) {
                return [
                    'id' => $auditoria->id,
                    'usuario' => $auditoria->usuario ? [
                        'id' => $auditoria->usuario->id,
                        'nombre' => $auditoria->usuario->persona?->nombre_completo ?? $auditoria->usuario->username,
                        'email' => $auditoria->usuario->email
                    ] : null,
                    'accion' => $auditoria->accion,
                    'accion_traducida' => $this->traducirAccion($auditoria->accion),
                    'entidad_type' => class_basename($auditoria->entidad_type),
                    'entidad_id' => $auditoria->entidad_id,
                    'ip' => $auditoria->valores_nuevos['ip'] ?? null,
                    'fecha' => $auditoria->created_at->format('Y-m-d H:i:s'),
                    'fecha_humano' => $auditoria->created_at->diffForHumans(),
                    'icono' => $this->getIconoAccion($auditoria->accion),
                    'color' => $this->getColorAccion($auditoria->accion)
                ];
            });
    }

    /**
     * Obtener actividad por sucursal (si el usuario tiene una sucursal seleccionada)
     */
    public function getActividadPorSucursal(int $sucursalId, int $limit = 10)
    {
        // Buscar actividad relacionada con la sucursal
        $actividad = collect();

        // Actividad de usuarios de la sucursal
        $usuariosSucursal = User::whereHas('sucursales', function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        })->pluck('id');

        // Auditorías de esos usuarios
        $auditorias = Auditoria::with(['usuario.persona'])
            ->whereIn('usuario_id', $usuariosSucursal)
            ->latest()
            ->limit($limit)
            ->get();

        return $auditorias->map(function ($auditoria) {
            return [
                'id' => $auditoria->id,
                'usuario' => $auditoria->usuario ? [
                    'nombre' => $auditoria->usuario->persona?->nombre_completo ?? $auditoria->usuario->username
                ] : null,
                'accion' => $auditoria->accion,
                'fecha_humano' => $auditoria->created_at->diffForHumans()
            ];
        });
    }

    /**
     * Totales
     */
    private function getTotalPersonas(): array
    {
        return [
            'total' => Persona::count(),
            'fisicas' => Persona::where('tipo_persona', 'FISICA')->count(),
            'morales' => Persona::where('tipo_persona', 'MORAL')->count(),
            'activas' => Persona::where('estado', 'ACTIVO')->count(),
            'inactivas' => Persona::where('estado', 'INACTIVO')->count(),
            'bloqueadas' => Persona::where('estado', 'BLOQUEADO')->count(),
            'nuevas_hoy' => Persona::whereDate('created_at', today())->count(),
            'nuevas_semana' => Persona::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'nuevas_mes' => Persona::whereMonth('created_at', now()->month)->count()
        ];
    }

    private function getTotalUsuarios(): array
    {
        return [
            'total' => User::count(),
            'activos' => User::where('activo', true)->count(),
            'inactivos' => User::where('activo', false)->count(),
            'con_sucursal' => User::whereHas('sucursales')->count(),
            'sin_sucursal' => User::whereDoesntHave('sucursales')->count(),
            'nuevos_hoy' => User::whereDate('created_at', today())->count(),
            'con_sesion_hoy' => $this->getUsuariosActivosHoy()
        ];
    }

    private function getTotalSucursales(): array
    {
        return [
            'total' => Sucursal::count(),
            'activas' => Sucursal::where('activa', true)->count(),
            'inactivas' => Sucursal::where('activa', false)->count(),
            'con_usuarios' => Sucursal::whereHas('usuarios')->count(),
            'sin_usuarios' => Sucursal::whereDoesntHave('usuarios')->count()
        ];
    }

    private function getSesionesActivas(): array
    {
        $sesiones = Sesion::where('activa', true)
            ->with('usuario.persona')
            ->get();

        return [
            'total' => $sesiones->count(),
            'por_dispositivo' => [
                'postman' => $sesiones->where('device_name', 'Postman')->count(),
                'mobile' => $sesiones->where('device_name', 'mobile')->count(),
                'web' => $sesiones->where('device_name', 'web')->count(),
                'otros' => $sesiones->whereNull('device_name')->count()
            ],
            'ultima_actividad' => $sesiones->sortByDesc('login_at')->first()?->login_at?->diffForHumans(),
            'detalles' => $sesiones->map(function ($sesion) {
                return [
                    'usuario' => $sesion->usuario?->persona?->nombre_completo ?? $sesion->usuario?->username,
                    'dispositivo' => $sesion->device_name ?? 'Desconocido',
                    'ip' => $sesion->ip,
                    'ultima_actividad' => $sesion->login_at?->diffForHumans(),
                    'login_at' => $sesion->login_at?->format('Y-m-d H:i:s')
                ];
            })->take(5)
        ];
    }

    private function getDocumentosVencidos(): array
    {
        $hoy = now();
        
        $vencidos = Archivo::whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', $hoy)
            ->with('entidad')
            ->get();

        $porVencer = Archivo::whereNotNull('fecha_expiracion')
            ->whereBetween('fecha_expiracion', [$hoy, $hoy->copy()->addDays(30)])
            ->with('entidad')
            ->get();

        return [
            'vencidos' => [
                'total' => $vencidos->count(),
                'documentos' => $vencidos->map(function ($archivo) {
                    return [
                        'id' => $archivo->id,
                        'nombre' => $archivo->nombre,
                        'tipo' => $archivo->tipo,
                        'entidad' => $this->getEntidadInfo($archivo->entidad),
                        'fecha_expiracion' => $archivo->fecha_expiracion?->format('Y-m-d'),
                        'dias_vencido' => $archivo->fecha_expiracion?->diffInDays(now())
                    ];
                })->take(5)
            ],
            'por_vencer' => [
                'total' => $porVencer->count(),
                'documentos' => $porVencer->map(function ($archivo) {
                    return [
                        'id' => $archivo->id,
                        'nombre' => $archivo->nombre,
                        'tipo' => $archivo->tipo,
                        'entidad' => $this->getEntidadInfo($archivo->entidad),
                        'fecha_expiracion' => $archivo->fecha_expiracion?->format('Y-m-d'),
                        'dias_restantes' => now()->diffInDays($archivo->fecha_expiracion, false)
                    ];
                })->take(5)
            ]
        ];
    }

    private function getDocumentosPorVencer(): array
    {
        $porVencer = Archivo::whereNotNull('fecha_expiracion')
            ->whereBetween('fecha_expiracion', [now(), now()->addDays(30)])
            ->count();

        $proximos7Dias = Archivo::whereNotNull('fecha_expiracion')
            ->whereBetween('fecha_expiracion', [now(), now()->addDays(7)])
            ->count();

        return [
            'total' => $porVencer,
            'proximos_7_dias' => $proximos7Dias,
            'proximos_30_dias' => $porVencer
        ];
    }

    private function getUsuariosActivosHoy(): int
    {
        return Sesion::where('activa', true)
            ->whereDate('login_at', today())
            ->distinct('usuario_id')
            ->count('usuario_id');
    }

    private function getPorcentajeOcupacion(): array
    {
        $totalUsuarios = User::count();
        $totalSucursales = Sucursal::count();
        
        $usuariosConSucursal = User::whereHas('sucursales')->count();
        $sucursalesOcupadas = Sucursal::whereHas('usuarios')->count();

        return [
            'usuarios_asignados' => $totalUsuarios > 0 
                ? round(($usuariosConSucursal / $totalUsuarios) * 100, 2)
                : 0,
            'sucursales_con_usuarios' => $totalSucursales > 0
                ? round(($sucursalesOcupadas / $totalSucursales) * 100, 2)
                : 0
        ];
    }

    /**
     * Helpers
     */
    private function getEntidadInfo($entidad): ?array
    {
        if (!$entidad) return null;

        if ($entidad instanceof Persona) {
            return [
                'tipo' => 'Persona',
                'nombre' => $entidad->nombre_completo ?? $entidad->razon_social,
                'id' => $entidad->id
            ];
        }

        if ($entidad instanceof Sucursal) {
            return [
                'tipo' => 'Sucursal',
                'nombre' => $entidad->nombre,
                'id' => $entidad->id
            ];
        }

        return [
            'tipo' => class_basename($entidad),
            'id' => $entidad->id
        ];
    }

    private function traducirAccion(string $accion): string
    {
        return match($accion) {
            'LOGIN_SUCCESS' => 'Inicio de sesión',
            'LOGIN_FAILED' => 'Intento de login fallido',
            'LOGOUT' => 'Cierre de sesión',
            'REGISTER_SUCCESS' => 'Nuevo registro',
            'PASSWORD_CHANGED' => 'Cambio de contraseña',
            'SESSION_REVOKED' => 'Sesión cerrada remotamente',
            'ALL_SESSIONS_REVOKED' => 'Todas las sesiones cerradas',
            'SWITCH_BRANCH' => 'Cambio de sucursal',
            'SOLICITUD_UNION_CREADA' => 'Solicitud de unión a sucursal',
            'SOLICITUD_UNION_APROBADA' => 'Solicitud de unión aprobada',
            'SOLICITUD_UNION_RECHAZADA' => 'Solicitud de unión rechazada',
            default => $accion
        };
    }

    private function getIconoAccion(string $accion): string
    {
        return match($accion) {
            'LOGIN_SUCCESS' => 'login',
            'LOGOUT' => 'logout',
            'REGISTER_SUCCESS' => 'user-plus',
            'PASSWORD_CHANGED' => 'key',
            'SWITCH_BRANCH' => 'building',
            default => 'activity'
        };
    }

    private function getColorAccion(string $accion): string
    {
        return match($accion) {
            'LOGIN_SUCCESS', 'REGISTER_SUCCESS' => 'green',
            'LOGOUT', 'SESSION_REVOKED' => 'orange',
            'LOGIN_FAILED' => 'red',
            'PASSWORD_CHANGED' => 'blue',
            default => 'gray'
        };
    }
}