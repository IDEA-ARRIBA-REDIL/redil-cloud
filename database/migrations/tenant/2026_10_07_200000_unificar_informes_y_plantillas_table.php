<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna usa_plantilla a la tabla informes
        if (Schema::hasTable('informes') && ! Schema::hasColumn('informes', 'usa_plantilla')) {
            Schema::table('informes', function (Blueprint $table) {
                $table->boolean('usa_plantilla')->default(false)->after('link');
            });
        }

        // 2. Agregar informe_id a secciones_informes
        if (Schema::hasTable('secciones_informes') && ! Schema::hasColumn('secciones_informes', 'informe_id')) {
            Schema::table('secciones_informes', function (Blueprint $table) {
                $table->unsignedBigInteger('informe_id')->nullable()->after('id');
                $table->index('informe_id');
            });
        }

        // 3. Agregar informe_id a bloques_informes
        if (Schema::hasTable('bloques_informes') && ! Schema::hasColumn('bloques_informes', 'informe_id')) {
            Schema::table('bloques_informes', function (Blueprint $table) {
                $table->unsignedBigInteger('informe_id')->nullable()->after('id');
                $table->index('informe_id');
            });
        }

        // 4. Agregar informe_id a informes_en_cola
        if (Schema::hasTable('informes_en_cola') && ! Schema::hasColumn('informes_en_cola', 'informe_id')) {
            Schema::table('informes_en_cola', function (Blueprint $table) {
                $table->unsignedBigInteger('informe_id')->nullable()->after('id');
                $table->index('informe_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('informes') && Schema::hasColumn('informes', 'usa_plantilla')) {
            Schema::table('informes', function (Blueprint $table) {
                $table->dropColumn('usa_plantilla');
            });
        }

        if (Schema::hasTable('secciones_informes') && Schema::hasColumn('secciones_informes', 'informe_id')) {
            Schema::table('secciones_informes', function (Blueprint $table) {
                $table->dropColumn('informe_id');
            });
        }

        if (Schema::hasTable('bloques_informes') && Schema::hasColumn('bloques_informes', 'informe_id')) {
            Schema::table('bloques_informes', function (Blueprint $table) {
                $table->dropColumn('informe_id');
            });
        }

        if (Schema::hasTable('informes_en_cola') && Schema::hasColumn('informes_en_cola', 'informe_id')) {
            Schema::table('informes_en_cola', function (Blueprint $table) {
                $table->dropColumn('informe_id');
            });
        }
    }
};
