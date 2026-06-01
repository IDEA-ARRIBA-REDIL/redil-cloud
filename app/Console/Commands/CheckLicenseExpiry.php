<?php

namespace App\Console\Commands;

use App\Mail\LicenciaEnGraciaMail;
use App\Mail\LicenciaExpiradaMail;
use App\Mail\LicenciaVence30Mail;
use App\Mail\LicenciaVence7Mail;
use App\Models\AdminNotification;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLicenseExpiry extends Command
{
    protected $signature = 'licenses:check-expiry';

    protected $description = 'Verifica el vencimiento de licencias de inquilinos y envía alertas o realiza suspensiones';

    public function handle(): int
    {
        $this->info('Iniciando verificación de vencimiento de licencias...');

        // Obtenemos todos los inquilinos activos que tienen configurado un vencimiento de licencia
        $tenants = Tenant::where('status', 'active')
            ->whereNotNull('license_ends_at')
            ->get();

        $today = Carbon::today();

        foreach ($tenants as $tenant) {
            $endsAt = Carbon::parse($tenant->license_ends_at)->startOfDay();
            $graceEndsAt = Carbon::parse($tenant->grace_ends_at)->startOfDay();

            // CASO 1: Vence en exactamente 30 días
            if ($endsAt->isSameDay($today->copy()->addDays(30)) && ! $tenant->notified_30_days) {
                Mail::to($tenant->admin_email)->queue(new LicenciaVence30Mail($tenant));

                AdminNotification::create([
                    'tenant_id' => $tenant->id,
                    'tipo' => 'vencimiento_30d',
                    'mensaje' => "La licencia de la iglesia {$tenant->church_name} vence en 30 días (el {$endsAt->format('d/m/Y')}).",
                ]);

                $tenant->update(['notified_30_days' => true]);
                $this->info("Notificación de 30 días enviada a: {$tenant->church_name}");
            }
            // CASO 2: Vence en exactamente 7 días
            elseif ($endsAt->isSameDay($today->copy()->addDays(7)) && ! $tenant->notified_7_days) {
                Mail::to($tenant->admin_email)->queue(new LicenciaVence7Mail($tenant));

                AdminNotification::create([
                    'tenant_id' => $tenant->id,
                    'tipo' => 'vencimiento_7d',
                    'mensaje' => "La licencia de la iglesia {$tenant->church_name} vence en 7 días (el {$endsAt->format('d/m/Y')}).",
                ]);

                $tenant->update(['notified_7_days' => true]);
                $this->info("Notificación de 7 días enviada a: {$tenant->church_name}");
            }
            // CASO 3: Ya venció pero está dentro del período de gracia
            elseif ($today->isAfter($endsAt) && $today->isBefore($graceEndsAt->copy()->addDay()) && ! $tenant->notified_grace) {
                Mail::to($tenant->admin_email)->queue(new LicenciaEnGraciaMail($tenant));

                AdminNotification::create([
                    'tenant_id' => $tenant->id,
                    'tipo' => 'gracia',
                    'mensaje' => "La licencia de la iglesia {$tenant->church_name} ha vencido. Período de gracia activo hasta el {$graceEndsAt->format('d/m/Y')}.",
                ]);

                $tenant->update(['notified_grace' => true]);
                $this->info("Notificación de período de gracia enviada a: {$tenant->church_name}");
            }
            // CASO 4: Superó el período de gracia (Expira y se suspende)
            elseif ($today->isAfter($graceEndsAt)) {
                $tenant->update([
                    'status' => 'expired',
                    'is_suspended' => true,
                    'suspension_reason' => 'vencimiento',
                ]);

                Mail::to($tenant->admin_email)->queue(new LicenciaExpiradaMail($tenant));

                AdminNotification::create([
                    'tenant_id' => $tenant->id,
                    'tipo' => 'expirado',
                    'mensaje' => "La licencia de la iglesia {$tenant->church_name} ha expirado completamente. El servicio ha sido suspendido.",
                ]);

                $this->warn("Licencia de: {$tenant->church_name} expirada y suspendida.");
            }
        }

        $this->info('¡Verificación de licencias completada!');

        return 0;
    }
}
