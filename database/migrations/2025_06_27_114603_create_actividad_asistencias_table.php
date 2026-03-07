<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('actividad_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('compra_id')->nullable();
            $table->timestamps();
            // Aseguramos que un usuario solo pueda registrar su asistencia una vez por actividad
            $table->unique(['actividad_id', 'user_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('actividad_asistencias');
    }
};