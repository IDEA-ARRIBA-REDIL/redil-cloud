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

class ConfigurarNuevoTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

          
        });

        Log::info('Tenant configurado exitosamente: '.$this->tenant->id);

        // 3. Enviar el correo final de confirmación (Comentado temporalmente por error SMTP en local)
        /*
        $domain = $this->tenant->domains->first()->domain ?? null;
        if ($domain) {
            Mail::to($this->admin_email)->send(new CuentaCreadaMail($this->tenant, $domain));
        }
        */
    }
}
