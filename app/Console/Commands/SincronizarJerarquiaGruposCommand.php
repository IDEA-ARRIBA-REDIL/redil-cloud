<?php

namespace App\Console\Commands;

use App\Models\Grupo;
use App\Models\GrupoDeGrupo;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizarJerarquiaGruposCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grupos:sincronizar-jerarquia
                            {--tenant= : ID del inquilino (tenant) específico}
                            {--forzar : Reconstruir la jerarquía completa sin evaluar cambios recientes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconstruye y aplana el árbol jerárquico ministerial de grupos en la tabla grupos_de_grupos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Manejo Multi-Tenant (si se llama desde la base central sin inicializar)
        if (function_exists('tenancy') && ! tenancy()->initialized) {
            $tenantId = $this->option('tenant');

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if (! $tenant) {
                    $this->error("El inquilino '{$tenantId}' no fue encontrado.");
                    return self::FAILURE;
                }

                $this->info("=== Sincronizando Inquilino: {$tenant->id} ===");
                $tenant->run(function () use ($tenant) {
                    $this->sincronizarJerarquiaTenant($tenant->id);
                });

                return self::SUCCESS;
            }

            $tenants = Tenant::all();
            $this->info("Iniciando sincronización de jerarquía en {$tenants->count()} inquilino(s)...");

            foreach ($tenants as $tenant) {
                $this->info("=== Inquilino: {$tenant->id} ===");
                $tenant->run(function () use ($tenant) {
                    $this->sincronizarJerarquiaTenant($tenant->id);
                });
            }

            $this->info('¡Sincronización en todos los inquilinos finalizada exitosamente!');
            return self::SUCCESS;
        }

        // 2. Ejecución dentro del contexto del tenant actual
        return $this->sincronizarJerarquiaTenant();
    }

    /**
     * Ejecuta la lógica de sincronización para el tenant activo.
     */
    public function sincronizarJerarquiaTenant(?string $tenantId = null): int
    {
        $prefijo = $tenantId ? "[Tenant: {$tenantId}] " : '';
        $this->info("{$prefijo}Procesando jerarquía de grupos...");
        Log::info("{$prefijo}Comando grupos:sincronizar-jerarquia iniciado.");

        // Consultar todos los grupos activos
        $grupos = Grupo::select('id', 'tipo_grupo_id', 'dado_baja')
            ->where('dado_baja', false)
            ->get();

        if ($grupos->isEmpty()) {
            $this->warn("{$prefijo}No se encontraron grupos activos.");
            return self::SUCCESS;
        }

        $mapaTipos = $grupos->pluck('tipo_grupo_id', 'id')->toArray();
        $filasAInsertar = [];
        $ahora = now()->toDateTimeString();

        foreach ($grupos as $grupo) {
            $padreId = $grupo->id;
            $padreTipoId = $grupo->tipo_grupo_id;

            // Obtener todos los IDs de subgrupos / red ministerial usando la lógica ministerial del modelo
            $subgruposIds = $grupo->gruposMinisterio('array');

            if (is_array($subgruposIds) || $subgruposIds instanceof \Illuminate\Support\Collection) {
                foreach ($subgruposIds as $hijoId) {
                    if ($hijoId != $padreId) {
                        $hijoTipoId = $mapaTipos[$hijoId] ?? null;

                        $filasAInsertar[] = [
                            'grupo_padre' => $padreId,
                            'tipo_grupo_id_padre' => $padreTipoId,
                            'grupo_hijo' => $hijoId,
                            'tipo_grupo_id_hijo' => $hijoTipoId,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    }
                }
            }
        }

        // Reemplazo atómico en BD del tenant
        DB::transaction(function () use ($filasAInsertar) {
            GrupoDeGrupo::truncate();

            // Insertar por lotes de 500 registros para alta velocidad
            foreach (array_chunk($filasAInsertar, 500) as $lote) {
                GrupoDeGrupo::insert($lote);
            }
        });

        $totalRelaciones = count($filasAInsertar);
        $this->info("{$prefijo}¡Sincronización completada! Se indexaron {$totalRelaciones} relaciones jerárquicas.");
        Log::info("{$prefijo}Comando grupos:sincronizar-jerarquia finalizado con {$totalRelaciones} relaciones.");

        return self::SUCCESS;
    }
}
