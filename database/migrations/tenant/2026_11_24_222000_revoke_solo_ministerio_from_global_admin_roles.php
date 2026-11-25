<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Completa la ubicación de grupos principales conservando todos los permisos existentes.
     */
    public function up(): void
    {
        if (Schema::hasTable('grupos') && Schema::hasTable('sedes') && DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE grupos AS g
                SET
                    latitud = COALESCE(g.latitud, s.latitud),
                    longitud = COALESCE(g.longitud, s.longitud),
                    direccion = COALESCE(g.direccion, s.direccion),
                    telefono = COALESCE(g.telefono, s.telefono)
                FROM sedes AS s
                WHERE g.sede_id = s.id
                    AND g.codigo = 'PRINCIPAL'
                    AND (g.latitud IS NULL OR g.longitud IS NULL)
                    AND s.latitud IS NOT NULL
                    AND s.longitud IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Los valores completados se conservan al revertir para no perder datos.
    }
};
