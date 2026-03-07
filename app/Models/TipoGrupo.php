<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoGrupo extends Model
{
  use HasFactory;

  protected $fillable = [
    'nombre',
    'imagen',
    'descripcion',
    'seguimiento_actividad',
    'contiene_servidores',
    'posible_grupo_sede',
    'metros_cobertura',
    'ingresos_individuales_discipulos',
    'ingresos_individuales_lideres',
    'registra_datos_planeacion',
    'servidores_solo_discipulos',
    'color', // Corresponde a color_hex del formulario
    'visible_mapa_asignacion',
    'geo_icono',
    'nombre_plural',
    'tipo_evangelistico',
    'cantidad_maxima_reportes_semana',
    'enviar_mensaje_bienvenida',
    'mensaje_bienvenida',
    'orden',
    'tiempo_para_definir_inactivo_grupo',
    'registrar_inasistencia',
    'inasistencia_obligatoria',
    'automatizacion_tipo_usuario_id',
    'titulo1_finalizar_reporte',
    'descripcion1_finalizar_reporte',
    'subtitulo_encargados_finalizar_reporte',
    'subtitulo_sumatorias_adiccionales_finalizar_reporte',
    'subtitulo_miebros_finalizar_reporte',
    'subtitulo_ofrendas_finalizar_reporte',
    'descripcion_ofrendas_finalizar_reporte',
    'sumar_encargado_asistencia_grupo',
    'horasDisponiblidadLinkAsistencia',
    'estado',
  ];

  public function pasosCrecimiento(): BelongsToMany
  {
    return $this->belongsToMany(
      PasoCrecimiento::class,
      'tipo_grupo_pasos_crecimientos',
      'tipo_grupo_id',
      'paso_crecimiento_id'
    )->withPivot('created_at', 'updated_at', 'estado_por_defecto', 'pregunta');
  }

  // antes privilegiosUsuarios
  public function privilegiosRoles(): BelongsToMany
  {
    return $this->belongsToMany(
      Role::class,
      'privilegios_tipo_grupo_rol',
      'tipo_grupo_id',
      'rol_id'
    )->withPivot('asignar_asistente', 'desvincular_asistente', 'asignar_encargado', 'desvincular_encargado', 'created_at', 'updated_at');
  }

  // antes tipoAsistentesPermitidos
  public function tipoUsuariosPermitidos(): BelongsToMany
  {
    return $this->belongsToMany(
      TipoUsuario::class,
      'asignaciones_permitidas_tipo_usuario_tipo_grupo',
      'tipo_grupo_id',
      'tipo_usuario_id'
    )->withPivot('para_encargados', 'para_asistentes', 'created_at', 'updated_at');
  }

  public function automatizacionesPasosCrecimiento(): BelongsToMany
  {
    return $this->belongsToMany(
      PasoCrecimiento::class,
      'automatizaciones_tipo_grupo_paso_crecimiento',
      'tipo_grupo_id',
      'paso_crecimiento_id'
    )->withPivot('created_at', 'updated_at', 'estado_por_defecto', 'estado_por_defecto');
  }


  // relacion de muchos a muchos de TIPO GRUPO  con la tabla CLASIFICACION_ASISTENTES_REPORTE_GRUPO
  public function clasificacionAsistentes(): BelongsToMany
  {
    return $this->belongsToMany(
      ClasificacionAsistente::class,
      'clasificacion_asistente_tipo_grupo',
      'tipo_grupo_id',
      'clasificacion_asistente_id'
    )->withPivot('created_at', 'updated_at');
  }

  public function tiposOfrendas(): BelongsToMany
  {
    return $this->belongsToMany(
      TipoOfrenda::class,
      'tipo_grupo_tipo_ofrenda',
      'tipo_grupo_id',
      'tipo_ofrenda_id'
    )->withPivot('created_at', 'updated_at');
  }
}
