<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\SessionResource;
use App\Http\Resources\Auth\UserProfileResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register - Registrar nuevo usuario
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return $this->created($result, 'Usuario registrado exitosamente');
    }

    /**
     * Login - Autenticar usuario
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->login,
            $request->password,
            $request->device_name,
            $request->ip(),
            $request->userAgent()
        );

        return $this->success($result, 'Login exitoso');
    }

    /**
     * Logout - Cerrar sesión actual
     */
    public function logout(Request $request)
    {
        $this->authService->logout(
            $request->user(),
            $request->user()->currentAccessToken(),
            $request->ip()
        );

        return $this->success(null, 'Sesión cerrada correctamente');
    }

    /**
     * Perfil autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user()->load([
            'persona',
            'sucursales' => fn ($q) => $q->wherePivot('activo', true),
        ]);

        return $this->success(
            new UserProfileResource($user)
        );
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password,
            $request->boolean('logout_others', true),
            $request->ip()
        );

        return $this->success(null, 'Contraseña actualizada correctamente');
    }

    /**
     * Listar dispositivos/sesiones activas
     */
    public function sessions(Request $request)
    {
        $sessions = $this->authService->listSessions($request->user());

        return $this->success(
            SessionResource::collection($sessions)
        );
    }

    /**
     * Cerrar sesión específica (dispositivo específico)
     */
    public function revokeSession(Request $request, $id)
    {
        $this->authService->revokeSession(
            $request->user(),
            $id,
            $request->ip()
        );

        return $this->success(null, 'Sesión cerrada correctamente');
    }

    /**
     * Cerrar todas las demás sesiones
     */
    public function revokeAllSessions(Request $request)
    {
        $count = $this->authService->revokeAllSessionsExcept(
            $request->user(),
            $request->ip()
        );

        return $this->success(
            ['sessions_revoked' => $count],
            "Se cerraron {$count} sesiones"
        );
    }
}
