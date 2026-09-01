<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tablas pivote de segmentación y restricciones de visibilidad para Hitos
     * (Sedes, Estados Civiles, Rangos de Edad, Tipos de Usuario, Tipos de Grupo).
     */
    public function up(): void
    {
        // 1. Restricción por Sede
        Schema::create('hito_sedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('sede_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['hito_id', 'sede_id']);
        });

        // 2. Restricción por Estado Civil
        Schema::create('hito_estados_civiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['hito_id', 'estado_civil_id']);
        });

        // 3. Restricción por Rango de Edad
        Schema::create('hito_rangos_edad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('rango_edad_id')->constrained('rangos_edad')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['hito_id', 'rango_edad_id']);
        });

        // 4. Restricción por Tipo de Usuario
        Schema::create('hito_tipos_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('tipo_usuario_id')->constrained('tipo_usuarios')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['hito_id', 'tipo_usuario_id']);
        });

        // 5. Restricción por Tipo de Grupo
        Schema::create('hito_grupo_tipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('tipo_grupo_id')->constrained('tipo_grupos')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['hito_id', 'tipo_grupo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hito_grupo_tipos');
        Schema::dropIfExists('hito_tipos_usuarios');
        Schema::dropIfExists('hito_rangos_edad');
        Schema::dropIfExists('hito_estados_civiles');
        Schema::dropIfExists('hito_sedes');
    }
};
