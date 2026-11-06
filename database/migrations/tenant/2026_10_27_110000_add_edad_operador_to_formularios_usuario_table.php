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
        Schema::table('formularios_usuario', function (Blueprint $table) {
            $table->boolean('validar_edad_operador')->default(false)->after('validar_edad');
            $table->smallInteger('edad_minima_operador')->nullable()->after('validar_edad_operador');
            $table->smallInteger('edad_maxima_operador')->nullable()->after('edad_minima_operador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formularios_usuario', function (Blueprint $table) {
            $table->dropColumn([
                'validar_edad_operador',
                'edad_minima_operador',
                'edad_maxima_operador',
            ]);
        });
    }
};
