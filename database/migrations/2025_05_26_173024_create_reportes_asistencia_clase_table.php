<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_asistencia_clase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_materia_periodo_id')
                  ->constrained('horarios_materia_periodo')
                  ->onDelete('cascade');
            $table->date('fecha_clase_reportada');
            $table->text('observaciones_generales')->nullable();
            $table->foreignId('reportado_por_user_id')
                  ->nullable()
                  ->comment('Usuario que creó o modificó por última vez el reporte general')
                  ->constrained('users')
                  ->onDelete('set null');
            $table->string('estado_reporte')->default('pendiente_detalle')->comment('Ej: pendiente_detalle, completado');
            $table->timestamps();

        
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_asistencia_clase');
    }
};