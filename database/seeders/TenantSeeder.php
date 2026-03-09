<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 'iglesia1';

        // Verificamos si ya existe para no duplicar
        if (!Tenant::find($tenantId)) {
            $this->command->info("Creando tenant: {$tenantId}");

            $tenant = Tenant::create(['id' => $tenantId]);

            // Asociamos los dominios de producción y locales
            $tenant->domains()->create(['domain' => 'iglesia1.redil.cloud']);

            // Opcional: mantener el dominio local para pruebas si compartes la DB
            $tenant->domains()->create(['domain' => 'iglesia1.redilcloud']);

            $this->command->info("Tenant {$tenantId} creado con sus dominios.");
        } else {
            $this->command->info("El tenant {$tenantId} ya existe.");
        }
    }
}
