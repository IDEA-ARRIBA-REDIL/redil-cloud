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
    Schema::create('users', function (Blueprint $table) {
      //Campos de user
      $table->id();
      //$table->string('name');
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
      $table->timestamps();
      $table->softDeletes();
      $table->boolean('activo');
      $table->integer('asistente_id')->nullable();

      // Campos del asistente
      $table->string('primer_nombre', 50);
      $table->string('primer_apellido', 50);
      $table->smallInteger('genero')->default(0); // 0 = Masculino, 1 = Femenino
      $table->integer('tipo_identificacion_id')->nullable(); // antes tipo_identificacion
      $table->string('identificacion', 20)->unique()->nullable();
      $table->date('fecha_nacimiento')->nullable();
      $table->string('direccion', 200)->nullable();
      $table->string('telefono_fijo', 20)->nullable();
      $table->string('telefono_movil', 20)->nullable();
      $table->string('telefono_otro', 20)->nullable();
      $table->integer('estado_civil_id')->nullable(); // antes estado_civil
      $table->date('fecha_ingreso')->nullable(); // OJO ... Este campo no esta en el formulario
      $table->text('indicaciones_medicas')->nullable();
      $table->string('foto', 20);
      $table->string('portada', 20)->default('profile-banner.png');
      $table->integer('tipo_usuario_id'); // antes tipo_asistente_id ¿Que pasa cuando es un usuario operador?
      $table->string('segundo_nombre', 50)->nullable();
      $table->string('segundo_apellido', 50)->nullable();
      $table->integer('profesion_id')->nullable(); // antes profesion
      $table->integer('nivel_academico_id')->nullable(); // antes nivel_academico
      $table->integer('sector_economico_id')->nullable(); // antes sector_economico
      $table->integer('tipo_vivienda_id')->nullable(); // antes tipo_vivienda
      $table->integer('barrio_id')->nullable();
      $table->integer('localidad_id')->nullable();
      $table->integer('pais_id')->nullable();
      $table->integer('estado_nivel_academico_id')->nullable(); // antes estado_nivel_academico
      $table->string('barrio_auxiliar', 50)->nullable();
      $table->integer('tipo_vinculacion_id')->default(1);
      $table->text('informacion_opcional')->nullable();
      $table->integer('sede_id')->default(2);
      $table->integer('ocupacion_id')->nullable(); // antes ocupacion
      $table->smallInteger('tipo_sangre_id')->nullable(); // antes tipo_sangre
      $table->text('campo_reservado')->nullable();
      $table->boolean('creado_como_menor_edad')->default(0);
      $table->boolean('creado_como_mayor_edad')->default(1);
      $table->boolean('activado_como_mayor_edad')->default(0);
      $table->string('nombre_acudiente', 200)->nullable();
      $table->string('telefono_acudiente', 20)->nullable();
      $table->dateTime('ultimo_reporte_grupo', $precision = 0)->default('2016-01-01 05:00:01');
      $table->dateTime('ultimo_reporte_grupo_auxiliar', $precision = 0)->default('2016-01-01 05:00:01');
      $table->dateTime('ultimo_reporte_reunion_auxiliar', $precision = 0)->default('2016-01-01 05:00:01');
      $table->dateTime('ultimo_reporte_reunion', $precision = 0)->default('2016-01-01 05:00:01');
      $table->integer('tipo_identificacion_acudiente_id')->nullable(); // antes tipo_identificacion_acudiente
      $table->string('identificacion_acudiente', 20)->nullable();
      $table->string('archivo_a', 20)->nullable();
      $table->string('archivo_b', 20)->nullable();
      $table->string('archivo_c', 20)->nullable();
      $table->string('archivo_d', 20)->nullable();
      $table->boolean('esta_aprobado')->default(1);
      $table->date('fecha_actualizacion')->nullable();
      $table->boolean('identificador_menor_desactualizado')->default(0);
      $table->boolean('ingresado_por_formulario_donacion')->default(0);
      $table->boolean('formulario_conectados')->default(0);
      $table->string('recepcion_conectados')->nullable();
      $table->integer('usuario_creacion_id')->nullable();
      $table->integer('rol_de_creacion_id')->nullable(); // antes tipo_usuario_de_creacion_id
      $table->integer('asistente_de_creacion_id')->nullable();
      $table->integer('indice_grafico_ministerial')->default(1);
      $table->integer('trasladado')->default(false);

      // nuevos campos
      $table->boolean('tiene_hijos')->default(false);
      $table->boolean('mostrar_modal_agregar_hijos')->default(false)->after('tiene_hijos');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {

    Schema::dropIfExists('users');
  }
};
