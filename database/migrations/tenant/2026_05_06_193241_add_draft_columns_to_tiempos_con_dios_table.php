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
        Schema::table('tiempos_con_dios', function (Blueprint $table) {
            $table->string('estado')->default('en_progreso')->after('fecha');
            $table->integer('paso_actual')->default(1)->after('estado');
            $table->string('modo')->default('propia')->after('paso_actual');
            $table->unsignedBigInteger('plan_lector_id')->nullable()->after('modo');
            $table->unsignedBigInteger('plan_lector_dia_id')->nullable()->after('plan_lector_id');
            
            // Si hay planes lectores, lo vinculamos opcionalmente
            // $table->foreign('plan_lector_id')->references('id')->on('planes_lectores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiempos_con_dios', function (Blueprint $table) {
            $table->dropColumn(['estado', 'paso_actual', 'modo', 'plan_lector_id', 'plan_lector_dia_id']);
        });
    }
};
