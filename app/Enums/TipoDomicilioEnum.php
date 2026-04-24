<?php

namespace App\Enums;

enum TipoDomicilioEnum: string
{
    case FISCAL = 'FISCAL';
    case PARTICULAR = 'PARTICULAR';
    case ENTREGA = 'ENTREGA';
    case OTRO = 'OTRO';

    public function label(): string
    {
        return match($this) {
            self::FISCAL => 'Fiscal',
            self::PARTICULAR => 'Particular',
            self::ENTREGA => 'Entrega',
            self::OTRO => 'Otro',
        };
    }
}