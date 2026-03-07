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
        Schema::create('informes_grupo', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('user_id'); // antes asistente_id
            $table->integer('grupo_id');
            $table->integer('grupo_id_inicial_de_asistente')->nullable();
            $table->string('observaciones',100)->nullable();
            $table->integer('servicio_prestado_en_grupo_inicial')->nullable();
            $table->integer('tipo_asignacion_id');
            $table->integer('user_autor_asignacion');
            $table->smallInteger('tipo_informe'); // (1) "Asignación de líder" (2) "Asignación de asistente" (3) "Desvinculacion de líder" (4) "Desvinculacion del asistente"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_grupo');
    }
};
