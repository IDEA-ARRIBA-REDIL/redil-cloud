<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException('Esta migración requiere PostgreSQL.');
        }

        DB::statement("SET LOCAL lock_timeout = '5s'");

        foreach (['compra_id', 'pago_id', 'estado_pago_id'] as $columna) {
            if (! Schema::hasColumn('matriculas', $columna)) {
                Schema::table('matriculas', function (Blueprint $table) use ($columna): void {
                    $table->unsignedBigInteger($columna)->nullable();
                });
            }
        }

        if (! Schema::hasColumn('compras', 'anulacion_solicitada')) {
            Schema::table('compras', function (Blueprint $table): void {
                $table->boolean('anulacion_solicitada')->default(false);
            });
        }

        foreach (['compras', 'pagos'] as $tabla) {
            if (! Schema::hasColumn($tabla, 'operacion_id')) {
                Schema::table($tabla, function (Blueprint $table) use ($tabla): void {
                    $table->uuid('operacion_id')->nullable();
                    if ($tabla === 'pagos') {
                        $table->unsignedInteger('operacion_linea')->nullable();
                    }
                });
            }
        }

        $columnasMonetarias = [
            'compras' => ['valor'],
            'pagos' => ['valor', 'comision', 'valor_neto'],
            'actividad_categoria_monedas' => ['valor'],
            'actividad_carritos_compra' => ['precio'],
            'categoria_actividad_compra' => ['valor'],
            'productos' => ['precio'],
            'documento_equivalente' => ['valor'],
            'monedas' => ['donacion_maxima'],
            'respuestas_formulario_elemento_compra' => ['respuesta_moneda'],
            'cajas' => ['dinero_acumulado', 'limite_dinero_acumulado'],
        ];
        foreach ($columnasMonetarias as $tabla => $columnas) {
            foreach ($columnas as $columna) {
                if (Schema::hasColumn($tabla, $columna) && Schema::getColumnType($tabla, $columna) !== 'numeric') {
                    DB::statement('ALTER TABLE "'.$tabla.'" ALTER COLUMN "'.$columna.'" TYPE numeric USING "'.$columna.'"::numeric');
                }
            }
        }

        $indices = [
            ['reporte_grupos', ['grupo_id', 'fecha'], 'mbd_reportes_grupo_fecha'],
            ['reporte_grupos', ['sede_id', 'fecha'], 'mbd_reportes_sede_fecha'],
            ['asistencia_grupos', ['user_id', 'reporte_grupo_id'], 'mbd_asistencia_usuario_reporte'],
            ['pagos', ['compra_id', 'estado_pago_id'], 'mbd_pagos_compra_estado'],
            ['pagos', ['registro_caja_id', 'fecha'], 'mbd_pagos_caja_fecha'],
            ['pagos', ['estado_pago_id', 'tipo_pago_id'], 'mbd_pagos_estado_tipo'],
            ['compras', ['actividad_id', 'fecha'], 'mbd_compras_actividad_fecha'],
            ['compras', ['user_id', 'fecha'], 'mbd_compras_usuario_fecha'],
            ['inscripciones', ['compra_id'], 'mbd_inscripciones_compra'],
            ['ofrendas', ['user_id', 'fecha'], 'mbd_ofrendas_usuario_fecha'],
            ['ofrendas', ['tipo_ofrenda_id', 'fecha'], 'mbd_ofrendas_tipo_fecha'],
            ['ofrenda_grupos', ['ofrenda_id'], 'mbd_ofrendas_grupo_ofrenda'],
            ['ofrenda_reuniones', ['ofrenda_id'], 'mbd_ofrendas_reunion_ofrenda'],
            ['ingresos', ['ofrenda_id'], 'mbd_ingresos_ofrenda'],
            ['matriculas', ['compra_id'], 'mbd_matriculas_compra'],
            ['matriculas', ['pago_id', 'compra_id'], 'mbd_matriculas_pago_compra'],
            ['clasificacion_asistente_reporte_grupo', ['reporte_grupo_id', 'clasificacion_asistente_id'], 'mbd_clasificacion_reporte'],
        ];
        foreach ($indices as [$tabla, $columnas, $nombre]) {
            if (Schema::hasTable($tabla) && ! $this->tieneIndice($tabla, $columnas)) {
                Schema::table($tabla, fn (Blueprint $table) => $table->index($columnas, $nombre));
            }
        }

        $unicos = [
            ['estados_pago', ['id', 'tipo_pago_id'], 'mbd_estado_tipo_unique'],
            ['compras', ['operacion_id'], 'mbd_compra_operacion_unique'],
            ['pagos', ['operacion_id', 'operacion_linea'], 'mbd_pago_operacion_unique'],
            ['asistencia_grupos', ['reporte_grupo_id', 'user_id'], 'mbd_asistencia_reporte_usuario_unique'],
            ['ofrenda_grupos', ['reporte_grupo_id', 'ofrenda_id'], 'mbd_ofrendas_grupo_unique'],
            ['ofrenda_reuniones', ['reporte_reunion_id', 'ofrenda_id'], 'mbd_ofrendas_reunion_unique'],
            ['integrantes_grupo', ['grupo_id', 'user_id'], 'mbd_integrantes_unique'],
            ['encargados_grupo', ['grupo_id', 'user_id'], 'mbd_encargados_unique'],
            ['pagos', ['id', 'compra_id'], 'mbd_pago_compra_unique'],
        ];
        foreach ($unicos as [$tabla, $columnas, $nombre]) {
            if (Schema::hasTable($tabla) && ! Schema::hasIndex($tabla, $columnas, 'unique')) {
                if (DB::table($tabla)->select($columnas)->whereNotNull($columnas)->groupBy($columnas)->havingRaw('COUNT(*) > 1')->exists()) {
                    throw new RuntimeException('Existen duplicados en '.$tabla.'. Conciliar sin borrar datos antes de migrar.');
                }
                Schema::table($tabla, fn (Blueprint $table) => $table->unique($columnas, $nombre));
            }
        }

        $relaciones = [
            ['matriculas', 'compra_id', 'compras'], ['matriculas', 'pago_id', 'pagos'],
            ['matriculas', 'estado_pago_id', 'estados_pago'],
            ['pagos', 'compra_id', 'compras'], ['pagos', 'moneda_id', 'monedas'],
            ['pagos', 'tipo_pago_id', 'tipos_pago'], ['pagos', 'estado_pago_id', 'estados_pago'],
            ['pagos', 'registro_caja_id', 'cajas'],
            ['compras', 'user_id', 'users'], ['compras', 'moneda_id', 'monedas'],
            ['inscripciones', 'compra_id', 'compras'], ['inscripciones', 'user_id', 'users'],
            ['inscripciones', 'actividad_categoria_id', 'actividad_categorias'],
            ['reporte_grupos', 'grupo_id', 'grupos'],
            ['asistencia_grupos', 'reporte_grupo_id', 'reporte_grupos'], ['asistencia_grupos', 'user_id', 'users'],
            ['integrantes_grupo', 'grupo_id', 'grupos'], ['integrantes_grupo', 'user_id', 'users'],
            ['encargados_grupo', 'grupo_id', 'grupos'], ['encargados_grupo', 'user_id', 'users'],
            ['ofrendas', 'user_id', 'users'], ['ofrendas', 'moneda_id', 'monedas'],
            ['ofrendas', 'tipo_ofrenda_id', 'tipo_ofrenda'],
            ['ofrenda_grupos', 'ofrenda_id', 'ofrendas'], ['ofrenda_grupos', 'reporte_grupo_id', 'reporte_grupos'],
            ['ofrenda_reuniones', 'ofrenda_id', 'ofrendas'], ['ofrenda_reuniones', 'reporte_reunion_id', 'reporte_reuniones'],
            ['ingresos', 'ofrenda_id', 'ofrendas'],
        ];
        foreach ($relaciones as [$tabla, $columna, $padre]) {
            if (! Schema::hasTable($tabla) || ! Schema::hasTable($padre)) {
                continue;
            }
            $existente = collect(Schema::getForeignKeys($tabla))->contains(fn (array $fk): bool => $fk['columns'] === [$columna] && $fk['foreign_table'] === $padre);
            if ($existente) {
                continue;
            }
            $nombre = 'mbd_'.$tabla.'_'.$columna.'_fk';
            DB::statement('ALTER TABLE "'.$tabla.'" ADD CONSTRAINT "'.$nombre.'" FOREIGN KEY ("'.$columna.'") REFERENCES "'.$padre.'" (id) ON DELETE RESTRICT NOT VALID');
            $huerfanos = DB::table($tabla.' as origen')->whereNotNull('origen.'.$columna)
                ->whereNotExists(fn ($query) => $query->selectRaw('1')->from($padre.' as destino')->whereColumn('destino.id', 'origen.'.$columna))->exists();
            if (! $huerfanos) {
                DB::statement('ALTER TABLE "'.$tabla.'" VALIDATE CONSTRAINT "'.$nombre.'"');
            }
        }

        if (! collect(Schema::getForeignKeys('pagos'))->contains(fn (array $fk): bool => $fk['name'] === 'mbd_pago_estado_tipo_fk')) {
            DB::statement('ALTER TABLE pagos ADD CONSTRAINT mbd_pago_estado_tipo_fk FOREIGN KEY (estado_pago_id, tipo_pago_id) REFERENCES estados_pago (id, tipo_pago_id) ON DELETE RESTRICT NOT VALID');
            $incompatibles = DB::table('pagos as pago')->join('estados_pago as estado', 'estado.id', '=', 'pago.estado_pago_id')
                ->whereColumn('pago.tipo_pago_id', '!=', 'estado.tipo_pago_id')->exists();
            if (! $incompatibles) {
                DB::statement('ALTER TABLE pagos VALIDATE CONSTRAINT mbd_pago_estado_tipo_fk');
            }
        }

        if (! collect(Schema::getForeignKeys('matriculas'))->contains(fn (array $fk): bool => $fk['name'] === 'mbd_matricula_pago_compra_fk')) {
            DB::statement('ALTER TABLE matriculas ADD CONSTRAINT mbd_matricula_pago_compra_fk FOREIGN KEY (pago_id, compra_id) REFERENCES pagos (id, compra_id) ON DELETE RESTRICT');
        }

        if (! DB::table('pg_constraint')->where('conname', 'mbd_matricula_pago_requiere_compra')->whereRaw('conrelid = ?::regclass', ['matriculas'])->exists()) {
            DB::statement('ALTER TABLE matriculas ADD CONSTRAINT mbd_matricula_pago_requiere_compra CHECK (pago_id IS NULL OR compra_id IS NOT NULL)');
        }

        if (Schema::hasColumn('reporte_grupos', 'ids_grupos_ascendentes') && ! Schema::hasIndex('reporte_grupos', 'mbd_reportes_ancestros_gin')) {
            DB::statement('CREATE INDEX mbd_reportes_ancestros_gin ON reporte_grupos USING gin ((ids_grupos_ascendentes::jsonb) jsonb_path_ops)');
        }
    }

    private function tieneIndice(string $tabla, array $columnas): bool
    {
        return collect(Schema::getIndexes($tabla))->contains(fn (array $indice): bool => array_slice($indice['columns'], 0, count($columnas)) === $columnas);
    }

    public function down(): void
    {
        throw new RuntimeException('La reversión requiere conservar las relaciones conciliadas y los importes exactos. Restaurar el respaldo en un entorno aislado y preparar una reversión explícita.');
    }
};
