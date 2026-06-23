<?php

namespace App\Jobs;

use App\Mail\CuentaCreadaMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConfigurarNuevoTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 600];

    public $tenant;

    public $admin_email;

    public $admin_password;

    public function __construct(Tenant $tenant, $admin_email, $admin_password)
    {
        $this->tenant = $tenant;
        $this->admin_email = $admin_email;
        $this->admin_password = $admin_password;
    }

    public function handle(): void
    {
        // En stancl/tenancy, la ejecución dentro de 'run' sitúa la conexión DB en la del tenant.
        $this->tenant->run(function () {
            // 1. Ejecutar el seeder del tenant
            Artisan::call('db:seed', ['--class' => 'TenantDatabaseSeeder']);

            // 2. Crear el usuario administrador del tenant
            $user = User::firstOrCreate(
                ['email' => $this->admin_email],
                [
                    'name' => $this->tenant->pastor_name,
                    'password' => Hash::make($this->admin_password),
                    'email_verified_at' => now(),
                ]
            );

            // Asignar el rol más alto (generalmente Super Admin o Administrador en el seeder)
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $adminRole = \Spatie\Permission\Models\Role::first();
                if ($adminRole) {
                    $user->assignRole($adminRole);
                }
            }
        });

        Log::info('Tenant configurado exitosamente: '.$this->tenant->id);

    }

    public function failed(\Throwable $e): void
    {
        $this->tenant->update(['status' => 'setup_failed']);

        DB::table('admin_notifications')->insert([
            'tenant_id' => $this->tenant->id,
            'tipo' => 'setup_failed',
            'mensaje' => "Error crítico al configurar la base de datos de {$this->tenant->church_name}.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::critical('ConfigurarNuevoTenantJob falló para tenant: ' . $this->tenant->id, ['error' => $e->getMessage()]);
    }
}
