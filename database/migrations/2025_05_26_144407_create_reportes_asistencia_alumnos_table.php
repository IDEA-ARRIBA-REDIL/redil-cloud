<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
     public function up(): void
    {
         Schema::create('reportes_asistencia_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_asistencia_clase_id')       
                  ->onDelete('cascade');
            // La columna se llama 'user_id' en tu migración, lo cual es estándar para FK a users
            $table->foreignId('user_id')->comment('ID del Alumno') // Mantenemos user_id
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->boolean('asistio');
            $table->foreignId('motivo_inasistencia_id')
                  ->nullable()
                  ->constrained('motivos_inasistencias_reporte_escuelas')
                  ->onDelete('set null');
            $table->text('observaciones_alumno')->nullable();
            $table->boolean('auto_asistencia')->default(false);
            $table->timestamps();

            // Un alumno solo tiene un estado de asistencia por cada reporte_asistencia_clase
            
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('reportes_asistencia_alumnos');
    }
};