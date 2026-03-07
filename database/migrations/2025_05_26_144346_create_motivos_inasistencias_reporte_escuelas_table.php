<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_inasistencias_reporte_escuelas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 191);
            $table->text('descripcion')->nullable();
            // Opcional: Si los motivos son específicos de una escuela
            // $table->foreignId('escuela_id')->nullable()->constrained('escuelas')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_inasistencias_reporte_escuelas');
    }
};