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
        Schema::table('configuracion_rv', function (Blueprint $table) {
            $table->integer('max_metas')->default(5)->after('nombre_habitos');
            $table->integer('max_habitos_por_meta')->default(5)->after('max_metas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_rv', function (Blueprint $table) {
            $table->dropColumn(['max_metas', 'max_habitos_por_meta']);
        });
    }
};
