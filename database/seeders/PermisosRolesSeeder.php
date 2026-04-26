<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosRolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            'personas.ver',
            'personas.crear',
            'personas.editar',
            'personas.eliminar',
            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.eliminar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',
            'permisos.ver',
            'permisos.asignar',

            'reportes.ver',
            'reportes.exportar',

            'archivos.subir',
            'archivos.eliminar',
            'archivos.eliminar',
            'archivos.eliminar_permanente',
            'archivos.ver',

            'auditoria.ver',

            'dashboard.ver',
            'dashboard.exportar',
            'dashboard.ver_sucursal',

            'contactos.ver',
            'contactos.crear',
            'contactos.editar',
            'contactos.eliminar',

            'domicilios.ver',
            'domicilios.crear',
            'domicilios.editar',
            'domicilios.eliminar',

            'asignaciones.ver',
            'asignaciones.asignar',
            'asignaciones.quitar',

            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            'permisos.ver',
            'permisos.crear',
            'permisos.editar',
            'permisos.eliminar',

            'asignaciones.ver',
            'asignaciones.asignar',
            'asignaciones.quitar',

            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            'permisos.ver',
            'permisos.crear',
            'permisos.editar',
            'permisos.eliminar',

            'auditoria.ver',
            'auditoria.exportar',
            
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            'vehiculos.ver',
            'vehiculos.crear',
            'vehiculos.editar',
            'vehiculos.eliminar',
            
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'api');
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'api');
        $superAdmin->givePermissionTo(Permission::all());

        $adminSucursal = Role::findOrCreate('Administrador de Sucursal', 'api');
        $adminSucursal->givePermissionTo([
            'personas.ver',
            'personas.crear',
            'personas.editar',
            'personas.eliminar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'sucursales.ver',
            'sucursales.editar',
            'archivos.subir',
            'archivos.eliminar',
            'reportes.ver',
            'reportes.exportar',

            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',
            'permisos.ver',
            'permisos.asignar',
        ]);

        $empleado = Role::findOrCreate('Empleado', 'api');
        $empleado->givePermissionTo([
            'personas.ver',
            'personas.crear',
            'personas.editar',
            'sucursales.ver',
            'archivos.subir',
            'reportes.ver',
        ]);

        $cliente = Role::findOrCreate('Cliente', 'api');
        $cliente->givePermissionTo([
            'personas.ver',
        ]);

        $this->command->info('Permisos y roles creados correctamente con guard api.');
    }
}
