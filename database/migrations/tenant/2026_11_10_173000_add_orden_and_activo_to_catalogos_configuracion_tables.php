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
        // 1. Agregar orden y activo a tipo_vinculaciones
        Schema::table('tipo_vinculaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('tipo_vinculaciones', 'orden')) {
                $table->integer('orden')->default(0)->after('por_grupo');
            }
            if (! Schema::hasColumn('tipo_vinculaciones', 'activo')) {
                $table->boolean('activo')->default(true)->after('orden');
            }
        });

        // Inicializar el orden consecutivo para registros existentes en tipo_vinculaciones
        DB::statement('UPDATE tipo_vinculaciones SET orden = id WHERE orden = 0 OR orden IS NULL');

        // 2. Agregar orden y activo a tipo_identificaciones
        Schema::table('tipo_identificaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('tipo_identificaciones', 'orden')) {
                $table->integer('orden')->default(0)->after('formulario_donacion');
            }
            if (! Schema::hasColumn('tipo_identificaciones', 'activo')) {
                $table->boolean('activo')->default(true)->after('orden');
            }
        });

        // Inicializar el orden consecutivo para registros existentes en tipo_identificaciones
        DB::statement('UPDATE tipo_identificaciones SET orden = id WHERE orden = 0 OR orden IS NULL');

        // 3. Agregar orden y activo a estados_civiles
        Schema::table('estados_civiles', function (Blueprint $table) {
            if (! Schema::hasColumn('estados_civiles', 'orden')) {
                $table->integer('orden')->default(0)->after('es_union_libre');
            }
            if (! Schema::hasColumn('estados_civiles', 'activo')) {
                $table->boolean('activo')->default(true)->after('orden');
            }
        });

        // Inicializar el orden consecutivo para registros existentes en estados_civiles
        DB::statement('UPDATE estados_civiles SET orden = id WHERE orden = 0 OR orden IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_vinculaciones', function (Blueprint $table) {
            $columns = array_filter(['orden', 'activo'], fn (string $col) => Schema::hasColumn('tipo_vinculaciones', $col));
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('tipo_identificaciones', function (Blueprint $table) {
            $columns = array_filter(['orden', 'activo'], fn (string $col) => Schema::hasColumn('tipo_identificaciones', $col));
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('estados_civiles', function (Blueprint $table) {
            $columns = array_filter(['orden', 'activo'], fn (string $col) => Schema::hasColumn('estados_civiles', $col));
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
