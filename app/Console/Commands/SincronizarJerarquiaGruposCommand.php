<?php

namespace App\Console\Commands;

use App\Models\Grupo;
use App\Models\GrupoDeGrupo;
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
    protected $signature = 'grupos:sincronizar-jerarquia {--forzar : Reconstruir la jerarquía completa sin evaluar cambios recientes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconstruye y aplana el árbol jerárquico de grupos en la tabla grupos_de_grupos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando sincronización de jerarquía ministerial de grupos...');
        Log::info('Comando grupos:sincronizar-jerarquia iniciado.');

        $forzar = $this->option('forzar');

        // Consultar todos los grupos activos
        $grupos = Grupo::select('id', 'tipo_grupo_id', 'grupo_padre_id', 'dado_baja')
            ->where('dado_baja', false)
            ->get();

        if ($grupos->isEmpty()) {
            $this->warn('No se encontraron grupos activos.');
            return self::SUCCESS;
        }

        $mapaPadres = [];
        $mapaTipos = [];

        foreach ($grupos as $g) {
            $mapaPadres[$g->id] = $g->grupo_padre_id;
            $mapaTipos[$g->id] = $g->tipo_grupo_id;
        }

        // Construir matriz de relaciones (padre -> [todos los hijos en cualquier profundidad])
        $filasAInsertar = [];
        $ahora = now()->toDateTimeString();

        foreach ($grupos as $grupo) {
            $hijoId = $grupo->id;
            $hijoTipoId = $mapaTipos[$hijoId] ?? null;

            $padreActualId = $mapaPadres[$hijoId] ?? null;
            $visitados = [$hijoId]; // Prevenir ciclos infinitos en referencias circulares

            while (! empty($padreActualId) && ! in_array($padreActualId, $visitados, true)) {
                $visitados[] = $padreActualId;
                $padreTipoId = $mapaTipos[$padreActualId] ?? null;

                $filasAInsertar[] = [
                    'grupo_padre' => $padreActualId,
                    'tipo_grupo_id_padre' => $padreTipoId,
                    'grupo_hijo' => $hijoId,
                    'tipo_grupo_id_hijo' => $hijoTipoId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $padreActualId = $mapaPadres[$padreActualId] ?? null;
            }
        }

        // Reemplazo atómico en BD
        DB::transaction(function () use ($filasAInsertar) {
            GrupoDeGrupo::truncate();

            // Insertar por lotes de 500 registros para alta velocidad
            foreach (array_chunk($filasAInsertar, 500) as $lote) {
                GrupoDeGrupo::insert($lote);
            }
        });

        $totalRelaciones = count($filasAInsertar);
        $this->info("¡Sincronización completada exitosamente! Se indexaron {$totalRelaciones} relaciones jerárquicas.");
        Log::info("Comando grupos:sincronizar-jerarquia finalizado con {$totalRelaciones} relaciones.");

        return self::SUCCESS;
    }
}
