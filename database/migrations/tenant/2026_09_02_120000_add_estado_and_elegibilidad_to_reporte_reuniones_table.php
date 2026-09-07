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
        Schema::table('reporte_reuniones', function (Blueprint $table) {
            $table->string('estado', 25)->default('finalizado')->after('reunion_id');
            $table->foreignId('finalizado_por')->nullable()->after('estado')->constrained('users')->nullOnDelete();
            $table->timestamp('finalizado_at')->nullable()->after('finalizado_por');
            $table->unsignedInteger('poblacion_elegible_historica')->nullable()->after('finalizado_at');

            $table->index(['fecha', 'reunion_id']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reporte_reuniones', function (Blueprint $table) {
            $table->dropIndex(['fecha', 'reunion_id']);
            $table->dropIndex(['estado']);
            $table->dropForeign(['finalizado_por']);
            $table->dropColumn(['estado', 'finalizado_por', 'finalizado_at', 'poblacion_elegible_historica']);
        });
    }
};
