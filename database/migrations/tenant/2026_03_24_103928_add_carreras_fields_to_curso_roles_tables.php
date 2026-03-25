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
            $table->boolean('limita_carreras')->default(false)->after('puede_gestionar_estudiantes');
        });

        Schema::table('curso_usuario_cargo', function (Blueprint $table) {
            $table->json('carreras_permitidas')->nullable()->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_cargo_cursos', function (Blueprint $table) {
            $table->dropColumn('limita_carreras');
        });

        Schema::table('curso_usuario_cargo', function (Blueprint $table) {
            $table->dropColumn('carreras_permitidas');
        });
    }
};
