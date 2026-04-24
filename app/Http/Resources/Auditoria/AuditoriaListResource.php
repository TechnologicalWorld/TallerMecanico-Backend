<?php

namespace App\Http\Resources\Auditoria;

use App\Http\Resources\User\UserListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditoriaListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario' => $this->whenLoaded('usuario', function() {
                return [
                    'id' => $this->usuario?->id,
                    'nombre' => $this->usuario?->persona?->nombre_completo ?? $this->usuario?->username,
                    'email' => $this->usuario?->email,
                ];
            }),
            'accion' => $this->accion,
            'accion_texto' => $this->getAccionTexto(),
            'entidad_type' => $this->entidad_type,
            'entidad_nombre' => class_basename($this->entidad_type),
            'entidad_id' => $this->entidad_id,
            'ip' => $this->valores_nuevos['ip'] ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_humano' => $this->created_at?->diffForHumans(),
            'fecha' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function getAccionTexto(): string
    {
        $acciones = [
            'LOGIN_SUCCESS' => 'Inicio de sesión',
            'LOGIN_FAILED' => 'Intento de login fallido',
            'LOGOUT' => 'Cierre de sesión',
            'REGISTER_SUCCESS' => 'Registro de usuario',
            'PASSWORD_CHANGED' => 'Cambio de contraseña',
            'PASSWORD_RESET_REQUESTED' => 'Solicitud de recuperación',
            'PASSWORD_RESET_COMPLETED' => 'Recuperación completada',
            'SESSION_REVOKED' => 'Sesión cerrada',
            'ALL_SESSIONS_REVOKED' => 'Todas las sesiones cerradas',
            'SWITCH_BRANCH' => 'Cambio de sucursal',
            'DASHBOARD_ACCESS' => 'Acceso a dashboard',
            
            'PERSONA_CREADA' => 'Persona creada',
            'PERSONA_ACTUALIZADA' => 'Persona actualizada',
            'PERSONA_ESTADO_CAMBIADO' => 'Estado de persona cambiado',
            'PERSONA_ELIMINADA' => 'Persona eliminada',
            'PERSONA_RESTAURADA' => 'Persona restaurada',
            
            'USUARIO_CREADO' => 'Usuario creado',
            'USUARIO_ACTUALIZADO' => 'Usuario actualizado',
            'USUARIO_ACTIVADO' => 'Usuario activado',
            'USUARIO_DESACTIVADO' => 'Usuario desactivado',
            'USUARIO_ASIGNADO_SUCURSAL' => 'Usuario asignado a sucursal',
            'USUARIO_QUITADO_SUCURSAL' => 'Usuario removido de sucursal',
            
            'SUCURSAL_CREADA' => 'Sucursal creada',
            'SUCURSAL_ACTUALIZADA' => 'Sucursal actualizada',
            'SUCURSAL_ACTIVADA' => 'Sucursal activada',
            'SUCURSAL_DESACTIVADA' => 'Sucursal desactivada',
            
            'CONTACTO_CREADO' => 'Contacto creado',
            'CONTACTO_ACTUALIZADO' => 'Contacto actualizado',
            'CONTACTO_ELIMINADO' => 'Contacto eliminado',
            
            'DOMICILIO_CREADO' => 'Domicilio creado',
            'DOMICILIO_ACTUALIZADO' => 'Domicilio actualizado',
            'DOMICILIO_ELIMINADO' => 'Domicilio eliminado',
            'DOMICILIO_RESTAURADO' => 'Domicilio restaurado',
            
            'ARCHIVO_SUBIDO' => 'Archivo subido',
            'ARCHIVO_ELIMINADO' => 'Archivo eliminado',
            'ARCHIVO_ELIMINADO_PERMANENTE' => 'Archivo eliminado permanentemente',
            
            'ROL_CREADO' => 'Rol creado',
            'ROL_ACTUALIZADO' => 'Rol actualizado',
            'ROL_ELIMINADO' => 'Rol eliminado',
            'ROL_PERMISOS_SINCRONIZADOS' => 'Permisos sincronizados',
            'PERMISO_CREADO' => 'Permiso creado',
            'PERMISO_ACTUALIZADO' => 'Permiso actualizado',
            'PERMISO_ELIMINADO' => 'Permiso eliminado',
            
            'SOLICITUD_UNION_CREADA' => 'Solicitud de unión creada',
            'SOLICITUD_UNION_APROBADA' => 'Solicitud de unión aprobada',
            'SOLICITUD_UNION_RECHAZADA' => 'Solicitud de unión rechazada',
            'SOLICITUD_UNION_CANCELADA' => 'Solicitud de unión cancelada',
        ];

        return $acciones[$this->accion] ?? $this->accion;
    }

}