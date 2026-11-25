<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AgregarPermisosActualizacion20261125Seeder extends Seeder
{
    public function run(): void
    {
        Permission::resolveConnection()->transaction(function (): void {
            $cumpleanos = Permission::query()->firstOrCreate(
                ['name' => 'dashboard.ver_cumpleanos', 'guard_name' => 'web'],
                ['titulo' => 'ver_cumpleanos', 'descripcion' => 'Permite visualizar la opción automática de próximos cumpleaños en el dashboard.']
            );
            $acciones = Permission::query()->firstOrCreate(
                ['name' => 'configuraciones.subitem_acciones_dashboard', 'guard_name' => 'web'],
                ['titulo' => 'subitem_acciones_dashboard', 'descripcion' => 'Permite configurar los enlaces rápidos de la sección ¿Qué deseas hacer hoy? en el dashboard.']
            );
            $cobertura = Permission::query()->firstOrCreate(
                ['name' => 'sedes.opcion_cobertura_geografica_sede', 'guard_name' => 'web'],
                ['titulo' => 'opcion_cobertura_geografica_sede', 'descripcion' => 'Configurar cobertura geográfica de la sede']
            );

            $administradores = Role::query()->where('guard_name', 'web')->whereIn('name', [
                'Super Administrador Prueba', 'Super Administrador', 'Súper Administrador',
                'Super Administrador REDIL', 'Administrador iglesia',
            ])->get();
            foreach ($administradores as $administrador) {
                $administrador->givePermissionTo([$cumpleanos, $acciones]);
            }

            $lideres = Role::query()->where('guard_name', 'web')
                ->where(function ($query): void {
                    $query->whereIn('name', ['Pastor Prueba', 'Lider Prueba', 'Pastor', 'Líder', 'Lider'])
                        ->orWhereHas('permissions', function ($permissions): void {
                            $permissions->where('guard_name', 'web')->whereIn('name', [
                                'personas.lista_asistentes_solo_ministerio', 'personas.lista_asistentes_todos',
                            ]);
                        });
                })->get();
            foreach ($lideres as $lider) {
                $lider->givePermissionTo($cumpleanos);
            }

            $editoresSedes = Role::query()->where('guard_name', 'web')
                ->whereHas('permissions', fn ($query) => $query->where('guard_name', 'web')->where('name', 'sedes.opcion_modificar_sede'))
                ->get();
            foreach ($editoresSedes as $editorSede) {
                $editorSede->givePermissionTo($cobertura);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
