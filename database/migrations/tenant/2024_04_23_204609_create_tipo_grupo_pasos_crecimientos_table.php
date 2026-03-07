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
        Schema::create('tipo_grupo_pasos_crecimientos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('tipo_grupo_id');
            $table->integer('paso_crecimiento_id');
            $table->string('pregunta', 100);
            $table->smallinteger('estado_por_defecto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('tipo_grupo_pasos_crecimientos');
    }
};
