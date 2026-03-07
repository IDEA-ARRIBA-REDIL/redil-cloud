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
        Schema::table('materia_paso_crecimiento', function (Blueprint $table) {
            $table->integer('indice')->default(0);
            $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable();
            
            // Si deseas clave foránea (recomendado):
            // $table->foreign('estado_paso_crecimiento_usuario_id')->references('id')->on('estados_pasos_crecimiento_usuario'); 
        });

        Schema::table('materia_proceso_prerrequisito', function (Blueprint $table) {
            $table->integer('indice')->default(0);
            $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable();
            
            // $table->foreign('estado_paso_crecimiento_usuario_id')->references('id')->on('estados_pasos_crecimiento_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materia_paso_crecimiento', function (Blueprint $table) {
            $table->dropColumn(['indice', 'estado_paso_crecimiento_usuario_id']);
        });

        Schema::table('materia_proceso_prerrequisito', function (Blueprint $table) {
            $table->dropColumn(['indice', 'estado_paso_crecimiento_usuario_id']);
        });
    }
};
