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
        Schema::table('item_corte_materia_periodo', function (Blueprint $table) {
            $table->boolean('calificable')->default(true)->after('habilitar_entregable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_corte_materia_periodo', function (Blueprint $table) {
            $table->dropColumn('calificable');
        });
    }
};
