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
        Schema::table('tipo_identificaciones', function (Blueprint $table) {
            $table->string('tipo_caracteres', 30)->default('solo_numeros')->after('abreviatura');
            $table->boolean('forzar_mayusculas')->default(true)->after('tipo_caracteres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_identificaciones', function (Blueprint $table) {
            $table->dropColumn(['tipo_caracteres', 'forzar_mayusculas']);
        });
    }
};
