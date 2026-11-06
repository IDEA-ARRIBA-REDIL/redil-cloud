<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RELACIONES = ['secciones_informes', 'bloques_informes', 'informes_en_cola'];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new RuntimeException('La conciliación de informes requiere PostgreSQL o SQLite con DDL transaccional.');
        }

        DB::transaction(function (): void {
            if (! Schema::hasTable('informes')) {
                throw new RuntimeException('Falta informes; revise las migraciones anteriores.');
            }
            $tablas = array_values(array_filter(self::RELACIONES, fn (string $tabla): bool => Schema::hasTable($tabla)));
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL lock_timeout = '5s'");
                $bloqueadas = array_merge(['informes'], $tablas, Schema::hasTable('informes_personalizados') ? ['informes_personalizados'] : []);
                $grammar = DB::connection()->getQueryGrammar();
                DB::statement('LOCK TABLE '.implode(', ', array_map($grammar->wrapTable(...), $bloqueadas)).' IN SHARE ROW EXCLUSIVE MODE');
            }
            $referencias = [];
            foreach ($tablas as $tabla) {
                if (! Schema::hasColumn($tabla, 'informe_id')) {
                    throw new RuntimeException("Falta {$tabla}.informe_id; revise las migraciones anteriores.");
                }
                $legacy = Schema::hasColumn($tabla, 'informe_personalizado_id');
                $columnas = $legacy ? ['id', 'informe_id', 'informe_personalizado_id'] : ['id', 'informe_id'];
                foreach (DB::table($tabla)->select($columnas)->orderBy('id')->get() as $fila) {
                    $antiguo = $fila->informe_personalizado_id ?? null;
                    if ($fila->informe_id === null && $antiguo === null) {
                        throw new RuntimeException("{$tabla} #{$fila->id}: referencia perdida; requiere revisión con un respaldo, sin inferir IDs.");
                    }
                    if ($fila->informe_id !== null && ! DB::table('informes')->where('id', $fila->informe_id)->exists()) {
                        throw new RuntimeException("{$tabla} #{$fila->id}: informe_id huérfano; requiere revisión.");
                    }
                    if ($antiguo !== null) {
                        if (! Schema::hasTable('informes_personalizados') || ! DB::table('informes_personalizados')->where('id', $antiguo)->exists()) {
                            throw new RuntimeException("{$tabla} #{$fila->id}: referencia antigua huérfana; requiere revisión.");
                        }
                        $referencias[$antiguo][] = ['tabla' => $tabla, 'id' => $fila->id, 'destino' => $fila->informe_id];
                    }
                }
            }
            if (! Schema::hasColumn('informes', 'informe_personalizado_origen_id')) {
                Schema::table('informes', function (Blueprint $table): void {
                    $table->unsignedBigInteger('informe_personalizado_origen_id')->nullable()->unique();
                });
            }
            $columnas = array_flip(Schema::getColumnListing('informes'));
            foreach ($referencias as $origenId => $relaciones) {
                $destinos = DB::table('informes')->where('informe_personalizado_origen_id', $origenId)->pluck('id');
                if ($destinos->count() > 1) {
                    throw new RuntimeException("Informe antiguo #{$origenId}: correspondencia duplicada; requiere revisión.");
                }
                $destino = $destinos->first();
                foreach ($relaciones as $relacion) {
                    if ($relacion['destino'] !== null && ($destino === null || (string) $relacion['destino'] !== (string) $destino)) {
                        throw new RuntimeException("{$relacion['tabla']} #{$relacion['id']}: correspondencia ambigua; requiere revisión, sin sobrescribir informe_id.");
                    }
                }
                if ($destino === null) {
                    if (! isset($columnas['usa_plantilla'], $columnas['link'], $columnas['add_id_a_la_url'])) {
                        throw new RuntimeException('Faltan columnas de plantilla en informes; revise las migraciones anteriores.');
                    }
                    $origen = DB::table('informes_personalizados')->where('id', $origenId)->first();
                    $datos = array_intersect_key((array) $origen, $columnas);
                    unset($datos['id']);
                    $datos['informe_personalizado_origen_id'] = $origenId;
                    $datos['usa_plantilla'] = true;
                    $datos['link'] = 'informes-personalizados.mega-informe.show';
                    $datos['add_id_a_la_url'] = true;
                    $destino = DB::table('informes')->insertGetId($datos);
                }
                foreach ($relaciones as $relacion) {
                    DB::table($relacion['tabla'])->where('id', $relacion['id'])->whereNull('informe_id')->update(['informe_id' => $destino]);
                }
            }
            foreach ($tablas as $tabla) {
                if (! Schema::hasColumn($tabla, 'informe_personalizado_id')) {
                    continue;
                }
                if (DB::getDriverName() === 'pgsql') {
                    $nombre = DB::connection()->getQueryGrammar()->wrapTable($tabla);
                    DB::statement("ALTER TABLE {$nombre} ALTER COLUMN informe_personalizado_id DROP NOT NULL");
                } else {
                    $columna = collect(Schema::getColumns($tabla))->firstWhere('name', 'informe_personalizado_id');
                    Schema::table($tabla, function (Blueprint $table) use ($columna): void {
                        $table->unsignedBigInteger('informe_personalizado_id')->nullable()->default($columna['default'] === null ? null : new \Illuminate\Database\Query\Expression($columna['default']))->change();
                    });
                }
            }
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Conciliación de informes irreversible automáticamente: conserve datos y aplique una migración correctiva revisada.');
    }
};
