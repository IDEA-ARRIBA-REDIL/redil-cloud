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
        Schema::table('estados_pasos_crecimiento_usuario', function (Blueprint $table) {
            $table->boolean('finalizado')->default(false)->after('default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estados_pasos_crecimiento_usuario', function (Blueprint $table) {
            $table->dropColumn('finalizado');
        });
    }
};
