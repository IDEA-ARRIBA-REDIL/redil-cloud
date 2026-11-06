<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integrantes_grupo', function (Blueprint $table): void {
            $table->index(['grupo_id', 'user_id'], 'integrantes_grupo_ministerio_grupo_user_idx');
            $table->index(['user_id', 'grupo_id'], 'integrantes_grupo_ministerio_user_grupo_idx');
        });
        Schema::table('encargados_grupo', function (Blueprint $table): void {
            $table->index(['grupo_id', 'user_id'], 'encargados_grupo_ministerio_grupo_user_idx');
            $table->index(['user_id', 'grupo_id'], 'encargados_grupo_ministerio_user_grupo_idx');
        });
        Schema::table('grupos_excluidos', function (Blueprint $table): void {
            $table->index(['user_id', 'grupo_id'], 'grupos_excluidos_ministerio_user_grupo_idx');
            $table->index(['grupo_id', 'user_id'], 'grupos_excluidos_ministerio_grupo_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('grupos_excluidos', function (Blueprint $table): void {
            $table->dropIndex('grupos_excluidos_ministerio_user_grupo_idx');
            $table->dropIndex('grupos_excluidos_ministerio_grupo_user_idx');
        });
        Schema::table('encargados_grupo', function (Blueprint $table): void {
            $table->dropIndex('encargados_grupo_ministerio_grupo_user_idx');
            $table->dropIndex('encargados_grupo_ministerio_user_grupo_idx');
        });
        Schema::table('integrantes_grupo', function (Blueprint $table): void {
            $table->dropIndex('integrantes_grupo_ministerio_grupo_user_idx');
            $table->dropIndex('integrantes_grupo_ministerio_user_grupo_idx');
        });
    }
};
