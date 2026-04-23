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
        // 1. Convertir los valores existentes a JSON válido antes de cambiar el tipo.
        //    Cada valor como "individual" pasa a '["individual"]'
        DB::table('tipos_notificaciones')->get()->each(function ($row) {
            $alcanceActual = $row->alcance ?? 'individual';

            // Si ya es JSON válido (migración re-ejecutada) no lo tocamos
            $decoded = json_decode($alcanceActual, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                $alcanceActual = json_encode([$alcanceActual]);
            } else {
                $alcanceActual = $row->alcance; // ya era JSON, dejarlo igual
            }

            DB::table('tipos_notificaciones')
                ->where('id', $row->id)
                ->update(['alcance' => $alcanceActual]);
        });

        // 2. Eliminar el DEFAULT antes de cambiar el tipo (PostgreSQL lo requiere)
        DB::statement('ALTER TABLE tipos_notificaciones ALTER COLUMN alcance DROP DEFAULT');

        // 3. Cambiar el tipo de columna a JSON usando USING (requerido en PostgreSQL)
        DB::statement('ALTER TABLE tipos_notificaciones ALTER COLUMN alcance TYPE json USING alcance::json');

        // 4. Agregar el campo de vigencia
        Schema::table('tipos_notificaciones', function (Blueprint $table) {
            $table->unsignedSmallInteger('dias_vigencia')->nullable()->after('alcance')
                ->comment('Días que una notificación permanece visible. NULL = sin caducidad.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir dias_vigencia
        Schema::table('tipos_notificaciones', function (Blueprint $table) {
            $table->dropColumn('dias_vigencia');
        });

        // Revertir alcance a string tomando el primer elemento del array JSON
        DB::statement("
            ALTER TABLE tipos_notificaciones
            ALTER COLUMN alcance TYPE varchar(100)
            USING (alcance::json->>0)
        ");

        // Restaurar el valor por defecto
        DB::statement("ALTER TABLE tipos_notificaciones ALTER COLUMN alcance SET DEFAULT 'individual'");
    }
};
