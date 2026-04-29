<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'usuarios.ver', 'usuarios.gestionar',
            'sucursales.ver', 'sucursales.crear', 'sucursales.editar', 'sucursales.eliminar',
            'menu.ver', 'menu.gestionar',
            'mesas.ver', 'mesas.gestionar',
            'pedidos.ver', 'pedidos.crear', 'pedidos.gestionar',
            'reservas.ver', 'reservas.gestionar',
            'empleados.ver', 'empleados.gestionar',
            'inventario.ver', 'inventario.gestionar',
            'caja.ver', 'caja.gestionar',
            'reportes.ver',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'super_admin' => $permissions,

            'gerente_sucursal' => [
                'usuarios.ver', 'usuarios.gestionar',
                'menu.ver', 'menu.gestionar',
                'mesas.ver', 'mesas.gestionar',
                'pedidos.ver',
                'reservas.ver', 'reservas.gestionar',
                'empleados.ver', 'empleados.gestionar',
                'inventario.ver', 'inventario.gestionar',
                'reportes.ver',
            ],

            'cajero' => [
                'pedidos.ver',
                'caja.ver', 'caja.gestionar',
                'menu.ver',
            ],

            'mozo' => [
                'menu.ver',
                'mesas.ver',
                'pedidos.ver', 'pedidos.crear', 'pedidos.gestionar',
                'reservas.ver', 'reservas.gestionar',
            ],

            'cocinero' => [
                'menu.ver',
                'pedidos.ver', 'pedidos.gestionar',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}