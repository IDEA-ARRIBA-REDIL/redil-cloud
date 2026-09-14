<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $guarded = ['id', 'singleton_key'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'singleton_key' => 'boolean',
            'version' => 'integer',
            'limite_menor_edad' => 'integer',
            'visible_seccion_campos_extra' => 'boolean',
            'visible_seccion_campos_extra_grupo' => 'boolean',
            'logo_personalizado' => 'boolean',
            'usa_listas_geograficas' => 'boolean',
            'direccion_obligatoria' => 'boolean',
            'dia_corte_reportes_grupos' => 'integer',
            'dia_recordatorio_para_reporte_grupos' => 'integer',
            'dias_plazo_reporte_grupo' => 'integer',
            'reportar_grupo_cualquier_dia' => 'boolean',
            'sumar_encargado_asistencia_grupo' => 'boolean',
            'maximos_niveles_grafico_ministerio' => 'integer',
            'habilitar_nombre_grupo' => 'boolean',
            'nombre_grupo_obligatorio' => 'boolean',
            'habilitar_tipo_grupo' => 'boolean',
            'tipo_grupo_obligatorio' => 'boolean',
            'habilitar_telefono_grupo' => 'boolean',
            'telefono_grupo_obligatorio' => 'boolean',
            'habilitar_tipo_vivienda_grupo' => 'boolean',
            'tipo_vivienda_grupo_obligatorio' => 'boolean',
            'habilitar_hora_reunion_grupo' => 'boolean',
            'hora_reunion_grupo_obligatorio' => 'boolean',
            'habilitar_dia_reunion_grupo' => 'boolean',
            'dia_reunion_grupo_obligatorio' => 'boolean',
            'habilitar_direccion_grupo' => 'boolean',
            'direccion_grupo_obligatorio' => 'boolean',
            'habilitar_fecha_creacion_grupo' => 'boolean',
            'fecha_creacion_grupo_obligatorio' => 'boolean',
            'habilitar_campo_opcional1_grupo' => 'boolean',
            'campo_opcional1_obligatorio' => 'boolean',
            'habilitar_campo_1_informe_evidencias_grupo' => 'boolean',
            'campo_1_informe_evidencias_grupo_obligatorio' => 'boolean',
            'habilitar_campo_2_informe_evidencias_grupo' => 'boolean',
            'campo_2_informe_evidencias_grupo_obligatorio' => 'boolean',
            'habilitar_campo_3_informe_evidencias_grupo' => 'boolean',
            'campo_3_informe_evidencias_grupo_obligatorio' => 'boolean',
            'correo_por_defecto' => 'boolean',
            'identificacion_obligatoria' => 'boolean',
            'identificacion_solo_numerica' => 'boolean',
            'tiempo_para_definir_inactivo_grupo' => 'integer',
            'tiempo_para_definir_inactivo_reunion' => 'integer',
            'edad_minima_logueo' => 'integer',
            'enviar_correo_bienvenida_nuevo_asistente' => 'boolean',
            'banner_mensaje_bienvenida' => 'boolean',
            'valor_minimo_resaltador_informe_mensual_reportes_grupo' => 'integer',
            'valor_maximo_resaltador_informe_mensual_reportes_grupo' => 'integer',
            'habilitar_observacion_anadir_invitados_modal' => 'boolean',
            'habilitar_contador_anadir_invitados_modal' => 'boolean',
            'moneda_predeterminada_punto_pago' => 'integer',
            'tiene_sistema_aprobacion_de_reporte' => 'boolean',
            'opciones_extra_matriculas_escuelas' => 'boolean',
            'opcion_material_sede' => 'boolean',
            'habilitar_salones_con_estaciones' => 'boolean',
            'items_mixtos_escuelas_deshabilitados' => 'boolean',
            'cierre_cortes_habilitado' => 'boolean',
            'habilitar_traslados' => 'boolean',
            'cantidad_intentos_traslados' => 'integer',
            'cantidad_dias_alerta_notas_maestro' => 'integer',
            'espacio_academico_habilitado' => 'boolean',
            'cantidad_intentos_auto_matricula' => 'integer',
            'dias_plazo_maximo_actualizacion_automatricula' => 'integer',
            'envio_material' => 'boolean',
            'edad_minima_consolidacion' => 'integer',
            'marca_blanca' => 'boolean',
        ];
    }

    // Relación de uno a uno que permite vincular los rangos de edad a la configuración de la iglesia.
    public function rangoEdad(): HasMany
    {
        return $this->hasMany(RangoEdad::class);
    }
}
