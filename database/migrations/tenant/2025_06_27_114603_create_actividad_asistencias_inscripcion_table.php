<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('actividad_asistencias_inscripcion', function (Blueprint $table) {
            $table->id();
            $table->integer('actividad_id');
            $table->integer('user_id')->nullable();
            $table->integer('compra_id');
            $table->integer('inscripcion_id');
            $table->date('fecha');
            $table->timestamps();
            // Aseguramos que un usuario solo pueda registrar su asistencia una vez por actividad

        });
    }
    public function down(): void
    {
        Schema::dropIfExists('actividad_asistencias_inscripcion');
    }
};
