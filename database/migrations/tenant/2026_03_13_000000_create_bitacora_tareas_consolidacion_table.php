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
        Schema::create('bitacora_tareas_consolidacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_consolidacion_usuario_id')->constrained('tarea_consolidacion_usuario')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->onDelete('set null');
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->onDelete('set null');
            $table->foreignId('estado_tarea_consolidacion_id')->constrained('estados_tarea_consolidacion')->onDelete('cascade');
            $table->foreignId('autor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_tareas_consolidacion');
    }
};
