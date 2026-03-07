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
        Schema::create('prerequisito_pasos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prerequisiteable_id'); // ID de Materia o Nivel
            $table->string('prerequisiteable_type'); // App\Models\Materia o App\Models\Nivel
            $table->integer('paso_crecimiento_id');
            $table->smallInteger('estado_requerido')->default(1); // 1 = no iniciado
            $table->timestamps();
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prerequisito_pasos');
    }
};
