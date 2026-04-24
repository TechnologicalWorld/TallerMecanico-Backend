<?php

namespace App\Enums;

enum TipoContactoEnum: string
{
    case EMAIL = 'EMAIL';
    case TELEFONO = 'TELEFONO';
    case OTRO = 'OTRO';

    public function label(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::TELEFONO => 'Teléfono',
            self::OTRO => 'Otro',
        };
    }
}