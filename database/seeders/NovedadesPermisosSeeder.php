<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NovedadesPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::where('name', 'like', '%Super Administrador%')->first();
        $admin = Role::where('name', 'like', '%Administrativo%')->first();

        $permisoVer = Permission::firstOrCreate(
            ['name' => 'actividades.ver_novedades'],
            [
                'titulo' => 'ver_novedades',
                'descripcion' => 'Permite ver el listado de novedades e incidencias de actividades',
            ]
        );

        $permisoGestionar = Permission::firstOrCreate(
            ['name' => 'actividades.gestionar_novedades'],
            [
                'titulo' => 'gestionar_novedades',
                'descripcion' => 'Permite contestar, cambiar estado y administrar tipos de novedad en actividades',
            ]
        );

        $rolesToSync = array_filter([$superAdmin, $admin]);
        if (! empty($rolesToSync)) {
            $permisoVer->syncRoles($rolesToSync);
            $permisoGestionar->syncRoles($rolesToSync);
        }
    }
}
