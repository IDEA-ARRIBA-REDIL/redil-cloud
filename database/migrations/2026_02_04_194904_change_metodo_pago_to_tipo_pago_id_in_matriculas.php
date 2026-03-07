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
        Schema::table('matriculas', function (Blueprint $table) {
            // Eliminar la columna anterior (string)
            $table->dropColumn('metodo_pago');

            // Agregar la nueva columna (integer, nullable)
            // Asumimos que es nullable porque antes era nullable y el user dijo "que se quede nullable"
            $table->integer('tipo_pago_id')->nullable()->after('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('tipo_pago_id');
            $table->string('metodo_pago')->nullable();
        });
    }
};
