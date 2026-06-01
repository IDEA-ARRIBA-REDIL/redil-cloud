<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Obtenemos el plan básico como default para los seeders
        $defaultPlan = \App\Models\Plan::where('slug', 'basico-350')->first() ?? \App\Models\Plan::first();

        foreach ($tenants as $tenantId) {
            // Aseguramos que siempre usamos la conexión central
            $tenant = DB::connection('central')->table('tenants')->where('id', $tenantId)->first()
                ? Tenant::on('central')->find($tenantId)
                : null;

            $tenantCreateData = [
                'id' => $tenantId,
                'church_name' => ucfirst($tenantId).' Iglesia',
                'pastor_name' => 'Pastor '.ucfirst($tenantId),
                'city' => 'Ciudad',
                'country' => 'Colombia',
                'estimated_members' => 300,
                'whatsapp' => '1234567890',
                'admin_email' => "admin@{$tenantId}.com",
                'status' => 'active',
                'is_suspended' => false,
                'plan_id' => $defaultPlan?->id,
                'license_starts_at' => now(),
                'license_ends_at' => now()->addYear(),
                'grace_ends_at' => now()->addYear()->addDays(7),
            ];

            // Para inserción cruda en DB
            $tenantRawData = $tenantCreateData;
            unset(
                $tenantRawData['church_name'],
                $tenantRawData['pastor_name'],
                $tenantRawData['city'],
                $tenantRawData['country'],
                $tenantRawData['estimated_members'],
                $tenantRawData['whatsapp'],
                $tenantRawData['admin_email']
            );
            $tenantRawData['data'] = json_encode([
                'church_name' => $tenantCreateData['church_name'],
                'pastor_name' => $tenantCreateData['pastor_name'],
                'city' => $tenantCreateData['city'],
                'country' => $tenantCreateData['country'],
                'estimated_members' => $tenantCreateData['estimated_members'],
                'whatsapp' => $tenantCreateData['whatsapp'],
                'admin_email' => $tenantCreateData['admin_email'],
            ]);
            $tenantRawData['updated_at'] = now();

            // Si no existe el tenant, intentamos registrarlo
            if (! $tenant) {
                $this->command->info("Registrando tenant: {$tenantId}");

                try {
                    // Intento normal de creación
                    $tenant = Tenant::create($tenantCreateData);
                } catch (\Exception $e) {
                    // Si falla porque la BD ya existe (común tras un migrate:fresh central),
                    // insertamos el registro directamente en la tabla de la DB central.
                    if (str_contains(strtolower($e->getMessage()), 'already exists')) {
                        $this->command->warn("La base de datos o el tenant {$tenantId} ya existe en el servidor. Vinculando registro...");

                        $tenantRawData['updated_at'] = now();

                        \Illuminate\Support\Facades\DB::table('tenants')->updateOrInsert(
                            ['id' => $tenantId],
                            $tenantRawData
                        );

                        $tenant = Tenant::on('central')->find($tenantId);
                    } else {
                        throw $e;
                    }
                }
            } else {
                $this->command->info("El tenant {$tenantId} ya existe. Actualizando datos de licencia...");
                $tenant->update([
                    'status' => 'active',
                    'is_suspended' => false,
                    'plan_id' => $defaultPlan?->id,
                    'license_starts_at' => $tenant->license_starts_at ?? now(),
                    'license_ends_at' => $tenant->license_ends_at ?? now()->addYear(),
                    'grace_ends_at' => $tenant->grace_ends_at ?? now()->addYear()->addDays(7),
                ]);
            }

            // ASEGURAMOS LOS DOMINIOS (Esto corre siempre)
            if ($tenant) {
                $this->command->info("Asegurando dominios para {$tenantId}...");

                // Creamos el dominio dinámico basado en la variable de entorno
                $domain = "{$tenantId}.{$centralDomain}";

                $tenant->domains()->updateOrCreate(['domain' => $domain]);

                $this->command->info("Dominio asociado: {$domain}");

                // Limpiamos el contexto de tenancy para la siguiente iteración
                if (tenancy()->initialized) {
                    tenancy()->end();
                }
            }
        }
    }
}
