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
        // Agregar columnas nuevas a nivel_paso_crecimiento (tabla base creada en 2025_04_21)
        Schema::table('nivel_paso_crecimiento', function (Blueprint $table) {
            if (! Schema::hasColumn('nivel_paso_crecimiento', 'indice')) {
                $table->integer('indice')->default(0)->after('estado');
            }

            if (! Schema::hasColumn('nivel_paso_crecimiento', 'estado_paso_crecimiento_usuario_id')) {
                $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable()->after('indice');
            }
        });

        // Agregar columnas nuevas a nivel_proceso_prerrequisito (tabla base creada en 2025_04_21)
        Schema::table('nivel_proceso_prerrequisito', function (Blueprint $table) {
            if (! Schema::hasColumn('nivel_proceso_prerrequisito', 'indice')) {
                $table->integer('indice')->default(0)->after('estado_proceso');
            }

            if (! Schema::hasColumn('nivel_proceso_prerrequisito', 'estado_paso_crecimiento_usuario_id')) {
                $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable()->after('indice');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nivel_proceso_prerrequisito', function (Blueprint $table) {
            $table->dropColumn(['indice', 'estado_paso_crecimiento_usuario_id']);
        });

        Schema::table('nivel_paso_crecimiento', function (Blueprint $table) {
            $table->dropColumn(['indice', 'estado_paso_crecimiento_usuario_id']);
        });
    }
};
