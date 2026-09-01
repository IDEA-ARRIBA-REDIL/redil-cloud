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
            $table->foreignId('sede_default_id')
                ->nullable()
                ->after('tipo_usuario_default_id')
                ->constrained('sedes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formularios_usuario', function (Blueprint $table) {
            $table->dropForeign(['sede_default_id']);
            $table->dropColumn('sede_default_id');
        });
    }
};
