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
        // 1. secciones_informes: eliminar informe_personalizado_id
        if (Schema::hasTable('secciones_informes') && Schema::hasColumn('secciones_informes', 'informe_personalizado_id')) {
            Schema::table('secciones_informes', function (Blueprint $table) {
                $table->dropColumn('informe_personalizado_id');
            });
        }

        // 2. bloques_informes: eliminar informe_personalizado_id
        if (Schema::hasTable('bloques_informes') && Schema::hasColumn('bloques_informes', 'informe_personalizado_id')) {
            Schema::table('bloques_informes', function (Blueprint $table) {
                $table->dropColumn('informe_personalizado_id');
            });
        }

        // 3. informes_en_cola: eliminar informe_personalizado_id
        if (Schema::hasTable('informes_en_cola') && Schema::hasColumn('informes_en_cola', 'informe_personalizado_id')) {
            Schema::table('informes_en_cola', function (Blueprint $table) {
                $table->dropColumn('informe_personalizado_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('secciones_informes') && ! Schema::hasColumn('secciones_informes', 'informe_personalizado_id')) {
            Schema::table('secciones_informes', function (Blueprint $table) {
                $table->unsignedBigInteger('informe_personalizado_id')->nullable();
            });
        }

        if (Schema::hasTable('bloques_informes') && ! Schema::hasColumn('bloques_informes', 'informe_personalizado_id')) {
            Schema::table('bloques_informes', function (Blueprint $table) {
                $table->unsignedBigInteger('informe_personalizado_id')->nullable();
            });
        }

        if (Schema::hasTable('informes_en_cola') && ! Schema::hasColumn('informes_en_cola', 'informe_personalizado_id')) {
            Schema::table('informes_en_cola', function (Blueprint $table) {
                $table->unsignedBigInteger('informe_personalizado_id')->nullable();
            });
        }
    }
};
