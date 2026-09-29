<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sincronizar la secuencia de la tabla grupos con el ID máximo existente en PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('grupos', 'id'), coalesce((SELECT MAX(id) FROM grupos), 1));");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No requiere acción de reversión ya que solo ajusta el puntero de la secuencia
    }
};
