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
        Schema::create('tipos_novedad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tipos de novedad iniciales
        DB::table('tipos_novedad')->insert([
            [
                'nombre' => 'Prerrequisito de materia no reconocido / pendiente',
                'descripcion' => 'Aplica cuando la persona ya cursó o aprobó la materia previa pero no figura en su historial académico.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Paso de crecimiento no registrado',
                'descripcion' => 'Aplica cuando completó Bautismo, Caminos a la Libertad u otro paso de crecimiento que no aparece concluido.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Error en documento de identidad / datos personales',
                'descripcion' => 'Aplica cuando el número de identificación u otros datos del perfil presentan discordancia tras la migración.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Inconveniente con cupos o sede',
                'descripcion' => 'Aplica cuando no encuentra disponibilidad en su sede o presenta restricción de sede.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Dificultad en proceso de pago / inscripción',
                'descripcion' => 'Aplica cuando el carrito o la pasarela rechaza la transacción o el registro.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Otro inconveniente',
                'descripcion' => 'Cualquier otra novedad no clasificada anteriormente.',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_novedad');
    }
};
