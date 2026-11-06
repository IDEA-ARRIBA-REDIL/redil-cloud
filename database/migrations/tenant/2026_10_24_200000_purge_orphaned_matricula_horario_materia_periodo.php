<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('matricula_horario_materia_periodo') && Schema::hasTable('matriculas')) {
            // 1. Eliminar huérfanos cuya matrícula fue anulada/eliminada (soft delete)
            if (Schema::hasColumn('matriculas', 'deleted_at')) {
                DB::table('matricula_horario_materia_periodo')
                    ->whereIn('matricula_id', function ($query) {
                        $query->select('id')
                            ->from('matriculas')
                            ->whereNotNull('deleted_at');
                    })
                    ->delete();
            }

            // 2. Eliminar huérfanos cuya matrícula no existe en la tabla matriculas
            DB::table('matricula_horario_materia_periodo')
                ->whereNotIn('matricula_id', function ($query) {
                    $query->select('id')
                        ->from('matriculas');
                })
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo / limpieza de huérfanos no reversible
    }
};
