<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LoginBrandingPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador Prueba']);

        Permission::firstOrCreate([
            'titulo' => 'subitem_personalizacion_login',
            'descripcion' => 'Permite administrar las imágenes personalizadas de la pantalla de acceso.',
            'name' => 'configuraciones.subitem_personalizacion_login',
        ])->syncRoles([$superAdmin]);
    }
}
