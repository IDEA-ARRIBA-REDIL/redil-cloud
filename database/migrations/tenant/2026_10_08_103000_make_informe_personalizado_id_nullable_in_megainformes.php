<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('informes', 'informe_personalizado_origen_id')) {
            Schema::table('informes', function (Blueprint $table): void {
                $table->unsignedBigInteger('informe_personalizado_origen_id')->nullable()->unique();
            });
        }

        foreach (['secciones_informes', 'bloques_informes', 'informes_en_cola'] as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'informe_personalizado_id')) {
                Schema::table($tabla, function (Blueprint $table): void {
                    $table->unsignedBigInteger('informe_personalizado_id')->nullable()->change();
                });
            }
        }

        if (! Schema::hasTable('informes_personalizados')) {
            return;
        }

        DB::transaction(function (): void {
            $columnas = array_flip(Schema::getColumnListing('informes'));

            foreach (DB::table('informes_personalizados')->orderBy('id')->get() as $origen) {
                $destinoId = DB::table('informes')
                    ->where('informe_personalizado_origen_id', $origen->id)
                    ->value('id');

                if ($destinoId === null) {
                    $datos = array_intersect_key((array) $origen, $columnas);
                    unset($datos['id']);
                    $datos['informe_personalizado_origen_id'] = $origen->id;
                    $datos['usa_plantilla'] = true;
                    $datos['link'] = 'informes-personalizados.mega-informe.show';
                    $datos['add_id_a_la_url'] = true;
                    $destinoId = DB::table('informes')->insertGetId($datos);
                }

                foreach (['secciones_informes', 'bloques_informes', 'informes_en_cola'] as $tabla) {
                    if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'informe_personalizado_id')) {
                        $relaciones = DB::table($tabla)->where('informe_personalizado_id', $origen->id);
                        if ((clone $relaciones)->whereNotNull('informe_id')->where('informe_id', '!=', $destinoId)->exists()) {
                            throw new RuntimeException("La relación de {$tabla} requiere revisión antes de migrar.");
                        }
                        $relaciones->whereNull('informe_id')->update(['informe_id' => $destinoId]);
                    }
                }
            }

            foreach (['secciones_informes', 'bloques_informes', 'informes_en_cola'] as $tabla) {
                if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'informe_personalizado_id')
                    && DB::table($tabla)->whereNotNull('informe_personalizado_id')->whereNull('informe_id')->exists()) {
                    throw new RuntimeException("Hay relaciones sin informe de origen en {$tabla}.");
                }
            }
        });
    }

    /**
     * La transición conserva ambas referencias para permitir volver al código anterior.
     */
    public function down(): void {}
};
