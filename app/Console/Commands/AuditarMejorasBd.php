<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditarMejorasBd extends Command
{
    protected $signature = 'bd:auditar-mejoras {--tenant= : Tenant concreto; nunca recorre todos} {--json : Resumen sin datos personales}';

    protected $description = 'Audita relaciones financieras y restricciones sin modificar registros.';

    public function handle(): int
    {
        if ($this->option('tenant')) {
            $tenant = Tenant::query()->findOrFail($this->option('tenant'));

            return $tenant->run(fn (): int => $this->auditar());
        }

        return $this->auditar();
    }

    private function auditar(): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->error('La auditoría requiere PostgreSQL.');

            return self::FAILURE;
        }
        $entorno = DB::selectOne('SELECT current_database() AS base, current_schema() AS esquema');
        if ($entorno->esquema === 'public' || ! $entorno->esquema) {
            $this->error('Seleccione explícitamente un tenant con --tenant o una conexión a su esquema.');

            return self::FAILURE;
        }
        $resultado = DB::transaction(function () use ($entorno): array {
            $referencias = DB::selectOne(<<<'SQL'
                SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE m.compra_id IS NULL AND m.referencia_pago IS NOT NULL) AS sin_conciliar,
                    COUNT(*) FILTER (WHERE p.id IS NOT NULL AND c.id IS NOT NULL AND m.compra_id IS NULL) AS coincidencia_ambigua,
                    COUNT(*) FILTER (WHERE p.id IS NOT NULL AND c.id IS NULL AND m.compra_id IS NULL) AS candidato_pago,
                    COUNT(*) FILTER (WHERE p.id IS NULL AND c.id IS NOT NULL AND m.compra_id IS NULL) AS candidato_compra,
                    COUNT(*) FILTER (WHERE p.id IS NULL AND c.id IS NULL AND m.referencia_pago IS NOT NULL AND m.compra_id IS NULL) AS sin_coincidencia
                FROM matriculas m LEFT JOIN pagos p ON p.id::text = m.referencia_pago
                LEFT JOIN compras c ON c.id::text = m.referencia_pago
                SQL);
            $restricciones = DB::select(<<<'SQL'
                SELECT conrelid::regclass::text AS tabla, conname AS restriccion,
                    pg_get_constraintdef(oid) AS definicion
                FROM pg_constraint WHERE connamespace = current_schema()::regnamespace AND NOT convalidated
                ORDER BY conrelid::regclass::text, conname
                SQL);
            $huerfanos = [];
            foreach ([['pagos', 'compra_id', 'compras'], ['pagos', 'registro_caja_id', 'cajas'], ['reporte_grupos', 'grupo_id', 'grupos'], ['integrantes_grupo', 'grupo_id', 'grupos'], ['encargados_grupo', 'grupo_id', 'grupos'], ['ingresos', 'ofrenda_id', 'ofrendas']] as [$tabla, $columna, $padre]) {
                $huerfanos[$tabla.'.'.$columna] = DB::table($tabla.' as origen')->whereNotNull('origen.'.$columna)
                    ->whereNotExists(fn ($query) => $query->selectRaw('1')->from($padre.' as destino')->whereColumn('destino.id', 'origen.'.$columna))->count();
            }
            $estados = DB::table('pagos as p')->join('estados_pago as e', 'e.id', '=', 'p.estado_pago_id')
                ->whereColumn('p.tipo_pago_id', '!=', 'e.tipo_pago_id')->count();
            $duplicados = [];
            foreach (['asistencia_grupos' => ['reporte_grupo_id', 'user_id'], 'ofrenda_grupos' => ['reporte_grupo_id', 'ofrenda_id'], 'ofrenda_reuniones' => ['reporte_reunion_id', 'ofrenda_id']] as $tabla => $columnas) {
                $duplicados[$tabla] = DB::query()->fromSub(DB::table($tabla)->select($columnas)->groupBy($columnas)->havingRaw('COUNT(*) > 1'), 'duplicados')->count();
            }

            return ['entorno' => $entorno, 'matriculas' => $referencias, 'estados_tipo_incompatibles' => $estados,
                'restricciones_sin_validar' => $restricciones, 'huerfanos' => $huerfanos, 'pares_duplicados' => $duplicados,
                'apto_para_promover' => (int) $referencias->sin_conciliar === 0 && $estados === 0 && count($restricciones) === 0 && array_sum($huerfanos) === 0 && array_sum($duplicados) === 0];
        });
        $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $resultado['apto_para_promover'] ? self::SUCCESS : self::FAILURE;
    }
}
