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
        Schema::create('citas_consejeria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('consejero_id')->constrained('consejeros')->onDelete('cascade');
            $table->foreignId('tipo_consejeria_id')->nullable()->constrained('tipo_consejerias')->onDelete('set null');
            $table->smallInteger('medio'); // 1 presencial, 2 virtual
            $table->string('enlace_virtual')->nullable();
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->text('notas_paciente')->nullable(); // Notas que el paciente añade al agendar
            $table->text('notas_cancelacion')->nullable(); // Motivo de cancelación
            $table->text('conclusiones_consejero')->nullable();             
            $table->boolean('concluida')->default(false);
            $table->unsignedBigInteger('cancelado_por')->nullable(); // ID del usuario que canceló
            $table->softDeletes(); // Para borrado lógico
            $table->timestamps();
            
            // --- ÍNDICES PARA BÚSQUEDAS RÁPIDAS ---
            $table->index('user_id');
            $table->index('consejero_id');
            $table->index(['fecha_hora_inicio', 'fecha_hora_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas_consejeria');
    }
};
