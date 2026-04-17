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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('entidad_relacionada_id')->nullable()->after('tipo_usuario_id');
            $table->foreign('entidad_relacionada_id')->references('id')->on('entidades_relacionadas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['entidad_relacionada_id']);
            $table->dropColumn('entidad_relacionada_id');
        });
    }
};
