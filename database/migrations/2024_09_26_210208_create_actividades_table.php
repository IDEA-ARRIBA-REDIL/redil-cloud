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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->text('descripcion_corta')->nullable();
            $table->text('mensaje_informativo')->nullable();
            
            $table->date('fecha_inicio')->nullable(); 
            $table->date('fecha_finalizacion')->nullable();
            $table->date('fecha_visualizacion')->nullable();
            $table->date('fecha_cierre')->nullable();

            $table->string('codigo_sap', 50)->nullable();
            $table->integer('tipo_actividad_id')->nullable();

            $table->boolean('totalmente_publica')->default('false')->nullable();
            $table->integer('genero')->nullable();
            $table->integer('vinculacion_grupo')->nullable();
            $table->integer('actividad_grupo')->nullable();
            $table->text('evaluacion_general')->nullable();
            $table->text('evaluacion_financiera')->nullable();
            $table->text('motivos_cancelacion')->nullable();
            $table->boolean('excluyente')->default('false')->nullable();
            $table->text('instrucciones_finales')->nullable();
            $table->string('proyecto_sap', 20)->nullable();
            $table->string('centro_costo_sap', 20)->nullable();
            $table->string('sucursal_sap', 20)->nullable();
            $table->softDeletes();
            $table->boolean('punto_de_pago')->default('false')->nullable();
            $table->integer('incremento_pdp')->nullable();
            $table->boolean('permite_personas_externas')->default('false')->nullable();
            $table->string('color', 30)->nullable();
            $table->string('fondo', 30)->nullable();
            $table->text('label_destinatario')->nullable();
            $table->boolean('activa')->default('true')->nullable();
            $table->boolean('restriccion_por_categoria')->default('false')->nullable();
            $table->integer('aforo_ocupado')->nullable();
            $table->integer('aforo')->nullable();
            $table->integer('periodo_id')->nullable();
            $table->tinyInteger('estado_inscripcion_defecto')->default(3)->nullable()->after('activa'); //'1: Iniciada, 2: Pendiente, 3: Finalizada'
            $table->boolean('tiene_invitados')->default('false');
            $table->boolean('editar_formulario')->default('false');
            $table->boolean('pagos_abonos_con_valores_cerrados')->default('false');
            $table->boolean('mostrar_en_proximas_actividades')->default('false');
            $table->integer('tipo_usuario_objetivo_id')->nullable();
            $table->string('password', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
