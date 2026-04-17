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
        Schema::table('cursos', function (Blueprint $table) {
            // 1. Límite de reinicios permitido (0 = ilimitado)
            $table->integer('limite_reintentos')->default(0)->after('estado');
            
            // 2. Días de espera para volver a inscribirse (castigo)
            $table->integer('dias_castigo')->default(0)->after('limite_reintentos');
            
            // 3. Términos y condiciones específicos del curso
            $table->longText('terminos_condiciones')->nullable()->after('mensaje_aprobacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['limite_reintentos', 'dias_castigo', 'terminos_condiciones']);
        });
    }
};
