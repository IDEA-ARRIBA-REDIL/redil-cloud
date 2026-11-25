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
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'ciudad')) {
                Schema::table('users', function (Blueprint $table): void {
                    $table->dropColumn('ciudad');
                });
            }

            if (! Schema::hasColumn('users', 'municipio_id')) {
                Schema::table('users', function (Blueprint $table): void {
                    $table->unsignedBigInteger('municipio_id')->nullable();
                });
            }

            if (Schema::hasTable('localidades') && Schema::hasColumn('users', 'localidad_id')) {
                $this->poblarMunicipioDesdeLocalidad();
            }
        }

        if (Schema::hasTable('campos_formulario_usuario') && DB::table('campos_formulario_usuario')->exists()) {
            $existeCampo = DB::table('campos_formulario_usuario')
                ->where('nombre_bd', 'municipio_id')
                ->exists();

            if (! $existeCampo) {
                DB::table('campos_formulario_usuario')->insert([
                    'nombre' => 'Ciudad',
                    'nombre_bd' => 'municipio_id',
                    'placeholder' => 'Busca aquí la ciudad',
                    'name_id' => 'ciudad',
                    'es_campo_extra' => false,
                    'visible_resumen' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('campos_perfil_usuario') && DB::table('campos_perfil_usuario')->exists()) {
            $existePerfil = DB::table('campos_perfil_usuario')
                ->where('nombre_bd', 'municipio_id')
                ->exists();

            if (! $existePerfil) {
                DB::table('campos_perfil_usuario')->insert([
                    'nombre' => 'Ciudad',
                    'nombre_bd' => 'municipio_id',
                    'placeholder' => 'Busca aquí la ciudad',
                    'seccion' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('campos_informe_excel') && DB::table('campos_informe_excel')->exists()) {
            $existeInforme = DB::table('campos_informe_excel')
                ->where('nombre_campo_bd', 'municipio_id')
                ->where('selector_id', 1)
                ->exists();

            if (! $existeInforme) {
                $siguienteId = ((int) DB::table('campos_informe_excel')->max('id')) + 1;

                DB::table('campos_informe_excel')->insert([
                    'id' => $siguienteId,
                    'nombre_campo_bd' => 'municipio_id',
                    'nombre_campo_informe' => 'Ciudad',
                    'selector_id' => 1,
                    'tabla' => 'users.',
                    'raw_sql' => true,
                    'eloquent_sql' => false,
                    'orden' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (DB::getDriverName() === 'pgsql') {
                    DB::statement("SELECT setval('campos_informe_excel_id_seq', (SELECT COALESCE(MAX(id), 1) FROM campos_informe_excel))");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'municipio_id')) {
                Schema::table('users', function (Blueprint $table): void {
                    $table->dropColumn('municipio_id');
                });
            }

            if (! Schema::hasColumn('users', 'ciudad')) {
                Schema::table('users', function (Blueprint $table): void {
                    $table->string('ciudad')->nullable();
                });
            }
        }

        if (Schema::hasTable('campos_formulario_usuario')) {
            DB::table('campos_formulario_usuario')
                ->where('nombre_bd', 'municipio_id')
                ->delete();
        }

        if (Schema::hasTable('campos_perfil_usuario')) {
            DB::table('campos_perfil_usuario')
                ->where('nombre_bd', 'municipio_id')
                ->delete();
        }

        if (Schema::hasTable('campos_informe_excel')) {
            DB::table('campos_informe_excel')
                ->where('nombre_campo_bd', 'municipio_id')
                ->where('selector_id', 1)
                ->delete();
        }
    }

    private function poblarMunicipioDesdeLocalidad(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                UPDATE users AS u
                SET municipio_id = l.municipio_id
                FROM localidades AS l
                WHERE u.localidad_id = l.id
                    AND u.municipio_id IS NULL
                    AND l.municipio_id IS NOT NULL
            ');

            return;
        }

        $localidades = DB::table('localidades')
            ->whereNotNull('municipio_id')
            ->pluck('municipio_id', 'id');

        DB::table('users')
            ->whereNull('municipio_id')
            ->whereNotNull('localidad_id')
            ->orderBy('id')
            ->chunkById(500, function ($usuarios) use ($localidades): void {
                foreach ($usuarios as $usuario) {
                    $municipioId = $localidades->get($usuario->localidad_id);
                    if ($municipioId) {
                        DB::table('users')
                            ->where('id', $usuario->id)
                            ->update(['municipio_id' => $municipioId]);
                    }
                }
            });
    }
};
