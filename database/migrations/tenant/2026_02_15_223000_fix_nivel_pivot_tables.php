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
        // Fix nivel_paso_crecimiento
        Schema::table('nivel_paso_crecimiento', function (Blueprint $table) {
            // Rename nivel_id to nivel_agrupacion_id if it exists
            if (Schema::hasColumn('nivel_paso_crecimiento', 'nivel_id')) {
                $table->renameColumn('nivel_id', 'nivel_agrupacion_id');
            }

            // Add missing columns
            if (!Schema::hasColumn('nivel_paso_crecimiento', 'estado_paso_crecimiento_usuario_id')) {
                $table->integer('estado_paso_crecimiento_usuario_id')->nullable();
            }
            if (!Schema::hasColumn('nivel_paso_crecimiento', 'indice')) {
                $table->integer('indice')->default(0);
            }
        });

        // Fix nivel_proceso_prerrequisito
        Schema::table('nivel_proceso_prerrequisito', function (Blueprint $table) {
             // Rename nivel_id to nivel_agrupacion_id if it exists
             if (Schema::hasColumn('nivel_proceso_prerrequisito', 'nivel_id')) {
                $table->renameColumn('nivel_id', 'nivel_agrupacion_id');
            }

            // Add missing columns
            if (!Schema::hasColumn('nivel_proceso_prerrequisito', 'estado_paso_crecimiento_usuario_id')) {
                $table->integer('estado_paso_crecimiento_usuario_id')->nullable();
            }
            if (!Schema::hasColumn('nivel_proceso_prerrequisito', 'indice')) {
                $table->integer('indice')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nivel_paso_crecimiento', function (Blueprint $table) {
            if (Schema::hasColumn('nivel_paso_crecimiento', 'nivel_agrupacion_id')) {
                $table->renameColumn('nivel_agrupacion_id', 'nivel_id');
            }
            $table->dropColumn(['estado_paso_crecimiento_usuario_id', 'indice']);
        });

        Schema::table('nivel_proceso_prerrequisito', function (Blueprint $table) {
             if (Schema::hasColumn('nivel_proceso_prerrequisito', 'nivel_agrupacion_id')) {
                $table->renameColumn('nivel_agrupacion_id', 'nivel_id');
            }
            $table->dropColumn(['estado_paso_crecimiento_usuario_id', 'indice']);
        });
    }
};
