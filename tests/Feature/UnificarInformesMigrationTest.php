<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UnificarInformesMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        foreach (['informes', 'informes_personalizados'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->string('nombre');
                $table->string('link');
                $table->boolean('add_id_a_la_url')->default(false);
            });
        }
        foreach (['secciones_informes', 'bloques_informes', 'informes_en_cola'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('informe_personalizado_id')->constrained('informes_personalizados')->cascadeOnDelete();
            });
        }
        DB::table('informes')->insert(['id' => 1, 'nombre' => 'Informe estándar', 'link' => 'estandar']);
        DB::table('informes_personalizados')->insert(['id' => 1, 'nombre' => 'Informe antiguo', 'link' => 'antiguo']);
        foreach (['secciones_informes', 'bloques_informes', 'informes_en_cola'] as $tabla) {
            DB::table($tabla)->insert(['informe_personalizado_id' => 1]);
        }
        $this->additiveMigration()->up();
    }

    public function test_it_preserves_legacy_records_and_maps_colliding_ids_idempotently(): void
    {
        $this->migration()->up();
        $this->migration()->up();
        $destino = DB::table('informes')->where('informe_personalizado_origen_id', 1)->first();
        $this->assertSame(2, DB::table('informes')->count());
        $this->assertNotSame(1, $destino->id);
        $this->assertSame('estandar', DB::table('informes')->where('id', 1)->value('link'));
        $this->assertSame('antiguo', DB::table('informes_personalizados')->value('link'));
        $this->assertSame('informes-personalizados.mega-informe.show', $destino->link);
        foreach (['secciones_informes', 'bloques_informes', 'informes_en_cola'] as $tabla) {
            $this->assertSame(1, DB::table($tabla)->value('informe_personalizado_id'));
            $this->assertSame($destino->id, DB::table($tabla)->value('informe_id'));
            DB::table($tabla)->insert(['informe_id' => $destino->id]);
            $this->assertSame(2, DB::table($tabla)->count());
        }
        $this->migration()->down();
        $this->assertSame(2, DB::table('informes')->count());
    }

    public function test_it_aborts_and_rolls_back_data_when_a_relationship_conflicts(): void
    {
        DB::table('bloques_informes')->update(['informe_id' => 1]);
        try {
            $this->migration()->up();
            $this->fail('Debió rechazar la referencia incompatible.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('requiere revisión', $exception->getMessage());
        }
        $this->assertSame(1, DB::table('informes')->count());
        $this->assertNull(DB::table('secciones_informes')->value('informe_id'));
        $this->assertSame(1, DB::table('bloques_informes')->value('informe_id'));
    }

    public function test_it_keeps_foreign_keys_after_making_legacy_columns_nullable(): void
    {
        $this->migration()->up();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('secciones_informes')->insert(['informe_personalizado_id' => 999]);
    }

    private function migration(): Migration
    {
        return require database_path('migrations/tenant/2026_10_08_103000_make_informe_personalizado_id_nullable_in_megainformes.php');
    }

    private function additiveMigration(): Migration
    {
        return require database_path('migrations/tenant/2026_10_07_200000_unificar_informes_y_plantillas_table.php');
    }
}
