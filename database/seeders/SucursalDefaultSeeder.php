<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalDefaultSeeder extends Seeder
{
    public function run(): void
    {
        Sucursal::firstOrCreate(
            ['codigo' => 'MATRIZ'],
            [
                'nombre' => 'Sucursal Matriz',
                'activa' => true,
                'email' => 'matriz@example.com',
                'direccion' => 'Dirección por defecto',
                'horario_apertura' => '08:00',
                'horario_cierre' => '18:00',
                'descripcion' => 'Sucursal principal',
            ]
        );

        $this->command->info('Sucursal por defecto creada.');
    }
}