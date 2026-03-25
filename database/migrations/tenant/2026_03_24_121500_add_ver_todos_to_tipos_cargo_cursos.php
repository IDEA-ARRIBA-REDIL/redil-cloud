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
        Schema::table('tipos_cargo_cursos', function (Blueprint $blueprint) {
            $blueprint->boolean('puede_ver_todos_los_cursos')->default(false)->after('limita_carreras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_cargo_cursos', function (Blueprint $blueprint) {
            $blueprint->dropColumn('puede_ver_todos_los_cursos');
        });
    }
};
