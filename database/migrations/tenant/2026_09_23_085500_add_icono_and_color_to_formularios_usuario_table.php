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
            $table->string('icono', 100)->nullable()->after('descripcion');
            $table->string('color', 50)->nullable()->after('icono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formularios_usuario', function (Blueprint $table) {
            $table->dropColumn(['icono', 'color']);
        });
    }
};
