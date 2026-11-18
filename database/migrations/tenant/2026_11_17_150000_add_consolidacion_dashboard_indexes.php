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
        if (! Schema::hasIndex('users', 'users_created_at_idx')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index(['created_at'], 'users_created_at_idx');
            });
        }

        if (! Schema::hasIndex('bitacora_sedes', 'bitacora_sedes_user_created_id_idx')) {
            Schema::table('bitacora_sedes', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at', 'id'], 'bitacora_sedes_user_created_id_idx');
            });
        }

        if (! Schema::hasIndex('bitacora_sedes', 'bitacora_sedes_created_sede_idx')) {
            Schema::table('bitacora_sedes', function (Blueprint $table): void {
                $table->index(['created_at', 'sede_id_nuevo'], 'bitacora_sedes_created_sede_idx');
            });
        }

        if (! Schema::hasIndex('bitacora_tipos_usuarios', 'bitacora_tipos_usuarios_user_created_id_idx')) {
            Schema::table('bitacora_tipos_usuarios', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at', 'id'], 'bitacora_tipos_usuarios_user_created_id_idx');
            });
        }

        if (! Schema::hasIndex('reporte_bajas_altas', 'reporte_bajas_altas_user_created_id_idx')) {
            Schema::table('reporte_bajas_altas', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at', 'id'], 'reporte_bajas_altas_user_created_id_idx');
            });
        }

        if (! Schema::hasIndex('bitacora_estados_civiles', 'bitacora_estados_civiles_user_created_id_idx')) {
            Schema::table('bitacora_estados_civiles', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at', 'id'], 'bitacora_estados_civiles_user_created_id_idx');
            });
        }

        if (! Schema::hasIndex('bitacora_integrantes_grupo', 'bitacora_integrantes_grupo_user_created_id_idx')) {
            Schema::table('bitacora_integrantes_grupo', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at', 'id'], 'bitacora_integrantes_grupo_user_created_id_idx');
            });
        }

        if (! Schema::hasIndex('matriculas', 'matriculas_user_fecha_id_idx')) {
            Schema::table('matriculas', function (Blueprint $table): void {
                $table->index(['user_id', 'fecha_matricula', 'id'], 'matriculas_user_fecha_id_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('matriculas', 'matriculas_user_fecha_id_idx')) {
            Schema::table('matriculas', function (Blueprint $table): void {
                $table->dropIndex('matriculas_user_fecha_id_idx');
            });
        }

        if (Schema::hasIndex('bitacora_integrantes_grupo', 'bitacora_integrantes_grupo_user_created_id_idx')) {
            Schema::table('bitacora_integrantes_grupo', function (Blueprint $table): void {
                $table->dropIndex('bitacora_integrantes_grupo_user_created_id_idx');
            });
        }

        if (Schema::hasIndex('bitacora_estados_civiles', 'bitacora_estados_civiles_user_created_id_idx')) {
            Schema::table('bitacora_estados_civiles', function (Blueprint $table): void {
                $table->dropIndex('bitacora_estados_civiles_user_created_id_idx');
            });
        }

        if (Schema::hasIndex('reporte_bajas_altas', 'reporte_bajas_altas_user_created_id_idx')) {
            Schema::table('reporte_bajas_altas', function (Blueprint $table): void {
                $table->dropIndex('reporte_bajas_altas_user_created_id_idx');
            });
        }

        if (Schema::hasIndex('bitacora_tipos_usuarios', 'bitacora_tipos_usuarios_user_created_id_idx')) {
            Schema::table('bitacora_tipos_usuarios', function (Blueprint $table): void {
                $table->dropIndex('bitacora_tipos_usuarios_user_created_id_idx');
            });
        }

        if (Schema::hasIndex('bitacora_sedes', 'bitacora_sedes_created_sede_idx')) {
            Schema::table('bitacora_sedes', function (Blueprint $table): void {
                $table->dropIndex('bitacora_sedes_created_sede_idx');
            });
        }

        if (Schema::hasIndex('bitacora_sedes', 'bitacora_sedes_user_created_id_idx')) {
            Schema::table('bitacora_sedes', function (Blueprint $table): void {
                $table->dropIndex('bitacora_sedes_user_created_id_idx');
            });
        }

        if (Schema::hasIndex('users', 'users_created_at_idx')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_created_at_idx');
            });
        }
    }
};
