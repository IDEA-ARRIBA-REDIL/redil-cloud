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
        Schema::table('materias', function (Blueprint $row) {
            $row->foreignId('tipo_usuario_inicial_id')->nullable()->after('tipo_usuario_objetivo_id')->constrained('tipo_usuarios')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $row) {
            $row->dropForeign(['tipo_usuario_inicial_id']);
            $row->dropColumn('tipo_usuario_inicial_id');
        });
    }
};
