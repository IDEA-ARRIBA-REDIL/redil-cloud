<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Console\Command;

class AsignarPlanTenantCommand extends Command
{
    /**
     * Nombre y firma del comando en la consola.
     */
    protected $signature = 'tenant:asignar-plan {plan=4} {tenant=crecer}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Asigna un plan específico a un tenant para habilitar logo y marca blanca.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $tenantId = $this->argument('tenant');
        $planArg = $this->argument('plan');

        // 1. Buscar el tenant
        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            $this->error("No se encontró el inquilino (tenant) '{$tenantId}'.");

            return Command::FAILURE;
        }

        // 2. Buscar el plan (por ID o por slug)
        $plan = is_numeric($planArg)
            ? Plan::find((int) $planArg)
            : Plan::where('slug', $planArg)->first();

        if (! $plan) {
            $this->error("No se encontró ningún plan con identificador '{$planArg}'.");

            return Command::FAILURE;
        }

        // 3. Actualizar el tenant en la base de datos central
        $tenant->update([
            'plan_id' => $plan->id,
            'status' => 'active',
            'is_suspended' => false,
            'provisioned_at' => $tenant->provisioned_at ?? now(),
            'approved_at' => $tenant->approved_at ?? now(),
        ]);

        // 4. Activar personalización y permisos dentro del tenant
        $tenant->run(function () {
            // Activar flags de personalización en la configuración general
            $config = \App\Models\Configuracion::first();
            if ($config) {
                $config->update([
                    'logo_personalizado' => true,
                    'marca_blanca' => true,
                ]);
            }

            // Asignar todos los permisos al rol Super Administrador Prueba
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            $roles = \Spatie\Permission\Models\Role::whereIn('name', ['Super Administrador Prueba', 'Super Administrador'])->get();
            $permisos = \Spatie\Permission\Models\Permission::all();
            foreach ($roles as $rol) {
                $rol->syncPermissions($permisos);
            }
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        });

        $this->info('¡Tenant y personalización configurados con éxito!');
        $this->line(" - <fg=yellow>Tenant:</> {$tenant->church_name} ({$tenant->id})");
        $this->line(" - <fg=yellow>Plan Asignado:</> {$plan->nombre} (ID: {$plan->id}, Slug: {$plan->slug})");
        $this->line(' - <fg=yellow>Aprovisionamiento:</> <fg=green>Completo y Aprobado</>');
        $this->line(' - <fg=yellow>Marca Blanca y Logos:</> <fg=green>Habilitados en Configuración</>');
        $this->line(' - <fg=yellow>Permisos:</> <fg=green>Todos sincronizados al Super Administrador</>');

        return Command::SUCCESS;
    }
}
