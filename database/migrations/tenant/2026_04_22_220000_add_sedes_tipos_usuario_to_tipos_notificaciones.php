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
        Schema::table('tipos_notificaciones', function (Blueprint $table) {
            // NULL = aplica a todas las sedes. JSON array de IDs = solo esas sedes.
            $table->json('sedes_ids')->nullable()->after('dias_vigencia')
                ->comment('IDs de sedes destino. NULL = todas las sedes.');

            // NULL = aplica a todos los tipos de usuario. JSON array de IDs = solo esos tipos.
            $table->json('tipos_usuario_ids')->nullable()->after('sedes_ids')
                ->comment('IDs de tipos de usuario destino. NULL = todos los tipos.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_notificaciones', function (Blueprint $table) {
            $table->dropColumn(['sedes_ids', 'tipos_usuario_ids']);
        });
    }
};
