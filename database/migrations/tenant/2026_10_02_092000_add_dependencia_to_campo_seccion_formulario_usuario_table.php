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
        Schema::table('campo_seccion_formulario_usuario', function (Blueprint $table) {
            $table->integer('depende_de_campo_id')->nullable()->after('campo_id');
            $table->string('tipo_condicion', 50)->nullable()->after('depende_de_campo_id');
            $table->string('valor_condicion', 255)->nullable()->after('tipo_condicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campo_seccion_formulario_usuario', function (Blueprint $table) {
            $table->dropColumn(['depende_de_campo_id', 'tipo_condicion', 'valor_condicion']);
        });
    }
};
