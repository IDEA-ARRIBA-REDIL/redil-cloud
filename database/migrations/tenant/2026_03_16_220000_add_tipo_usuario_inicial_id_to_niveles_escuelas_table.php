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
        Schema::table('niveles_escuelas', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_usuario_inicial_id')->nullable()->after('tipo_usuario_objetivo_id');

            // Add foreign key if we want strictness, but let's check if others have it
            // For now, let's keep it consistent with tipo_usuario_objetivo_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('niveles_escuelas', function (Blueprint $table) {
            $table->dropColumn('tipo_usuario_inicial_id');
        });
    }
};
