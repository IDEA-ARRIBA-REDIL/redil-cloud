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
        Schema::create('asistencia_grupos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('user_id'); // antes asistente_id
            $table->integer('reporte_grupo_id');
            $table->boolean('asistio');
            $table->text('observaciones')->nullable();
            $table->smallinteger('tipo_inasistencia_id')->nullable(); // antes tipo_inasistencia
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_grupos');
    }
};
