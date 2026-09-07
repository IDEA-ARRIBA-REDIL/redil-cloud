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
        Schema::table('reuniones', function (Blueprint $table) {
            $table->foreignId('tipo_servicio_reporte_reunion_id')
                ->nullable()
                ->after('sede_id')
                ->constrained('tipo_servicios_reporte_reunion')
                ->nullOnDelete();

            $table->index('tipo_servicio_reporte_reunion_id');
            $table->index('sede_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reuniones', function (Blueprint $table) {
            $table->dropIndex(['tipo_servicio_reporte_reunion_id']);
            $table->dropIndex(['sede_id']);
            $table->dropForeign(['tipo_servicio_reporte_reunion_id']);
            $table->dropColumn('tipo_servicio_reporte_reunion_id');
        });
    }
};
