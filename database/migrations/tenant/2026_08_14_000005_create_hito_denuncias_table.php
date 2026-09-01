<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para gestionar reportes/denuncias de fotos o contenido en hitos.
     */
    public function up(): void
    {
        Schema::create('hito_denuncias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('foto_id')->nullable()->constrained('hito_fotos')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->comment('Usuario que reporta');
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->comment('Admin que resuelve la denuncia');
            $table->string('motivo', 255);
            $table->enum('estado', ['pendiente', 'resuelta'])->default('pendiente');
            $table->text('observaciones_admin')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hito_denuncias');
    }
};
