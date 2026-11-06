<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('informes') || ! Schema::hasTable('informes_personalizados')) {
            return;
        }

        DB::transaction(function (): void {
            if (! Schema::hasColumn('informes', 'informe_personalizado_origen_id')) {
                Schema::table('informes', function (Blueprint $table): void {
                    $table->unsignedBigInteger('informe_personalizado_origen_id')->nullable()->unique();
                });
            }
            $tablas = array_filter(['secciones_informes', 'bloques_informes', 'informes_en_cola'],
                fn (string $tabla): bool => Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'informe_personalizado_id'));
            $columnas = array_flip(Schema::getColumnListing('informes'));

            foreach (DB::table('informes_personalizados')->orderBy('id')->get() as $origen) {
                $destino = DB::table('informes')->where('informe_personalizado_origen_id', $origen->id)->value('id');
                if (! $destino) {
                    $datos = array_intersect_key((array) $origen, $columnas);
                    unset($datos['id']);
                    $datos['informe_personalizado_origen_id'] = $origen->id;
                    $datos['usa_plantilla'] = true;
                    $datos['link'] = 'informes-personalizados.mega-informe.show';
                    $datos['add_id_a_la_url'] = true;
                    $destino = DB::table('informes')->insertGetId($datos);
                }
                foreach ($tablas as $tabla) {
                    $relaciones = DB::table($tabla)->where('informe_personalizado_id', $origen->id);
                    if ((clone $relaciones)->whereNotNull('informe_id')->where('informe_id', '!=', $destino)->exists()) {
                        throw new RuntimeException("La relación de {$tabla} requiere revisión antes de unificar informes.");
                    }
                    $relaciones->whereNull('informe_id')->update(['informe_id' => $destino]);
                }
            }
            foreach ($tablas as $tabla) {
                Schema::table($tabla, function (Blueprint $table): void {
                    $table->unsignedBigInteger('informe_personalizado_id')->nullable()->change();
                });
            }
        });
    }

    public function down(): void
    {
        // La reversión conserva las referencias antiguas y nuevas para no perder informes.
    }
};
