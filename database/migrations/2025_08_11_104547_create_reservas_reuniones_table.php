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
        Schema::create('reservas_reuniones', function (Blueprint $table) {
            $table->id();
            $table->integer('reporte_reunion_id');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->boolean('invitado')->nullable();
            $table->boolean('registrada')->default('false');
            $table->string('nombre_invitado', 100)->nullable();
            $table->string('email_invitado', 50)->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users'); // Este es el id del usuario que lo invito
            $table->integer('autor_creacion_reserva_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_reuniones');
    }
};
