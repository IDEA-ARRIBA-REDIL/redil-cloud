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
        Schema::create('maestros', function (Blueprint $table) 
        {
          $table->id();
          // Clave foránea a la tabla 'users'. Asume que tu tabla de usuarios se llama 'users'.
          // onDelete('cascade') significa que si el usuario se elimina, su registro de maestro también.
          // Ajusta onDelete() según tus necesidades (cascade, set null, restrict).
          $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique(); // unique() para asegurar que un usuario solo sea maestro una vez
          $table->boolean('activo')->default(true); // Default a true puede ser más conveniente
          $table->text('descripcion')->nullable(); // Cambiado a TEXT y permite nulo
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maestros');
    }
};
