<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AsignarTodosLosPermisosSuperAdminSeeder extends Seeder
{
    /**
     * Asigna todos los permisos existentes al rol Super Administrador / Super Administrador Prueba en el tenant activo.
     */
    public function run(): void
    {
        // 1. Limpiar caché previa de permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Buscar roles de super administrador
        $roles = Role::whereIn('name', ['Super Administrador Prueba', 'Super Administrador'])->get();

        if ($roles->isEmpty()) {
            $this->command?->warn('No se encontraron los roles Super Administrador o Super Administrador Prueba.');

            return;
        }

        // 3. Obtener todos los permisos del tenant
        $todosLosPermisos = Permission::all();
        $this->command?->info("Total de permisos encontrados: {$todosLosPermisos->count()}");

        // 4. Sincronizar permisos a cada rol
        foreach ($roles as $rol) {
            $rol->syncPermissions($todosLosPermisos);
            $this->command?->info("Se han asignado todos los permisos al rol: {$rol->name}");
        }

        // 5. Limpiar caché nuevamente
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
