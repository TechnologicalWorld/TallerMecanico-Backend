<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $sucursalMatriz = Sucursal::where('codigo', 'MATRIZ')->first();

        if (!$sucursalMatriz) {
            $this->command->error('Ejecuta primero SucursalDefaultSeeder.');
            return;
        }

        $persona = Persona::firstOrCreate(
            ['identificacion_principal' => 'KaeReyes123'],
            [
                'tipo_persona' => 'FISICA',
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'fecha_nacimiento' => '1990-01-01',
                'genero' => 'Otro',
                'estado' => 'ACTIVO',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'persona_id' => $persona->id,
                'username' => 'admin',
                'password' => Hash::make('password'),
                'activo' => true,
                'current_branch_id' => $sucursalMatriz->id,
            ]
        );

        setPermissionsTeamId($sucursalMatriz->id);

        $admin->assignRole('Super Admin', $sucursalMatriz->id);
        $admin->sucursales()->syncWithoutDetaching([
            $sucursalMatriz->id => ['es_administrador' => true, 'activo' => true]
        ]);

        $personaK = Persona::firstOrCreate(
            ['identificacion_principal' => 'KaeReyes1234'],
            [
                'tipo_persona' => 'FISICA',
                'nombre' => 'Kae',
                'apellido' => 'Reyes',
                'fecha_nacimiento' => '1990-01-01',
                'genero' => 'Otro',
                'estado' => 'ACTIVO',
            ]
        );

        $adminK = User::firstOrCreate(
            ['email' => 'kae@example.com'],
            [
                'persona_id' => $personaK->id,
                'username' => 'kaeR',
                'password' => Hash::make('password'),
                'activo' => true,
                'current_branch_id' => $sucursalMatriz->id,
            ]
        );


        $adminK->assignRole('Super Admin', $sucursalMatriz->id);

        $adminK->sucursales()->syncWithoutDetaching([
            $sucursalMatriz->id => ['es_administrador' => true, 'activo' => true]
        ]);

        $this->command->info('Usuario administrador creado.');
    }
}