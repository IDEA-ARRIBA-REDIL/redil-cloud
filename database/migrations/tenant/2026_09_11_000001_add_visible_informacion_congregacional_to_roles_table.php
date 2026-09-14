<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('visible_informacion_congregacional')->nullable()->default(1);
        });

        // Desactivar visibilidad por defecto para roles gestionados exclusivamente por módulos especializados
        DB::table('roles')
            ->where('es_consejero', true)
            ->orWhere('es_intercesor', true)
            ->orWhere('es_maestro', true)
            ->orWhere('es_cajero_pdp', true)
            ->orWhere('es_encargado_pdp', true)
            ->update(['visible_informacion_congregacional' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('visible_informacion_congregacional');
        });
    }
};
