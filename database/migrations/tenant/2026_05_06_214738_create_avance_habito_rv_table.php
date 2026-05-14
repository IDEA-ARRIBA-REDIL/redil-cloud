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
        Schema::create('avance_habito_rv', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habito_usuario_rv_id');
            $table->tinyInteger('puntaje')->default(0)->comment('Puntaje del hábito en el período, rango 0-10');
            $table->date('periodo_inicio')->comment('Fecha de inicio del período registrado');
            $table->timestamps();

            $table->foreign('habito_usuario_rv_id')
                ->references('id')
                ->on('habitos_usuario_rv')
                ->onDelete('cascade');

            // Un hábito solo puede tener un registro por período
            $table->unique(['habito_usuario_rv_id', 'periodo_inicio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avance_habito_rv');
    }
};
