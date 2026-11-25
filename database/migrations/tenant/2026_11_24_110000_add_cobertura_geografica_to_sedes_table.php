<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sedes')) {
            Schema::table('sedes', function (Blueprint $table): void {
                if (! Schema::hasColumn('sedes', 'aparece_en_todos_los_casos')) {
                    $table->boolean('aparece_en_todos_los_casos')->default(true);
                }
                if (! Schema::hasColumn('sedes', 'modo_paises')) {
                    $table->string('modo_paises', 20)->default('incluir');
                }
                if (! Schema::hasColumn('sedes', 'modo_municipios')) {
                    $table->string('modo_municipios', 20)->default('ninguno');
                }
                if (! Schema::hasColumn('sedes', 'modo_localidades')) {
                    $table->string('modo_localidades', 20)->default('ninguno');
                }
            });
        }

        if (! Schema::hasTable('pais_sede')) {
            Schema::create('pais_sede', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('pais_id')->constrained('paises')->onDelete('cascade');
                $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['pais_id', 'sede_id']);
            });
        }

        if (! Schema::hasTable('municipio_sede')) {
            Schema::create('municipio_sede', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('municipio_id')->constrained('municipios')->onDelete('cascade');
                $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['municipio_id', 'sede_id']);
            });
        }

        if (Schema::hasTable('localidad_sede') && Schema::hasTable('localidades') && Schema::hasTable('municipios')) {
            $sedesConLocalidades = DB::table('localidad_sede')
                ->select('sede_id')
                ->distinct()
                ->pluck('sede_id');

            foreach ($sedesConLocalidades as $sedeId) {
                $municipioIds = DB::table('localidad_sede as ls')
                    ->join('localidades as l', 'l.id', '=', 'ls.localidad_id')
                    ->where('ls.sede_id', $sedeId)
                    ->whereNotNull('l.municipio_id')
                    ->distinct()
                    ->pluck('l.municipio_id');

                $paisIds = DB::table('municipios')
                    ->whereIn('id', $municipioIds)
                    ->whereNotNull('pais_id')
                    ->distinct()
                    ->pluck('pais_id');

                foreach ($paisIds as $paisId) {
                    DB::table('pais_sede')->insertOrIgnore([
                        'pais_id' => $paisId,
                        'sede_id' => $sedeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($municipioIds as $municipioId) {
                    DB::table('municipio_sede')->insertOrIgnore([
                        'municipio_id' => $municipioId,
                        'sede_id' => $sedeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('sedes')
                    ->where('id', $sedeId)
                    ->update([
                        'aparece_en_todos_los_casos' => false,
                        'modo_paises' => 'incluir',
                        'modo_municipios' => $municipioIds->isNotEmpty() ? 'incluir' : 'ninguno',
                        'modo_localidades' => 'incluir',
                    ]);
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipio_sede');
        Schema::dropIfExists('pais_sede');

        if (Schema::hasTable('sedes')) {
            $columnas = array_values(array_filter(
                ['aparece_en_todos_los_casos', 'modo_paises', 'modo_municipios', 'modo_localidades'],
                fn (string $columna): bool => Schema::hasColumn('sedes', $columna)
            ));

            if ($columnas !== []) {
                Schema::table('sedes', function (Blueprint $table) use ($columnas): void {
                    $table->dropColumn($columnas);
                });
            }
        }
    }
};
