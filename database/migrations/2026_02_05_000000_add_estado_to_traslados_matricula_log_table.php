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
        Schema::table('traslados_matricula_log', function (Blueprint $table) {
            // 'estado' puede ser: 'pendiente', 'aprobado', 'rechazado'
            // Default 'aprobado' para mantener consistencia con los registros históricos que fueron traslados directos.
            $table->string('estado')->default('aprobado')->after('user_id');
            $table->text('motivo_rechazo')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traslados_matricula_log', function (Blueprint $table) {
            $table->dropColumn(['estado', 'motivo_rechazo']);
        });
    }
};
