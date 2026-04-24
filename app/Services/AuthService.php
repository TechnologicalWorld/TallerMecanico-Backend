<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $login, string $password, ?string $deviceName, string $ip, ?string $userAgent)
    {
        return DB::transaction(function () use ($login, $password, $deviceName, $ip, $userAgent) {
            $user = User::where('email', $login)
                ->orWhere('username', $login)
                ->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                $this->registerAudit(null, 'LOGIN_FAILED', [
                    'login' => $login,
                    'ip' => $ip,
                    'reason' => 'invalid_credentials',
                ]);

                throw ValidationException::withMessages([
                    'login' => ['Las credenciales son incorrectas'],
                ]);
            }

            if (! $user->activo) {
                $this->registerAudit($user->id, 'LOGIN_FAILED', [
                    'ip' => $ip,
                    'reason' => 'inactive_user',
                ]);

                throw ValidationException::withMessages([
                    'activo' => ['El usuario está inactivo'],
                ]);
            }

            $sucursalActual = $user->currentBranch ??
                             $user->sucursales()->wherePivot('activo', true)->first();

            $tokenName = $deviceName ?? $this->parseDeviceName($userAgent);
            $accessToken = $user->createToken($tokenName, ['*']);
            $tokenId = $accessToken->accessToken->id; 
            $tokenString = $accessToken->plainTextToken; 

            $sesion = $user->sesiones()->create([
                'token_id' => $tokenId, 
                'token' => $tokenString,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'device_name' => $deviceName,
                'login_at' => now(),
                'login_at' => now(),
                'activa' => true,
            ]);
            $this->registerAudit($user->id, 'LOGIN_SUCCESS', [
                'session_id' => $sesion->id,
                'ip' => $ip,
                'device' => $deviceName,
            ]);

            $user->load(['persona', 'sucursales' => function ($q) {
                $q->wherePivot('activo', true);
            }]);

            return [
                'user' => $user,
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'sucursal_actual' => $sucursalActual,
                'session_id' => $sesion->id,
            ];
        });
    }

    /**
     * Logout - Cerrar sesión actual
     */
    public function logout(User $user, $token, string $ip)
    {
        return DB::transaction(function () use ($user, $token, $ip) {
            $sesion = $user->sesiones()
                ->where('token', $token->token)
                ->where('activa', true)
                ->first();

            if ($sesion) {
                $sesion->update([
                    'activa' => false,
                    'logout_at' => now(),
                ]);
            }

            $token->delete();

            $this->registerAudit($user->id, 'LOGOUT', [
                'session_id' => $sesion?->id,
                'ip' => $ip,
            ]);

            return true;
        });
    }

    /**
     * Listar sesiones activas (dispositivos conectados)
     */
    public function listSessions(User $user)
    {
        return $user->sesiones()
            ->where('activa', true)
            ->orderBy('logout_at', 'desc')
            ->orderBy('login_at', 'desc')
            ->get();
    }

    /**
     * Cerrar sesión específica (revocar dispositivo)
     */
    public function revokeSession(User $user, int $sessionId, string $ip)
    {
        return DB::transaction(function () use ($user, $sessionId, $ip) {
            $sesion = $user->sesiones()
                ->where('id', $sessionId)
                ->where('activa', true)
                ->firstOrFail();

            $personalToken = $user->tokens()
                ->where('token', $sesion->token)
                ->first();

            if ($personalToken) {
                $personalToken->delete();
            }

            $sesion->update([
                'activa' => false,
                'logout_at' => now(),
            ]);

            $this->registerAudit($user->id, 'SESSION_REVOKED', [
                'session_id' => $sessionId,
                'ip' => $ip,
            ]);

            return true;
        });
    }

    /**
     * Cerrar todas las sesiones excepto la actual
     */
    public function revokeAllSessionsExcept(User $user, string $ip)
    {
        return DB::transaction(function () use ($user, $ip) {
            $currentToken = $user->currentAccessToken();

            if (! $currentToken) {
                return 0;
            }

            $sesiones = $user->sesiones()
                ->where('activa', true)
                ->where('token', '!=', $currentToken->token)
                ->get();

            foreach ($sesiones as $sesion) {
                $token = $user->tokens()
                    ->where('token', $sesion->token)
                    ->first();

                if ($token) {
                    $token->delete();
                }

                $sesion->update([
                    'activa' => false,
                    'logout_at' => now(),
                ]);
            }

            $this->registerAudit($user->id, 'ALL_SESSIONS_REVOKED', [
                'count' => $sesiones->count(),
                'ip' => $ip,
            ]);

            return $sesiones->count();
        });
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword, bool $logoutOthers, string $ip)
    {
        return DB::transaction(function () use ($user, $currentPassword, $newPassword, $logoutOthers, $ip) {
            if (! Hash::check($currentPassword, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['La contraseña actual es incorrecta'],
                ]);
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            if ($logoutOthers) {
                $this->revokeAllSessionsExcept($user, $ip);
            }

            $this->registerAudit($user->id, 'PASSWORD_CHANGED', [
                'ip' => $ip,
                'logout_others' => $logoutOthers,
            ]);

            return true;
        });
    }

    /**
     * Extraer nombre del dispositivo del User Agent
     */
    private function parseDeviceName(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile')) {
            return 'mobile';
        }
        if (str_contains($userAgent, 'android')) {
            return 'android';
        }
        if (str_contains($userAgent, 'iphone')) {
            return 'iphone';
        }
        if (str_contains($userAgent, 'ipad')) {
            return 'ipad';
        }
        if (str_contains($userAgent, 'postman')) {
            return 'postman';
        }
        if (str_contains($userAgent, 'windows')) {
            return 'windows';
        }
        if (str_contains($userAgent, 'mac')) {
            return 'mac';
        }
        if (str_contains($userAgent, 'linux')) {
            return 'linux';
        }

        return 'web';
    }

    /**
     * Registrar auditoría
     */
    private function registerAudit(?int $userId, string $accion, array $data = []): void
    {
        Auditoria::create([
            'usuario_id' => $userId,
            'accion' => $accion,
            'entidad_type' => 'App\Models\User',
            'entidad_id' => $userId,
            'valores_nuevos' => $data,
            'created_at' => now(),
        ]);
    }

    /**
     * Registrar nuevo usuario
     */
    public function register(array $data, string $ip, ?string $userAgent)
    {
        return DB::transaction(function () use ($data, $ip, $userAgent) {
            $personaData = [
                'tipo_persona' => $data['tipo_persona'],
                'identificacion_principal' => $data['identificacion_principal'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero' => $data['genero'] ?? null,
                'estado' => 'ACTIVO',
            ];

            if ($data['tipo_persona'] === 'FISICA') {
                $personaData['nombre'] = $data['nombre'];
                $personaData['apellido'] = $data['apellido'];
            } else {
                $personaData['razon_social'] = $data['razon_social'];
            }

            $persona = Persona::create($personaData);

            $user = User::create([
                'persona_id' => $persona->id,
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'activo' => true,
            ]);

            $sucursal = null;

            if (! empty($data['sucursal_codigo'])) {
                $sucursal = Sucursal::where('codigo', $data['sucursal_codigo'])->first();
            }

            // if (! $sucursal) {
            //     $sucursal = Sucursal::where('codigo', 'MATRIZ')->first();
            // }

            // setPermissionsTeamId($sucursal->id);
            // if ($sucursal) {
            //     $user->sucursales()->attach($sucursal->id, [
            //         'es_administrador' => false,
            //         'activo' => true,
            //     ]);
            //     $user->current_branch_id = $sucursal->id;
            //     $user->save();
            // }

            // $user->assignRole('Cliente', $sucursal?->id);

            $tokenName = $data['device_name'] ?? $this->parseDeviceName($userAgent);
            $accessToken = $user->createToken($tokenName, ['*'])->plainTextToken;

            $sesion = $user->sesiones()->create([
                'token' => explode('|', $accessToken)[1] ?? $accessToken,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'device_name' => $data['device_name'] ?? null,
                'login_at' => now(),
                'logout_at' => now(),
                'activa' => true,
            ]);

            $this->registerAudit($user->id, 'REGISTER_SUCCESS', [
                'session_id' => $sesion->id,
                'ip' => $ip,
                'sucursal_id' => $sucursal?->id,
            ]);

            $user->load(['persona', 'sucursales']);

            return [
                'user' => $user,
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'sucursal_asignada' => $sucursal,
                'session_id' => $sesion->id,
            ];
        });
    }
}
