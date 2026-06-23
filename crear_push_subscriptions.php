<?php

/**
 * Script temporal: Crear tabla push_subscriptions en todos los tenants.
 * Ejecutar con: php artisan tinker crear_push_subscriptions.php
 * O directamente: php crear_push_subscriptions.php (desde raíz del proyecto)
 *
 * ELIMINAR después de usar.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$migrationName = '2026_04_23_000000_create_push_subscriptions_table';
$tenants = Tenant::all()->pluck('id');

echo 'Total de tenants encontrados: '.$tenants->count().PHP_EOL;
echo str_repeat('-', 50).PHP_EOL;

foreach ($tenants as $tenantId) {
    try {
        tenancy()->initialize($tenantId);

        if (Schema::hasTable('push_subscriptions')) {
            echo "[{$tenantId}] Ya existia — OK".PHP_EOL;
        } else {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
                $table->string('endpoint', 500)->unique();
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->string('content_encoding')->nullable();
                $table->timestamps();
            });

            // Registrar en la tabla migrations para que tenants:migrate la reconozca
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
            if (! $exists) {
                $batch = DB::table('migrations')->max('batch') + 1;
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch,
                ]);
            }

            echo "[{$tenantId}] CREADA exitosamente".PHP_EOL;
        }

        tenancy()->end();

    } catch (Exception $e) {
        echo "[{$tenantId}] ERROR: ".$e->getMessage().PHP_EOL;
    }
}

echo str_repeat('-', 50).PHP_EOL;
echo 'Proceso terminado.'.PHP_EOL;
