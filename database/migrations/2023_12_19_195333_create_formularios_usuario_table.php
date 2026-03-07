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
    // antes la tabla se llamaba formularios_asistente
    Schema::create('formularios_usuario', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->softDeletes();
      $table->string('nombre', 200); //Este va ser para identificarlo en la plataforma
      $table->string('titulo', 200); // Este es el que se va mostrar para el usuario
      $table->string('label', 200); // Este es el que se imprime por ejemplo en el menú, o en las opciones
      $table->text('descripcion')->nullable();
      // Lo de terminos y condiciones
      $table->boolean('visible_terminos_condiciones')->default(1);
      //$table->boolean('obligatorio_terminos_condiciones')->default(1);
      $table->text('label_terminos_condiciones')->nullable();
      $table->text('url_terminos_condiciones')->nullable();
      $table->text('mensaje_terminos_condiciones_resumen')->nullable();
      $table->text('mensaje_terminos_condiciones_detallado')->nullable(); // Si se llena este campo abre el modal.


      $table->smallInteger('edad_minima')->nullable();
      $table->smallInteger('edad_maxima')->nullable();
      $table->text('edad_mensaje_error')->nullable();
      $table->boolean('validar_edad')->default(1);
      $table->integer('tipo_usuario_default_id')->nullable();
      $table->integer('tipo_formulario_id'); // antes llamado privilegio, ahora una tabla donde puede seleccionar por ejemplo 'nuevo', 'modificar'


      //$table->integer('formulario_modal_persona_externa_id')->nullable();
      //$table->string('btn_cancelar', 300)->nullable();


      //$table->boolean('guardado_ajax')->default(0);
      //$table->boolean('es_formulario_exterior')->default(0);
      //$table->boolean('usuario_responsable_automatico')->default(0);
      //$table->boolean('reprocesar_desactualizados')->nullable()->default(0);
      //$table->boolean('pendiente_por_aprobacion')->default(0);
      //$table->boolean('es_modal')->default(0);


      //$table->boolean('visible_foto')->default(0);
      //$table->boolean('visible_identificacion')->default(0);
      //$table->string('label_identificacion', 100)->nullable();
      //$table->string('class_identificacion', 100);
      //$table->boolean('obligatorio_identificacion')->default(0);
      //$table->boolean('visible_fecha_nacimiento')->default(0);
      //$table->string('label_fecha_nacimiento', 100)->nullable();
      //$table->string('class_fecha_nacimiento', 100);
      //$table->boolean('obligatorio_fecha_nacimiento')->default(0);
      //$table->boolean('visible_genero')->default(0);
      //$table->string('label_genero', 100)->nullable();
      //$table->string('class_genero', 100);
      //$table->boolean('obligatorio_genero')->default(0);
      //$table->boolean('visible_primer_nombre')->default(0);
      //$table->string('label_primer_nombre', 100)->nullable();
      //$table->string('class_primer_nombre', 100);
      //$table->boolean('obligatorio_primer_nombre')->default(0);
      //$table->boolean('visible_primer_apellido')->default(0);
      //$table->string('label_primer_apellido', 100)->nullable();
      //$table->string('class_primer_apellido', 100);
      //$table->boolean('obligatorio_primer_apellido')->default(0);
      //$table->boolean('visible_segundo_nombre')->default(0);
      //$table->string('label_segundo_nombre', 100)->nullable();
      //$table->string('class_segundo_nombre', 100);
      //$table->boolean('obligatorio_segundo_nombre')->default(0);
      //$table->boolean('visible_segundo_apellido')->default(0);
      //$table->string('label_segundo_apellido', 100)->nullable();
      //$table->string('class_segundo_apellido', 100);
      //$table->boolean('obligatorio_segundo_apellido')->default(0);
      //$table->boolean('visible_estado_civil')->default(0);
      //$table->string('label_estado_civil', 100)->nullable();
      //$table->string('class_estado_civil', 100);
      //$table->boolean('obligatorio_estado_civil')->default(0);
      //$table->boolean('visible_pais_nacimiento')->default(0);
      //$table->string('label_pais_nacimiento', 100)->nullable();
      //$table->string('class_pais_nacimiento', 100);
      //$table->boolean('obligatorio_pais_nacimiento')->default(0);
      //$table->boolean('visible_telefono_fijo')->default(0);
      //$table->string('label_telefono_fijo', 100)->nullable();
      //$table->string('class_telefono_fijo', 100);
      //$table->boolean('obligatorio_telefono_fijo')->default(0);
      //$table->boolean('visible_telefono_movil')->default(0);
      //$table->string('label_telefono_movil', 100)->nullable();
      //$table->string('class_telefono_movil', 100);
      //$table->boolean('obligatorio_telefono_movil')->default(0);
      //$table->boolean('visible_telefono_otro')->default(0);
      //$table->string('label_telefono_otro', 100)->nullable();
      //$table->string('class_telefono_otro', 100);
      //$table->boolean('obligatorio_telefono_otro')->default(0);
      //$table->boolean('visible_email')->default(0);
      //$table->string('label_email', 100)->nullable();
      //$table->string('class_email', 100);
      //$table->boolean('obligatorio_email')->default(0);
      //$table->boolean('visible_direccion')->default(0);
      //$table->string('label_direccion', 100)->nullable();
      //$table->string('class_direccion', 100);
      //$table->boolean('obligatorio_direccion')->default(0);
      //$table->boolean('visible_vivienda_en_calidad_de')->default(0);
      //$table->string('label_vivienda_en_calidad_de', 100)->nullable();
      //$table->string('class_vivienda_en_calidad_de', 100);
      //$table->boolean('obligatorio_vivienda_en_calidad_de')->default(0);
      //$table->boolean('visible_nivel_academico')->default(0);
      //$table->string('label_nivel_academico', 100)->nullable();
      //$table->string('class_nivel_academico', 100);
      //$table->boolean('obligatorio_nivel_academico')->default(0);
      //$table->boolean('visible_estado_nivel_academico')->default(0);
      //$table->string('label_estado_nivel_academico', 100)->nullable();
      //$table->string('class_estado_nivel_academico', 100);
      //$table->boolean('obligatorio_estado_nivel_academico')->default(0);
      //$table->boolean('visible_profesion')->default(0);
      //$table->string('label_profesion', 100)->nullable();
      //$table->string('class_profesion', 100);
      //$table->boolean('obligatorio_profesion')->default(0);
      //$table->boolean('visible_ocupacion')->default(0);
      //$table->string('label_ocupacion', 100)->nullable();
      //$table->string('class_ocupacion', 100);
      //$table->boolean('obligatorio_ocupacion')->default(0);
      //$table->boolean('visible_sector_economico')->default(0);
      //$table->string('label_sector_economico', 100)->nullable();
      //$table->string('class_sector_economico', 100);
      //$table->boolean('obligatorio_sector_economico')->default(0);
      //$table->boolean('visible_tipo_sangre')->default(0);
      //$table->string('label_tipo_sangre', 100)->nullable();
      //$table->string('class_tipo_sangre', 100);
      //$table->boolean('obligatorio_tipo_sangre')->default(0);
      //$table->boolean('visible_indicaciones_medicas')->default(0);
      //$table->string('label_indicaciones_medicas', 100)->nullable();
      //$table->string('class_indicaciones_medicas', 100);
      //$table->boolean('obligatorio_indicaciones_medicas')->default(0);
      //$table->boolean('visible_tipo_vinculacion')->default(0);
      //$table->string('label_tipo_vinculacion', 100)->nullable();
      //$table->string('class_tipo_vinculacion', 100);
      //$table->boolean('obligatorio_tipo_vinculacion')->default(0);
      //$table->boolean('visible_informacion_opcional')->default(0);
      //$table->string('label_informacion_opcional', 100)->nullable();
      //$table->string('class_informacion_opcional', 100);
      //$table->boolean('obligatorio_informacion_opcional')->default(0);
      //$table->boolean('visible_campo_reservado')->default(0);
      //$table->string('label_campo_reservado', 100)->nullable();
      //$table->string('class_campo_reservado', 100);
      //$table->boolean('obligatorio_campo_reservado')->default(0);
      //$table->boolean('visible_seccion_1')->default(0);
      //$table->string('label_seccion_1', 100)->nullable();
      //$table->string('class_seccion_1', 100)->nullable();
      //$table->boolean('visible_seccion_2')->default(0);
      //$table->string('label_seccion_2', 100)->nullable();
      //$table->string('class_seccion_2', 100)->nullable();
      //$table->boolean('visible_seccion_3')->default(0);
      //$table->string('label_seccion_3', 100)->nullable();
      //$table->string('class_seccion_3', 100)->nullable();
      //$table->boolean('visible_seccion_4')->default(0);
      //$table->string('label_seccion_4', 100)->nullable();
      //$table->string('class_seccion_4', 100)->nullable();
      //$table->boolean('visible_seccion_5')->default(0);
      //$table->string('label_seccion_5', 100)->nullable();
      //$table->string('class_seccion_5', 100)->nullable();
      //$table->boolean('visible_seccion_6')->default(0);
      //$table->string('label_seccion_6', 100)->nullable();
      //$table->string('class_seccion_6', 100)->nullable();
      //$table->boolean('visible_tipo_identificacion')->default(0);
      //$table->string('label_tipo_identificacion', 100)->nullable();
      //$table->string('class_tipo_identificacion', 100)->default('col-lg-3 col-md-6 col-sm-6 col-xs-12');
      //$table->boolean('obligatorio_tipo_identificacion')->default(0);



      //$table->boolean('visible_seccion_7')->default(0);
      //$table->string('label_seccion_7', 100)->nullable();
      //$table->string('class_seccion_7', 100)->nullable();
      //$table->boolean('visible_identificacion_responsable')->default(0);
      //$table->string('class_identificacion_responsable', 100)->nullable();
      //$table->boolean('obligatorio_identificacion_responsable')->default(0);
      //$table->string('label_identificacion_responsable', 100)->nullable();
      //$table->string('class_formulario', 100)->nullable();
      //$table->boolean('visible_seccion_8')->default(0);
      //$table->string('label_seccion_8', 100)->nullable();
      //$table->string('class_seccion_8', 100)->nullable();
      //$table->boolean('visible_telefono_acudiente')->default(0);
      //$table->string('label_telefono_acudiente', 100)->nullable();
      //$table->string('class_telefono_acudiente', 100)->nullable();
      //$table->boolean('obligatorio_telefono_acudiente')->default(0);
      //$table->boolean('visible_nombre_acudiente')->default(0);
      //$table->string('label_nombre_acudiente', 100)->nullable();
      //$table->string('class_nombre_acudiente', 100)->nullable();
      //$table->boolean('obligatorio_nombre_acudiente')->default(0);
      //$table->boolean('visible_tipo_identificacion_acudiente')->default(0);
      //$table->string('label_tipo_identificacion_acudiente', 100)->nullable();
      //$table->string('class_tipo_identificacion_acudiente', 100)->nullable();
      //$table->boolean('obligatorio_tipo_identificacion_acudiente')->default(0);
      //$table->boolean('visible_identificacion_acudiente')->default(0);
      //$table->string('label_identificacion_acudiente', 100)->nullable();
      //$table->string('class_identificacion_acudiente', 100)->nullable();
      //$table->boolean('obligatorio_identificacion_acudiente')->default(0);
      //$table->boolean('visible_archivo_a')->default(0);
      //$table->string('label_archivo_a', 100)->nullable();
      //$table->string('class_archivo_a', 100)->nullable();
      //$table->boolean('obligatorio_archivo_a')->default(0);
      //$table->boolean('descargable_archivo_a')->default(0);
      //$table->boolean('visible_archivo_b')->default(0);
      //$table->string('label_archivo_b', 100)->nullable();
      //$table->string('class_archivo_b', 100)->nullable();
      //$table->boolean('obligatorio_archivo_b')->default(0);
      //$table->boolean('descargable_archivo_b')->default(0);
      //$table->boolean('visible_archivo_c')->default(0);
      //$table->string('label_archivo_c', 100)->nullable();
      //$table->string('class_archivo_c', 100)->nullable();
      //$table->boolean('obligatorio_archivo_c')->default(0);
      //$table->boolean('descargable_archivo_c')->default(0);
      //$table->boolean('visible_archivo_d')->default(0);
      //$table->string('label_archivo_d', 100)->nullable();
      //$table->string('class_archivo_d', 100)->nullable();
      //$table->boolean('obligatorio_archivo_d')->default(0);
      //$table->boolean('descargable_archivo_d')->default(0);

      //$table->boolean('visible_pasos_crecimiento')->default(0);
      //$table->string('class_pasos_crecimiento', 100)->default('col-lg-3 col-md-6 col-sm-6 col-xs-12');
      //$table->boolean('visible_fecha_ingreso')->default(0);
      //$table->string('class_fecha_ingreso', 100)->default('col-lg-3 col-md-6 col-sm-6 col-xs-12');
      //$table->boolean('obligatorio_fecha_ingreso')->default(0);
      //$table->string('label_fecha_ingreso', 100)->nullable();

      //$table->boolean('visible_sede')->nullable()->default(0);
      //$table->string('class_sede', 100)->nullable();
      //$table->boolean('obligatorio_sede')->nullable()->default(0);
      //$table->string('label_sede', 100)->nullable();
      //$table->boolean('visible_seccion_campos_extra')->nullable()->default(0);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('formularios_usuario');
  }
};
