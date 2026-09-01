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
        Schema::table('materias_aprobada_usuario', function (Blueprint $table) {
            $table->integer('creditos_aprobados')->nullable()->after('nota_final')->comment('Número de créditos aprobados de la materia al momento del registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias_aprobada_usuario', function (Blueprint $table) {
            $table->dropColumn('creditos_aprobados');
        });
    }
};
