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
        $rolesAdministradores = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Super Administrador', 'Super Administrador Prueba'])
            ->get();

        if ($rolesAdministradores->isEmpty()) {
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

            foreach ($rolesAdministradores as $rolAdministrador) {
                $permiso->assignRole($rolAdministrador);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
