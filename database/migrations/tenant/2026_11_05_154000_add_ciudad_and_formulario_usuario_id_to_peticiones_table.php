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
        Schema::table('peticiones', function (Blueprint $table) {
            $table->string('ciudad')->nullable()->after('pais_id');
            $table->unsignedBigInteger('formulario_usuario_id')->nullable()->after('asignacion_peticion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peticiones', function (Blueprint $table) {
            $table->dropColumn(['ciudad', 'formulario_usuario_id']);
        });
    }
};
