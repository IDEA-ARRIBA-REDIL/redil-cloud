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
        Schema::create('asistencia_reuniones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('user_id');	// Antes asistente_id
            $table->integer('reporte_reunion_id');
            //$table->integer('invitados')->nullable();
            //$table->boolean('reservacion')->nullable()->default(0);
            //$table->boolean('asistio')->nullable()->default(0);
            //$table->text('observacion')->nullable();
            //$table->integer('autor_creacion_reserva_id')->nullable();
            $table->integer('autor_creacion_asistencia_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_reuniones');
    }
};
