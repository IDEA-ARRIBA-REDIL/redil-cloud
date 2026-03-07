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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id(); // Equivalente a tu primary key auto-incremental


            // Definimos las llaves foráneas. Las hacemos 'nullable' como en tu esquema.
            // El método constrained() asume que las tablas se llaman 'users', 'actividad_categorias', etc.
            // y que la columna es 'id'. Ajusta si tus tablas tienen otros nombres.
            $table->integer('user_id')->nullable();
            $table->integer('actividad_categoria_id');
            $table->integer('compra_id')->nullable(); // Asumiendo que tienes una tabla 'compras'

            $table->date('fecha')->nullable();
            $table->date('fecha_pago')->nullable();

            // Un campo booleano que por defecto es 'false' y no puede ser nulo.
            $table->integer('estado')->default(1)->comment('1: Iniciada, 2: Pendiente, 3: Finalizada');

            // Usamos el tipo 'json' de Laravel, que es más eficiente para manejar JSON que 'text'.
            $table->json('json_campos_adicionales')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('nombre_inscrito', 100)->nullable();
            $table->integer('inscripcion_asociada')->nullable();
            $table->integer('limite_invitados')->nullable();
            $table->timestamps(); // Crea las columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
