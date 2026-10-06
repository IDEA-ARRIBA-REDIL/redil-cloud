<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductoTiendaSchemaMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('productos_tienda', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });
    }

    public function test_it_repairs_all_missing_columns_without_changing_existing_products(): void
    {
        DB::table('productos_tienda')->insert(['nombre' => 'Producto existente']);

        $this->migration()->up();
        $this->migration()->up();

        $this->assertTrue(Schema::hasColumn('productos_tienda', 'enlace_digital'));
        $this->assertTrue(Schema::hasColumn('productos_tienda', 'visible_todos'));
        $this->assertTrue(Schema::hasColumn('productos_tienda', 'genero'));
        $this->assertSame(1, DB::table('productos_tienda')->count());
        $this->assertSame(1, DB::table('productos_tienda')->value('visible_todos'));
        $this->assertSame(3, DB::table('productos_tienda')->value('genero'));
        $this->assertNull(DB::table('productos_tienda')->value('enlace_digital'));
    }

    public function test_it_preserves_columns_already_present_on_a_fresh_installation(): void
    {
        Schema::table('productos_tienda', function (Blueprint $table): void {
            $table->text('enlace_digital')->nullable();
            $table->boolean('visible_todos')->default(true);
            $table->integer('genero')->default(3);
        });

        DB::table('productos_tienda')->insert([
            'nombre' => 'Producto segmentado',
            'enlace_digital' => 'https://example.test/producto',
            'visible_todos' => false,
            'genero' => 1,
        ]);

        $this->migration()->up();

        $this->assertSame(0, DB::table('productos_tienda')->value('visible_todos'));
        $this->assertSame(1, DB::table('productos_tienda')->value('genero'));
        $this->assertSame('https://example.test/producto', DB::table('productos_tienda')->value('enlace_digital'));
    }

    public function test_it_adds_only_missing_columns_on_a_partially_updated_installation(): void
    {
        Schema::table('productos_tienda', function (Blueprint $table): void {
            $table->boolean('visible_todos')->default(true);
        });

        DB::table('productos_tienda')->insert(['nombre' => 'Producto restringido', 'visible_todos' => false]);

        $this->migration()->up();

        $this->assertSame(0, DB::table('productos_tienda')->value('visible_todos'));
        $this->assertSame(3, DB::table('productos_tienda')->value('genero'));
        $this->assertTrue(Schema::hasColumn('productos_tienda', 'enlace_digital'));
    }

    protected function migration(): Migration
    {
        return require database_path('migrations/tenant/2026_10_06_165813_add_missing_columns_to_productos_tienda_table.php');
    }
}
