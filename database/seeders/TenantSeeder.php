<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = ['crecer', 'mcmtulua', 'tierranueva'];
        $centralDomain = env('CENTRAL_DOMAIN', 'redilcloud');

        $this->command->info("Dominio central configurado: {$centralDomain}");

        foreach ($tenants as $tenantId) {
            $tenant = Tenant::find($tenantId);

            // Si no existe el tenant, intentamos registrarlo
            if (! $tenant) {
                $this->command->info("Registrando tenant: {$tenantId}");

                try {
                    // Intento normal de creación
                    $tenant = Tenant::create(['id' => $tenantId]);
                } catch (\Exception $e) {
                    // Si falla porque la BD ya existe (común tras un migrate:fresh central),
                    // insertamos el registro directamente en la tabla de la DB central.
                    if (str_contains(strtolower($e->getMessage()), 'already exists')) {
                        $this->command->warn("La base de datos o el tenant {$tenantId} ya existe en el servidor. Vinculando registro...");

                        \Illuminate\Support\Facades\DB::table('tenants')->updateOrInsert(
                            ['id' => $tenantId],
                            [
                                'data' => json_encode([]),
                                'updated_at' => now(),
                            ]
                        );

                        $tenant = Tenant::find($tenantId);
                    } else {
                        throw $e;
                    }
                }
            } else {
                $this->command->info("El tenant {$tenantId} ya existe.");
            }

            // ASEGURAMOS LOS DOMINIOS (Esto corre siempre)
            if ($tenant) {
                $this->command->info("Asegurando dominios para {$tenantId}...");

                // Creamos el dominio dinámico basado en la variable de entorno
                $domain = "{$tenantId}.{$centralDomain}";

                $tenant->domains()->updateOrCreate(['domain' => $domain]);

                $this->command->info("Dominio asociado: {$domain}");
            }
        }
    }
}
