<?php

namespace App\Console\Commands;

use App\Mail\InformeSemanalMail;
use App\Models\Tenant;
use App\Models\UserAdminRedil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WeeklyLicenseReport extends Command
{
    protected $signature = 'licenses:weekly-report';

    protected $description = 'Genera un informe semanal consolidado de licencias y cuotas para los superadministradores';

    public function handle(): int
    {
        $this->info('Generando informe semanal consolidado...');

        $activeCount = Tenant::where('status', 'active')->count();
        $newCount = Tenant::where('created_at', '>=', now()->subDays(7))->count();
        $pendingCount = Tenant::where('status', 'pending_review')->count();

        // Inquilinos en periodo de gracia
        $inGrace = Tenant::where('status', 'active')
            ->where('license_ends_at', '<', now())
            ->where('grace_ends_at', '>=', now())
            ->get();

        // Inquilinos que vencen en los próximos 30 días (pero no están vencidos)
        $expiringSoon = Tenant::where('status', 'active')
            ->whereBetween('license_ends_at', [now(), now()->addDays(30)])
            ->get();

        // Inquilinos que superan el 90% de su cuota de miembros
        $quotaAlerts = [];
        $tenantsWithQuota = Tenant::where('status', 'active')
            ->whereNotNull('plan_id')
            ->get();

        foreach ($tenantsWithQuota as $tenant) {
            if ($tenant->plan && $tenant->plan->max_miembros) {
                $count = $tenant->miembros_count_cache;
                $max = $tenant->plan->max_miembros;
                $ratio = $count / $max;

                if ($ratio >= 0.90) {
                    $quotaAlerts[] = [
                        'tenant' => $tenant,
                        'miembros' => $count,
                        'max_miembros' => $max,
                        'ratio' => $ratio,
                    ];
                }
            }
        }

        $reportData = [
            'active_count' => $activeCount,
            'new_count' => $newCount,
            'pending_count' => $pendingCount,
            'in_grace' => $inGrace,
            'expiring_soon' => $expiringSoon,
            'quota_alerts' => $quotaAlerts,
        ];

        // Obtener superadministradores activos
        $admins = UserAdminRedil::where('is_suspended', false)->get();

        if ($admins->isEmpty()) {
            $this->warn('No se encontraron superadministradores activos para enviar el reporte.');

            return 0;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new InformeSemanalMail($reportData));
            $this->info("Reporte encolado para el administrador: {$admin->email}");
        }

        $this->info('¡Proceso de informe semanal completado!');

        return 0;
    }
}
