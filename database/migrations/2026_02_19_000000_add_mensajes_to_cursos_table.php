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
        Schema::table('cursos', function (Blueprint $table) {
            $table->text('mensaje_bienvenida')->nullable()->after('descripcion_larga');
            $table->text('mensaje_aprobacion')->nullable()->after('mensaje_bienvenida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['mensaje_bienvenida', 'mensaje_aprobacion']);
        });
    }
};
