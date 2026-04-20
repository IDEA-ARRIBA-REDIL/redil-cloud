<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        Schema::table('curso_evaluaciones', function (Blueprint $table) {
            $table->boolean('mostrar_respuestas_si_aprueba')->default(false)->after('tiempo_dilatacion');
            $table->boolean('mostrar_respuestas_si_pierde')->default(false)->after('mostrar_respuestas_si_aprueba');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_evaluaciones', function (Blueprint $table) {
            $table->dropColumn(['mostrar_respuestas_si_aprueba', 'mostrar_respuestas_si_pierde']);
        });
    }
};
