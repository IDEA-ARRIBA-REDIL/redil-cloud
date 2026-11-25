<?php

namespace Tests\Feature;

use Database\Seeders\AgregarPermisosActualizacion20261125Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigracionesEc2ActualizacionTest extends TestCase
{
    public function test_las_migraciones_y_el_seeder_preservan_los_datos_y_permisos_del_clon(): void
    {
        if (getenv('REDIL_EC2_MIGRATION_CLONE') !== '1') {
            $this->markTestSkipped('Requiere el clon aislado en el socket /tmp/redil-ec2-pg-20261125, nunca la base habitual.');
        }
        config([
            'database.default' => 'ec2_clone_testing',
            'database.connections.ec2_clone_testing' => [
                'driver' => 'pgsql', 'host' => '/tmp/redil-ec2-pg-20261125', 'port' => 55425,
                'database' => 'redil_ec2_release_test', 'username' => 'redil_test', 'password' => '',
                'charset' => 'utf8', 'prefix' => '', 'search_path' => 'crecer_2025', 'sslmode' => 'disable',
            ],
            'cache.default' => 'array', 'permission.cache.store' => 'array',
        ]);
        DB::purge('ec2_clone_testing');
        DB::setDefaultConnection('ec2_clone_testing');
        $this->assertSame('redil_ec2_release_test', DB::selectOne('SELECT current_database() AS db')->db);
        $this->assertSame('crecer_2025', DB::selectOne('SELECT current_schema() AS schema')->schema);
        DB::beginTransaction();
        try {
            $tablasProtegidas = ['roles', 'permissions', 'role_has_permissions', 'model_has_roles', 'model_has_permissions'];
            $antes = [];
            foreach ($tablasProtegidas as $tabla) {
                $antes[$tabla] = $this->filas($tabla);
            }
            $huellaUsuarios = $this->huellaUsuarios();
            $conteos = [];
            foreach (['users', 'grupos', 'sedes'] as $tabla) {
                $conteos[$tabla] = DB::table($tabla)->count();
            }
            foreach ([
                '2026_11_18_111500_add_autor_id_to_reporte_bajas_altas_table.php',
                '2026_11_19_090000_create_formulario_usuario_sede_excluida_table.php',
                '2026_11_20_160000_add_desea_pertenecer_iglesia_to_peticiones_table.php',
                '2026_11_23_120000_simplify_geographic_structure_tables.php',
                '2026_11_23_154500_add_municipio_id_to_users_and_campos_formulario_table.php',
                '2026_11_24_110000_add_cobertura_geografica_to_sedes_table.php',
                '2026_11_24_222000_revoke_solo_ministerio_from_global_admin_roles.php',
            ] as $archivo) {
                (require database_path('migrations/tenant/'.$archivo))->up();
            }
            foreach ($tablasProtegidas as $tabla) {
                $this->assertSame(hash('sha256', json_encode($antes[$tabla])), hash('sha256', json_encode($this->filas($tabla))), $tabla);
            }
            $this->assertSame($huellaUsuarios, $this->huellaUsuarios());
            foreach ($conteos as $tabla => $conteo) {
                $this->assertSame($conteo, DB::table($tabla)->count(), $tabla);
            }
            $this->assertTrue(Schema::hasColumn('users', 'municipio_id'));
            $this->assertTrue(Schema::hasColumn('sedes', 'modo_municipios'));
            $this->assertFalse(Schema::hasTable('barrios'));

            app(AgregarPermisosActualizacion20261125Seeder::class)->run();
            foreach ($tablasProtegidas as $tabla) {
                $this->assertSame([], array_values(array_diff($antes[$tabla], $this->filas($tabla))), 'No deben desaparecer ni cambiar filas de '.$tabla);
            }
            $primeraEjecucion = [];
            foreach ($tablasProtegidas as $tabla) {
                $primeraEjecucion[$tabla] = hash('sha256', json_encode($this->filas($tabla)));
            }
            app(AgregarPermisosActualizacion20261125Seeder::class)->run();
            foreach ($primeraEjecucion as $tabla => $huella) {
                $this->assertSame($huella, hash('sha256', json_encode($this->filas($tabla))), $tabla);
            }
        } finally {
            DB::rollBack();
        }
    }

    private function filas(string $tabla): array
    {
        return DB::table($tabla)->get()->map(function ($fila): string {
            $datos = (array) $fila;
            ksort($datos);

            return json_encode($datos, JSON_THROW_ON_ERROR);
        })->sort()->values()->all();
    }

    private function huellaUsuarios(): string
    {
        return DB::selectOne("SELECT md5(string_agg((to_jsonb(u) - ARRAY['ciudad', 'barrio_id', 'barrio_auxiliar', 'localidad_id', 'municipio_id'])::text, '' ORDER BY id)) AS huella FROM users AS u")->huella;
    }
}
