<?php

namespace App\Console\Commands;

use App\Mail\CuotaAlertaMail;
use App\Models\AdminNotification;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SyncTenantMembers extends Command
{
    protected $signature = 'quotas:sync-members';

    protected $description = 'Sincroniza y cachea el conteo de miembros activos de cada inquilino y evalúa los límites del plan';

    public function handle(): int
    {
        $this->info('Iniciando sincronización nocturna de cuotas de miembros...');

        // Procesamos inquilinos activos de 20 en 20 para evitar sobrecarga de memoria
        Tenant::where('status', 'active')->chunk(20, function ($tenants) {
            foreach ($tenants as $tenant) {
                try {
                    $this->info("Procesando inquilino: {$tenant->church_name} ({$tenant->id})...");

                    // Ejecutamos la consulta en la base de datos específica del inquilino
                    $count = $tenant->run(function () {
                        // Contamos los usuarios que no han sido borrados de forma lógica
                        return \App\Models\User::whereNull('deleted_at')->count();
                    });

                    // Guardamos el conteo en el cache del inquilino en la DB central
                    $tenant->update(['miembros_count_cache' => $count]);
                    $this->info("-> Miembros activos detectados: {$count}");

                    // Validamos límites del plan
                    if ($tenant->plan && $tenant->plan->max_miembros) {
                        $max = $tenant->plan->max_miembros;
                        $ratio = $count / $max;

                        if ($ratio >= 1.0) {
                            // Alerta de superación del 100% de cuota
                            AdminNotification::create([
                                'tenant_id' => $tenant->id,
                                'tipo' => 'cuota_100',
                                'mensaje' => "La iglesia {$tenant->church_name} ha excedido el límite de miembros de su plan ({$count} de {$max} miembros | ".number_format($ratio * 100, 1).'%).',
                            ]);

                            Mail::to($tenant->admin_email)->queue(new CuotaAlertaMail($tenant, $count, $max, $ratio));
                            $this->warn("-> ALERTA 100% de cuota encolada para: {$tenant->church_name}");
                        } elseif ($ratio >= 0.90) {
                            // Alerta preventiva del 90% de cuota
                            AdminNotification::create([
                                'tenant_id' => $tenant->id,
                                'tipo' => 'cuota_90',
                                'mensaje' => "La iglesia {$tenant->church_name} está cerca de superar el límite de miembros de su plan ({$count} de {$max} miembros | ".number_format($ratio * 100, 1).'%).',
                            ]);

                            Mail::to($tenant->admin_email)->queue(new CuotaAlertaMail($tenant, $count, $max, $ratio));
                            $this->info("-> Alerta preventiva de 90% de cuota encolada para: {$tenant->church_name}");
                        }
                    }
                } catch (\Throwable $e) {
                    $errorMessage = "Error al sincronizar cuota para el inquilino {$tenant->id}: ".$e->getMessage();
                    $this->error($errorMessage);
                    Log::error($errorMessage, [
                        'tenant_id' => $tenant->id,
                        'exception' => $e,
                    ]);
                    // Continuamos con el siguiente inquilino
                }
            }
        });

        $this->info('¡Sincronización de cuotas finalizada!');

        return 0;
    }
}
