<?php

namespace App\Enums;

enum EstadoPersonaEnum: string
{
    case ACTIVO = 'ACTIVO';
    case INACTIVO = 'INACTIVO';
    case BLOQUEADO = 'BLOQUEADO';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
            self::BLOQUEADO => 'Bloqueado',
        };
    }

    public function isActivo(): bool
    {
        return $this === self::ACTIVO;
    }
}