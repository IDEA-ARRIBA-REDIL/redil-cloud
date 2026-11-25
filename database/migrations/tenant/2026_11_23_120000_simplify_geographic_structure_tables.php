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
        if (Schema::hasTable('municipios')) {
            Schema::table('municipios', function (Blueprint $table): void {
                $table->string('nombre', 150)->change();
            });

            if (! Schema::hasColumn('municipios', 'pais_id')) {
                Schema::table('municipios', function (Blueprint $table): void {
                    $table->unsignedBigInteger('pais_id')->nullable();
                });
            }

            if (Schema::hasTable('departamentos') && Schema::hasColumn('municipios', 'departamento_id')) {
                $this->migrarMunicipiosConDepartamento();
            }

            DB::table('municipios')
                ->whereNull('pais_id')
                ->update(['pais_id' => 45]);
        }

        if (
            Schema::hasTable('users')
            && Schema::hasTable('barrios')
            && Schema::hasColumn('users', 'barrio_id')
            && Schema::hasColumn('users', 'localidad_id')
            && Schema::hasColumn('barrios', 'localidad_id')
        ) {
            $this->migrarLocalidadesDesdeBarrios();
        }

        $this->eliminarColumnasSiExisten('municipios', ['departamento_id']);
        $this->eliminarColumnasSiExisten('paises', ['continente_id']);
        $this->eliminarColumnasSiExisten('iglesias', ['continente_id', 'region_id', 'departamento_id']);
        $this->eliminarColumnasSiExisten('sedes', ['continente_id', 'region_id', 'departamento_id', 'barrio_id', 'barrio_auxiliar']);
        $this->eliminarColumnasSiExisten('grupos', ['barrio_id', 'barrio_auxiliar']);
        $this->eliminarColumnasSiExisten('users', ['barrio_id', 'barrio_auxiliar']);

        Schema::dropIfExists('barrios');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('regiones');
        Schema::dropIfExists('continentes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('continentes')) {
            Schema::create('continentes', function (Blueprint $table): void {
                $table->id();
                $table->string('nombre', 50);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('regiones')) {
            Schema::create('regiones', function (Blueprint $table): void {
                $table->id();
                $table->string('nombre', 50);
                $table->integer('pais_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('departamentos')) {
            Schema::create('departamentos', function (Blueprint $table): void {
                $table->id();
                $table->string('nombre', 50);
                $table->integer('region_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('barrios')) {
            Schema::create('barrios', function (Blueprint $table): void {
                $table->id();
                $table->string('nombre', 50);
                $table->integer('localidad_id')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('paises') && ! Schema::hasColumn('paises', 'continente_id')) {
            Schema::table('paises', function (Blueprint $table): void {
                $table->integer('continente_id')->nullable();
            });
        }

        if (Schema::hasTable('municipios')) {
            if (! Schema::hasColumn('municipios', 'departamento_id')) {
                Schema::table('municipios', function (Blueprint $table): void {
                    $table->integer('departamento_id')->nullable();
                });
            }

            if (Schema::hasColumn('municipios', 'pais_id')) {
                Schema::table('municipios', function (Blueprint $table): void {
                    $table->dropColumn('pais_id');
                });
            }
        }

        $this->agregarColumnasEnterasSiFaltan('iglesias', ['continente_id', 'region_id', 'departamento_id']);
        $this->agregarColumnasEnterasSiFaltan('sedes', ['continente_id', 'region_id', 'departamento_id', 'barrio_id']);
        $this->agregarColumnaTextoSiFalta('sedes', 'barrio_auxiliar', 50);
        $this->agregarColumnasEnterasSiFaltan('grupos', ['barrio_id']);
        $this->agregarColumnaTextoSiFalta('grupos', 'barrio_auxiliar', 50);
        $this->agregarColumnasEnterasSiFaltan('users', ['barrio_id']);
        $this->agregarColumnaTextoSiFalta('users', 'barrio_auxiliar', 50);
    }

    private function migrarMunicipiosConDepartamento(): void
    {
        $tieneRegiones = Schema::hasTable('regiones') && Schema::hasColumn('departamentos', 'region_id');

        if (DB::getDriverName() === 'pgsql') {
            if ($tieneRegiones) {
                DB::statement("
                    UPDATE municipios AS m
                    SET
                        pais_id = COALESCE(r.pais_id, m.pais_id, 45),
                        nombre = CASE
                            WHEN d.nombre IS NOT NULL
                                AND TRIM(d.nombre) <> ''
                                AND m.nombre NOT ILIKE ('%, ' || TRIM(d.nombre))
                            THEN LEFT(TRIM(m.nombre) || ', ' || TRIM(d.nombre), 150)
                            ELSE m.nombre
                        END
                    FROM departamentos AS d
                    LEFT JOIN regiones AS r ON r.id = d.region_id
                    WHERE m.departamento_id = d.id
                ");
            } else {
                DB::statement("
                    UPDATE municipios AS m
                    SET
                        pais_id = COALESCE(m.pais_id, 45),
                        nombre = CASE
                            WHEN d.nombre IS NOT NULL
                                AND TRIM(d.nombre) <> ''
                                AND m.nombre NOT ILIKE ('%, ' || TRIM(d.nombre))
                            THEN LEFT(TRIM(m.nombre) || ', ' || TRIM(d.nombre), 150)
                            ELSE m.nombre
                        END
                    FROM departamentos AS d
                    WHERE m.departamento_id = d.id
                ");
            }

            return;
        }

        $departamentos = DB::table('departamentos')->get()->keyBy('id');
        $regiones = $tieneRegiones ? DB::table('regiones')->get()->keyBy('id') : collect();

        DB::table('municipios')
            ->whereNotNull('departamento_id')
            ->orderBy('id')
            ->chunkById(500, function ($municipios) use ($departamentos, $regiones): void {
                foreach ($municipios as $municipio) {
                    $departamento = $departamentos->get($municipio->departamento_id);
                    if (! $departamento) {
                        continue;
                    }

                    $region = isset($departamento->region_id) ? $regiones->get($departamento->region_id) : null;
                    $paisId = $region->pais_id ?? $municipio->pais_id ?? 45;
                    $nombreMunicipio = trim((string) $municipio->nombre);
                    $nombreDepartamento = trim((string) ($departamento->nombre ?? ''));

                    if ($nombreDepartamento !== '' && ! str_ends_with(mb_strtolower($nombreMunicipio), ', '.mb_strtolower($nombreDepartamento))) {
                        $nombreMunicipio = mb_substr($nombreMunicipio.', '.$nombreDepartamento, 0, 150);
                    }

                    DB::table('municipios')
                        ->where('id', $municipio->id)
                        ->update([
                            'pais_id' => $paisId,
                            'nombre' => $nombreMunicipio,
                        ]);
                }
            });
    }

    private function migrarLocalidadesDesdeBarrios(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                UPDATE users AS u
                SET localidad_id = b.localidad_id
                FROM barrios AS b
                WHERE u.barrio_id = b.id
                    AND u.localidad_id IS NULL
                    AND b.localidad_id IS NOT NULL
            ');

            return;
        }

        $barrios = DB::table('barrios')
            ->whereNotNull('localidad_id')
            ->pluck('localidad_id', 'id');

        DB::table('users')
            ->whereNull('localidad_id')
            ->whereNotNull('barrio_id')
            ->orderBy('id')
            ->chunkById(500, function ($usuarios) use ($barrios): void {
                foreach ($usuarios as $usuario) {
                    $localidadId = $barrios->get($usuario->barrio_id);
                    if ($localidadId) {
                        DB::table('users')
                            ->where('id', $usuario->id)
                            ->update(['localidad_id' => $localidadId]);
                    }
                }
            });
    }

    /**
     * @param  array<int, string>  $columnas
     */
    private function eliminarColumnasSiExisten(string $tabla, array $columnas): void
    {
        if (! Schema::hasTable($tabla)) {
            return;
        }

        $existentes = array_values(array_filter(
            $columnas,
            fn (string $columna): bool => Schema::hasColumn($tabla, $columna)
        ));

        if ($existentes !== []) {
            Schema::table($tabla, function (Blueprint $table) use ($existentes): void {
                $table->dropColumn($existentes);
            });
        }
    }

    /**
     * @param  array<int, string>  $columnas
     */
    private function agregarColumnasEnterasSiFaltan(string $tabla, array $columnas): void
    {
        if (! Schema::hasTable($tabla)) {
            return;
        }

        $faltantes = array_values(array_filter(
            $columnas,
            fn (string $columna): bool => ! Schema::hasColumn($tabla, $columna)
        ));

        if ($faltantes !== []) {
            Schema::table($tabla, function (Blueprint $table) use ($faltantes): void {
                foreach ($faltantes as $columna) {
                    $table->integer($columna)->nullable();
                }
            });
        }
    }

    private function agregarColumnaTextoSiFalta(string $tabla, string $columna, int $longitud): void
    {
        if (Schema::hasTable($tabla) && ! Schema::hasColumn($tabla, $columna)) {
            Schema::table($tabla, function (Blueprint $table) use ($columna, $longitud): void {
                $table->string($columna, $longitud)->nullable();
            });
        }
    }
};
