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
        Schema::table('curso_users', function (Blueprint $table) {
            // 1. Número de intentos/reinicios realizados por el estudiante
            $table->integer('numero_reintentos')->default(0)->after('porcentaje_progreso');
            
            // 2. Fecha del último reinicio para controlar el tiempo de castigo
            $table->dateTime('ultimo_reintento_at')->nullable()->after('numero_reintentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_users', function (Blueprint $table) {
            $table->dropColumn(['numero_reintentos', 'ultimo_reintento_at']);
        });
    }
};
