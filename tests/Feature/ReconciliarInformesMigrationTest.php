<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReconciliarInformesMigrationTest extends TestCase
{
    private const ARCHIVO = 'database/migrations/tenant/2026_11_04_112220_reconciliar_referencias_de_informes_sin_eliminar_datos.php';

    private const TABLAS = ['secciones_informes', 'bloques_informes', 'informes_en_cola'];

    private ?string $esquema = null;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('INFORMES_MIGRATION_TEST_DATABASE');
        if ($database) {
            $this->assertSame('redil_integracion_test_20261104', $database);
            $this->esquema = 'informes_test_'.bin2hex(random_bytes(6));
            config(['database.connections.informes_test' => [
                'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => 5432,
                'database' => $database, 'username' => 'm', 'password' => '',
                'search_path' => $this->esquema, 'charset' => 'utf8', 'prefix' => '',
            ]]);
        } else {
            config(['database.connections.informes_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ]]);
        }
        DB::purge('informes_test');
        DB::setDefaultConnection('informes_test');
        if ($this->esquema !== null) {
            $this->assertSame($database, DB::selectOne('SELECT current_database() AS nombre')->nombre);
            DB::statement('CREATE SCHEMA '.$this->esquema);
        }
        foreach (['informes', 'informes_personalizados'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->string('nombre');
                $table->string('link');
                $table->boolean('add_id_a_la_url')->default(false);
                $table->boolean('usa_plantilla')->default(false);
                $table->text('descripcion')->nullable();
            });
        }
        foreach (self::TABLAS as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('informe_personalizado_id')->constrained('informes_personalizados')->cascadeOnDelete();
                $table->unsignedBigInteger('informe_id')->nullable()->index();
                $table->string('contenido')->default('contenido conservado');
            });
        }
        DB::table('informes')->insert(['nombre' => 'Estándar', 'link' => 'estandar', 'descripcion' => 'Cambio local']);
        DB::table('informes_personalizados')->insert(['nombre' => 'Antiguo', 'link' => 'antiguo', 'descripcion' => 'Cambio compañero']);
        foreach (self::TABLAS as $tabla) {
            DB::table($tabla)->insert(['informe_personalizado_id' => 1]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->esquema !== null) {
            DB::connection('informes_test')->statement('DROP SCHEMA '.$this->esquema.' CASCADE');
        }
        DB::purge('informes_test');
        parent::tearDown();
    }

    public function test_preserves_data_and_foreign_keys_and_is_idempotent(): void
    {
        $this->migration()->up();
        $snapshot = $this->datos();
        $this->migration()->up();
        $this->assertSame($snapshot, $this->datos());
        $this->assertSame(2, DB::table('informes')->count());
        $this->assertSame('Cambio local', DB::table('informes')->where('id', 1)->value('descripcion'));
        $destino = DB::table('informes')->where('informe_personalizado_origen_id', 1)->first();
        $this->assertSame('Cambio compañero', $destino->descripcion);
        foreach (self::TABLAS as $tabla) {
            $this->assertSame($destino->id, DB::table($tabla)->value('informe_id'));
            $this->assertSame(1, DB::table($tabla)->value('informe_personalizado_id'));
            $this->assertNotEmpty(Schema::getForeignKeys($tabla));
            DB::table($tabla)->insert(['informe_id' => $destino->id]);
        }
    }

    public function test_after_corrected_historical_migration_does_not_duplicate_or_overwrite_edits(): void
    {
        (require database_path('migrations/tenant/2026_10_08_103000_make_informe_personalizado_id_nullable_in_megainformes.php'))->up();
        DB::table('informes')->where('informe_personalizado_origen_id', 1)->update(['nombre' => 'Edición posterior']);
        $antes = $this->datos();
        $this->migration()->up();
        $this->assertSame($antes, $this->datos());
    }

    public function test_after_destructive_version_keeps_valid_new_links_without_guessing_old_ids(): void
    {
        foreach (self::TABLAS as $tabla) {
            DB::table($tabla)->update(['informe_id' => 1]);
        }
        $this->eliminarReferenciasAntiguas();
        $antes = $this->datos();
        $this->migration()->up();
        $this->assertSame(1, DB::table('informes')->count());
        foreach (self::TABLAS as $tabla) {
            $this->assertSame($antes[$tabla], $this->datos()[$tabla]);
            $this->assertFalse(Schema::hasColumn($tabla, 'informe_personalizado_id'));
        }
        $this->assertSame($antes['informes_personalizados'], $this->datos()['informes_personalizados']);
    }

    public function test_missing_deleted_references_abort_without_changes(): void
    {
        $this->eliminarReferenciasAntiguas();
        $this->assertRejectedWithoutChanges('referencia perdida');
        $this->assertFalse(Schema::hasColumn('informes', 'informe_personalizado_origen_id'));
    }

    public function test_conflicting_valid_destination_aborts_and_rolls_back_insertions(): void
    {
        DB::table('informes_personalizados')->insert(['nombre' => 'Segundo', 'link' => 'segundo']);
        DB::table('bloques_informes')->insert(['informe_personalizado_id' => 2, 'informe_id' => 1]);
        $this->assertRejectedWithoutChanges('correspondencia ambigua');
        $this->assertFalse(Schema::hasColumn('informes', 'informe_personalizado_origen_id'));
    }

    public function test_orphan_new_destination_aborts(): void
    {
        DB::table('secciones_informes')->update(['informe_id' => 999]);
        $this->assertRejectedWithoutChanges('informe_id huérfano');
    }

    public function test_rollback_refuses_to_delete_data(): void
    {
        $this->migration()->up();
        $antes = $this->datos();
        try {
            $this->migration()->down();
            $this->fail('Debe impedir una reversión destructiva.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('irreversible', $exception->getMessage());
        }
        $this->assertSame($antes, $this->datos());
    }

    public function test_migrator_runs_new_file_when_historical_migration_is_already_recorded(): void
    {
        Schema::create('migrations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
        $historica = '2026_10_08_103000_make_informe_personalizado_id_nullable_in_megainformes';
        DB::table('migrations')->insert(['migration' => $historica, 'batch' => 1]);
        $this->artisan('migrate', ['--path' => self::ARCHIVO, '--database' => 'informes_test', '--force' => true, '--no-interaction' => true])->assertExitCode(0);
        $this->assertSame(2, DB::table('migrations')->count());
        $antes = $this->datos();
        $this->artisan('migrate', ['--path' => self::ARCHIVO, '--database' => 'informes_test', '--force' => true, '--no-interaction' => true])->assertExitCode(0);
        $this->assertSame($antes, $this->datos());
        $this->assertSame(1, DB::table('migrations')->where('migration', $historica)->value('batch'));
    }

    public function test_failure_is_not_recorded_as_a_completed_migration(): void
    {
        Schema::create('migrations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
        $this->eliminarReferenciasAntiguas();
        try {
            app('migrator')->run([base_path(self::ARCHIVO)]);
            $this->fail('Debe detener una migración sin referencias comprobables.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('referencia perdida', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('migrations')->count());
    }

    public function test_nullable_change_preserves_existing_defaults_indexes_and_comments(): void
    {
        Schema::table('secciones_informes', function (Blueprint $table): void {
            $table->unsignedBigInteger('informe_personalizado_id')->default(1)->comment('Referencia histórica conservada')->change();
        });
        $antes = collect(Schema::getColumns('secciones_informes'))->firstWhere('name', 'informe_personalizado_id');
        $indices = Schema::getIndexes('secciones_informes');
        $this->migration()->up();
        $despues = collect(Schema::getColumns('secciones_informes'))->firstWhere('name', 'informe_personalizado_id');
        $this->assertTrue($despues['nullable']);
        foreach (['type', 'default', 'comment'] as $atributo) {
            $this->assertSame($antes[$atributo], $despues[$atributo]);
        }
        $this->assertSame($indices, Schema::getIndexes('secciones_informes'));
    }

    private function migration(): Migration
    {
        return require base_path(self::ARCHIVO);
    }

    private function datos(): array
    {
        $datos = [];
        foreach (array_merge(['informes', 'informes_personalizados'], self::TABLAS) as $tabla) {
            $datos[$tabla] = DB::table($tabla)->orderBy('id')->get()->toJson();
        }

        return $datos;
    }

    private function assertRejectedWithoutChanges(string $mensaje): void
    {
        $antes = $this->datos();
        try {
            $this->migration()->up();
            $this->fail('Debió detener la conciliación.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString($mensaje, $exception->getMessage());
        }
        $this->assertSame($antes, $this->datos());
    }

    private function eliminarReferenciasAntiguas(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropForeign(['informe_personalizado_id']);
            });
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('informe_personalizado_id');
            });
        }
    }
}
