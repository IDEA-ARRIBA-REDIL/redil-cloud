<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AsignarPermisosSuperAdminCommand extends Command
{
    /**
     * Nombre y firma del comando en la consola.
     */
    protected $signature = 'permisos:superadmin {tenant=crecer}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Asigna todos los permisos existentes al rol Super Administrador / Super Administrador Prueba en el tenant especificado.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $tenantId = $this->argument('tenant');
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("No se encontró el inquilino (tenant) '{$tenantId}'.");

            return Command::FAILURE;
        }

        $this->info("Conectando al tenant: {$tenant->church_name} ({$tenant->id})...");

        $resultado = $tenant->run(function () {
            // 1. Limpiar la caché de permisos previa
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // 2. Buscar los roles de super administrador
            $roles = Role::whereIn('name', ['Super Administrador Prueba', 'Super Administrador'])->get();

            if ($roles->isEmpty()) {
                return [
                    'exito' => false,
                    'mensaje' => 'No se encontraron los roles Super Administrador o Super Administrador Prueba.',
                ];
            }

            // 3. Obtener todos los permisos registrados en el tenant
            $todosLosPermisos = Permission::all();
            $totalPermisos = $todosLosPermisos->count();

            // 4. Sincronizar todos los permisos a cada rol
            $rolesActualizados = [];
            foreach ($roles as $rol) {
                $rol->syncPermissions($todosLosPermisos);
                $rolesActualizados[] = $rol->name;
            }

            // 5. Olvidar la caché para que el cambio tome efecto inmediatamente
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return [
                'exito' => true,
                'total_permisos' => $totalPermisos,
                'roles' => $rolesActualizados,
            ];
        });

        if (! $resultado['exito']) {
            $this->warn($resultado['mensaje']);

            return Command::FAILURE;
        }

        $this->info("¡Éxito! Se sincronizaron {$resultado['total_permisos']} permisos a los siguientes roles:");
        foreach ($resultado['roles'] as $nombreRol) {
            $this->line(" - <fg=green>{$nombreRol}</>");
        }

        return Command::SUCCESS;
    }
}
