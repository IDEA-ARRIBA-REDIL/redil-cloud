<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('tipo_grupos', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 100);
      $table->string('imagen', 200)->nullable();
      $table->string('descripcion', 200)->nullable();
      $table->boolean('seguimiento_actividad')->nullable();
      $table->boolean('contiene_servidores')->nullable();
      $table->boolean('posible_grupo_sede')->default(0);
      $table->integer('metros_cobertura')->nullable()->default(500);
      $table->boolean('ingresos_individuales_discipulos')->default(1);
      $table->boolean('ingresos_individuales_lideres')->default(1);
      $table->boolean('registra_datos_planeacion')->default(0);
      $table->boolean('servidores_solo_discipulos')->default(1);
      $table->string('color', 10)->nullable();
      $table->boolean('visible_mapa_asignacion')->default(1);
      $table->string('geo_icono', 100)->nullable();
      $table->string('nombre_plural', 100)->nullable();
      $table->boolean('tipo_evangelistico')->default(0);
      $table->smallinteger('cantidad_maxima_reportes_semana')->nullable()->default(1);
      $table->boolean('enviar_mensaje_bienvenida')->default(0);
      $table->text('mensaje_bienvenida')->nullable();
      $table->smallinteger('orden')->nullable();
      $table->smallinteger('tiempo_para_definir_inactivo_grupo')->nullable()->default(30);
      $table->boolean('registrar_inasistencia')->nullable()->default(0); // nuevo campo, para que se se pueda determinar si el tipo de grupo va registrar inasistencia
      $table->boolean('inasistencia_obligatoria')->nullable()->default(0);
      $table->smallinteger('automatizacion_tipo_usuario_id')->nullable(); // Este campo sirve para cambiar de manera automatica el tipo de usuario indicado aquí cuando se agrege a un grupo que tenga este tipo de grupo.

      /* Estos son textos que van en la ventana de finalizar reporte */
      $table->string('titulo1_finalizar_reporte', 100)->default('Confirmar asistencia');
      $table->text('descripcion1_finalizar_reporte')->default('Gestiona aquí las asistencias de los miembros del grupo.');
      $table->string('subtitulo_encargados_finalizar_reporte', 100)->default('Encargados');
      $table->string('subtitulo_sumatorias_adiccionales_finalizar_reporte',)->default('Personas nuevas');
      $table->string('subtitulo_miembros_finalizar_reporte', 100)->default('Miebros del grupo');
      $table->string('subtitulo_ofrendas_finalizar_reporte', 100)->default('Ofrendas');
      $table->text('descripcion_ofrendas_finalizar_reporte', 100)->default('Ingresa el valor de las ofrendas recolectadas en el grupo.');

      // Esta variable antes estaba en confirguracion, pero ahora esta aqui para que segun el tipo de grupo se pueda sumar sus reportes las asistencias de los encargados
      $table->boolean('sumar_encargado_asistencia_grupo')->default(0);

      //
      $table->smallinteger('horas_disponiblidad_link_asistencia')->default(0);

      // Inactivo
      $table->boolean('estado')->nullable()->default(false);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipo_grupos');
  }
};
