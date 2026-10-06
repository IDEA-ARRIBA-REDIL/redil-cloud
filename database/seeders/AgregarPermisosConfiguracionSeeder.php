<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AgregarPermisosConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdministrador = Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'Super Administrador')
            ->first() ?? Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'Super Administrador Prueba')
            ->first();

        if (! $rolAdministrador) {
            throw new \LogicException('No existe un rol de superadministrador; no se agregaron permisos.');
        }

        $permisos = [
            'configuraciones.subitem_profesiones' => 'Configuracion_profesiones',
            'configuraciones.subitem_tipo_identificaciones' => 'Configuracion_tipo_identificaciones',
            'configuraciones.subitem_ocupaciones' => 'Configuracion_ocupaciones',
            'configuraciones.subitem_sectores_economicos' => 'Configuracion_sectores_economicos',
            'configuraciones.subitem_estados_civiles' => 'Configuracion_estados_civiles',
            'configuraciones.subitem_tipo_vinculaciones' => 'Configuracion_tipo_vinculaciones',
        ];

        foreach ($permisos as $nombre => $titulo) {
            $permiso = Permission::query()->firstOrCreate(
                ['name' => $nombre, 'guard_name' => 'web'],
                ['titulo' => $titulo, 'descripcion' => '']
            );

            $permiso->assignRole($rolAdministrador);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
