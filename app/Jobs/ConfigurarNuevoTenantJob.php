<?php

namespace App\Jobs;

use App\Models\AdminNotification;
use App\Models\Tenant;
use Database\Seeders\NuevoTenantSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;

class ConfigurarNuevoTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $central = true;

    public int $tries = 3;

    public int $timeout = 900;

    public array $backoff = [60, 300, 600];

    public function __construct(public string $tenantId)
    {
        $this->onConnection('provisioning')->onQueue('provisioning');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('provisioning:'.$this->tenantId))->releaseAfter(60)->expireAfter(1000)];
    }

    public function handle(): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);
        if ((int) $tenant->onboarding_version !== 1 || $tenant->provisioned_at || $tenant->is_suspended || ! in_array($tenant->status, ['pending_review', 'setup_failed'], true)) {
            return;
        }
        if (! $tenant->database()->manager()->databaseExists($tenant->database()->getName())) {
            (new CreateDatabase($tenant))->handle(app(DatabaseManager::class));
        }
        $code = Artisan::call('tenants:migrate', ['--tenants' => [$tenant->id], '--force' => true, '--no-interaction' => true]);
        if ($code !== 0) {
            throw new \RuntimeException('La migración del tenant no terminó correctamente.');
        }
        $tenant->run(function (): void {
            \App\Models\User::query()->getConnection()->transaction(function (): void {
                app(NuevoTenantSeeder::class)->__invoke();
            });
        });
        $tenant->refresh();
        $tenant->update(['provisioned_at' => now()]);
        if ($tenant->status === 'setup_failed') {
            $tenant->update(['status' => 'pending_review']);
        }
        AdminNotification::create(['tenant_id' => $tenant->id, 'tipo' => 'setup_ready', 'mensaje' => 'Entorno preparado. Requiere revisión y activación manual.']);
    }

    public function failed(\Throwable $exception): void
    {
        $tenant = Tenant::find($this->tenantId);
        if ($tenant && (int) $tenant->onboarding_version === 1 && ! $tenant->provisioned_at && in_array($tenant->status, ['pending_review', 'setup_failed'], true)) {
            $tenant->update(['status' => 'setup_failed']);
            AdminNotification::create(['tenant_id' => $tenant->id, 'tipo' => 'setup_failed', 'mensaje' => 'Falló el aprovisionamiento. Revisar antes de reintentar.']);
        }
        Log::error('Falló aprovisionamiento tenant', ['tenant_id' => $this->tenantId, 'exception_type' => $exception::class]);
    }
}
