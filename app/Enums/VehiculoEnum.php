<?php

namespace App\Enums;

enum VehiculoEnum: string
{
    // Estados del vehículo
    case ACTIVO = 'ACTIVO';
    case INACTIVO = 'INACTIVO';

    // Tipos de vehículos 
    case AUTO = 'AUTO';
    case CAMIONETA = 'CAMIONETA';
    case MOTO = 'MOTO';
    case CAMION = 'CAMION';

    /**
     * Obtener texto amigable para la vista
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
            self::AUTO => 'Automóvil',
            self::CAMIONETA => 'Camioneta',
            self::MOTO => 'Motocicleta',
            self::CAMION => 'Camión',
        };
    }

    /**
     * Obtener todos los valores para validaciones
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}