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
            $this->command->info("Registrando tenant: {$tenantId}");

            try {
                // Intento normal de creación
                $tenant = Tenant::create(['id' => $tenantId]);
            } catch (\Exception $e) {
                // Si falla porque la BD ya existe (común tras un migrate:fresh central),
                // insertamos el registro directamente en la tabla de la DB central.
                if (str_contains(strtolower($e->getMessage()), 'already exists')) {
                    $this->command->warn("La base de datos {$tenantId} ya existe en el servidor. Vinculando registro...");

                    \Illuminate\Support\Facades\DB::table('tenants')->insert([
                        'id' => $tenantId,
                        'data' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $tenant = Tenant::find($tenantId);
                } else {
                    throw $e;
                }
            }

            // Asociamos los dominios
            // IMPORTANTE: Para InitializeBySubdomain, necesitamos la versión corta (subdominio)
            $tenant->domains()->updateOrCreate(['domain' => 'iglesia1']);

            // También guardamos las versiones largas por si acaso
            $tenant->domains()->updateOrCreate(['domain' => 'iglesia1.redil.cloud']);
            $tenant->domains()->updateOrCreate(['domain' => 'iglesia1.redilcloud']);

            $this->command->info("Tenant {$tenantId} vinculado con sus dominios.");
        } else {
            $this->command->info("El tenant {$tenantId} ya existe.");
        }
    }
}
