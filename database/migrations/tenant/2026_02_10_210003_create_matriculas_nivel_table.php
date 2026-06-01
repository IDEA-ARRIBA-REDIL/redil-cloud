<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nota: Esta migración fue reemplazada por 2026_03_15_174500_create_matriculas_nivel_table.php
     * que usa nivel_escuela_id en lugar de nivel_agrupacion_id.
     */
    public function up(): void
    {
        // Vacío intencionalmente: la tabla es creada en 2026_03_15_174500
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vacío intencionalmente
    }
};
