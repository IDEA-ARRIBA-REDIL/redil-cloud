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

        $permisosDefinidos = [
            'novedades.item_novedades' => [
                'titulo' => 'item_novedades',
                'descripcion' => 'Permite ver el ítem Novedades en el menú principal',
            ],
            'novedades.subitem_gestionar_novedades' => [
                'titulo' => 'subitem_gestionar_novedades',
                'descripcion' => 'Permite ver el subítem Gestionar Novedades en el menú',
            ],
            'novedades.ver_novedades' => [
                'titulo' => 'ver_novedades',
                'descripcion' => 'Permite ver el listado y detalle de novedades e incidencias',
            ],
            'novedades.gestionar_novedades' => [
                'titulo' => 'gestionar_novedades',
                'descripcion' => 'Permite contestar novedades por correo, cambiar estados y administrar tipos de novedad',
            ],
            // Compatibilidad legado
            'actividades.ver_novedades' => [
                'titulo' => 'ver_novedades',
                'descripcion' => 'Permite ver el listado de novedades e incidencias de actividades',
            ],
            'actividades.gestionar_novedades' => [
                'titulo' => 'gestionar_novedades',
                'descripcion' => 'Permite contestar, cambiar estado y administrar tipos de novedad en actividades',
            ],
        ];

        $rolesToSync = array_filter([$superAdmin, $admin]);

        foreach ($permisosDefinidos as $name => $meta) {
            $permiso = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'titulo' => $meta['titulo'],
                    'descripcion' => $meta['descripcion'],
                ]
            );

            if (! empty($rolesToSync)) {
                $permiso->syncRoles($rolesToSync);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
