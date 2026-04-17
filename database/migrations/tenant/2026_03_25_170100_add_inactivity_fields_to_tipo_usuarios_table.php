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
        Schema::table('tipo_usuarios', function (Blueprint $table) {
            $table->integer('dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion')
                ->default(0)
                ->comment('Cantidad de días de inactividad permitidos antes de la baja automática.');
            $table->boolean('seguimiento_para_dar_de_baja_automaticamente')
                ->default(false)
                ->comment('Habilita o deshabilita la baja automática por inactividad.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion',
                'seguimiento_para_dar_de_baja_automaticamente'
            ]);
        });
    }
};
