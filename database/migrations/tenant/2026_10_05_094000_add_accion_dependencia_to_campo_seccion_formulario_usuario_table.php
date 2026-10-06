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
        Schema::table('campo_seccion_formulario_usuario', function (Blueprint $table) {
            $table->string('accion_dependencia', 50)->default('deshabilitar')->after('valor_condicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campo_seccion_formulario_usuario', function (Blueprint $table) {
            $table->dropColumn(['accion_dependencia']);
        });
    }
};
