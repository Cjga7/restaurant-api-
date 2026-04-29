<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Sucursal de prueba
        $sucursalId = DB::table('sucursales')->insertGetId([
            'nombre'     => 'Sucursal Central',
            'direccion'  => 'Av. Principal 123',
            'ciudad'     => 'Cochabamba',
            'telefono'   => '4412345',
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Super Admin (sin sucursal)
        $superAdmin = User::create([
            'name'        => 'Super Admin',
            'email'       => 'admin@restaurant.test',
            'password'    => Hash::make('password'),
            'sucursal_id' => null,
        ]);
        $superAdmin->assignRole('super_admin');

        // Gerente de prueba (con sucursal)
        $gerente = User::create([
            'name'        => 'Gerente Central',
            'email'       => 'gerente@restaurant.test',
            'password'    => Hash::make('password'),
            'sucursal_id' => $sucursalId,
        ]);
        $gerente->assignRole('gerente_sucursal');
    }
}