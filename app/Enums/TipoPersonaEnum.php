<?php

namespace App\Enums;

enum TipoPersonaEnum: string
{
    case FISICA = 'FISICA';
    case MORAL = 'MORAL';

    public function label(): string
    {
        return match($this) {
            self::FISICA => 'Física',
            self::MORAL => 'Moral',
        };
    }
}