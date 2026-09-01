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
        Schema::create('solicitudes_canje', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_canje', 20)->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos_tienda')
                ->cascadeOnDelete();

            $table->integer('puntos_gastados');
            $table->string('estado', 50)->default('pendiente');
            $table->text('notas_admin')->nullable();

            $table->foreignId('procesado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('procesado_el')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_canje');
    }
};
