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
        Schema::table('niveles_escuelas', function (Blueprint $table) {
            $table->integer('limite_reporte_asistencias')->nullable()->after('portada');
            $table->integer('dia_limite_reporte')->nullable()->after('limite_reporte_asistencias');
            $table->integer('dias_plazo_reporte')->nullable()->after('dia_limite_reporte');
            $table->boolean('tiene_dia_limite')->default(false)->after('dias_plazo_reporte');
            $table->smallinteger('cantidad_limite_reportes_semana')->default(1)->after('tiene_dia_limite');
            $table->integer('tipo_usuario_objetivo_id')->nullable()->after('cantidad_limite_reportes_semana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('niveles_escuelas', function (Blueprint $table) {
            $table->dropColumn([
                'limite_reporte_asistencias',
                'dia_limite_reporte',
                'dias_plazo_reporte',
                'tiene_dia_limite',
                'cantidad_limite_reportes_semana',
                'tipo_usuario_objetivo_id'
            ]);
        });
    }
};
