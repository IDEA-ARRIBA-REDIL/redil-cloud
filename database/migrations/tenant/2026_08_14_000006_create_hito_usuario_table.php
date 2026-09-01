<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla reservada exclusivamente para asignaciones manuales individuales
     * (condecoraciones, reconocimientos directos) o metadatos personalizados por usuario.
     */
    public function up(): void
    {
        Schema::create('hito_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha')->comment('Fecha en que se otorgó el hito al usuario');
            $table->boolean('asistio')->default(true);
            $table->string('origen_tipo', 50)->default('manual')
                ->comment('manual | personalizado');
            $table->unsignedBigInteger('origen_id')->nullable()
                ->comment('ID de referencia si proviene de una entidad específica');
            $table->foreignId('asignado_por')->nullable()->constrained('users')->comment('Admin que realizó la asignación');
            $table->text('nota_personalizada')->nullable()
                ->comment('Dedicatoria o nota personal visible solo para este usuario');
            $table->timestamps();

            $table->unique(['hito_id', 'user_id', 'origen_tipo', 'origen_id'], 'hito_usuario_unico');
            $table->index(['user_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hito_usuario');
    }
};
