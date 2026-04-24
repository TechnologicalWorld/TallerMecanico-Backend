<?php

namespace App\Enums;

enum TipoArchivoEnum: string
{
    case CI = 'CI';
    case CONTRATO = 'CONTRATO';
    case CERTIFICADO = 'CERTIFICADO';
    case FOTO = 'FOTO';
    case OTRO = 'OTRO';

    public function label(): string
    {
        return match($this) {
            self::CI => 'CI',
            self::CONTRATO => 'Contrato',
            self::CERTIFICADO => 'Certificado',
            self::FOTO => 'Foto',
            self::OTRO => 'Otro',
        };
    }
}