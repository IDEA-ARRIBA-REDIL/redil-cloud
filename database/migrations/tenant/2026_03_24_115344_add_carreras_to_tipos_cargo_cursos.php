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
        Schema::table('tipos_cargo_cursos', function (Blueprint $table) {
            $table->json('carreras_permitidas')->nullable()->after('limita_carreras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_cargo_cursos', function (Blueprint $table) {
            $table->dropColumn('carreras_permitidas');
        });
    }
};
