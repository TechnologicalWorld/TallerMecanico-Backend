<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentToken = $request->user()->currentAccessToken();

        return [
            'id' => $this->id,
            'dispositivo' => $this->device_name ?? $this->parseDevice(),
            'ip' => $this->ip,
            'ultima_actividad' => $this->login_at?->diffForHumans(),
            'login_at' => $this->login_at?->format('Y-m-d H:i:s'),
            'es_actual' => $currentToken && $this->token_id === $currentToken->id,
            'currentToken'=>$currentToken,
            '$this->token_id'=>$this->token_id,
            '$currentToken->id,'=>$currentToken->id,
            'activa' => $this->activa
        ];
    }

    private function parseDevice(): string
    {
        $ua = strtolower($this->user_agent ?? '');

        if (str_contains($ua, 'mobile')) return 'Móvil';
        if (str_contains($ua, 'android')) return 'Android';
        if (str_contains($ua, 'iphone')) return 'iPhone';
        if (str_contains($ua, 'ipad')) return 'iPad';
        if (str_contains($ua, 'postman')) return 'Postman';
        if (str_contains($ua, 'windows')) return 'Windows';
        if (str_contains($ua, 'mac')) return 'Mac';

        return 'Web';
    }
}
