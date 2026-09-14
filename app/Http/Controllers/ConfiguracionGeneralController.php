<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateConfiguracionGeneralRequest;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ConfiguracionGeneralController extends Controller
{
    public function configuracionGeneral(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        abort_if($rolActivo === null, 403, 'No tienes los permisos necesarios.');
        $rolActivo->verificacionDelPermiso('configuraciones.subitem_general');

        $configuracion = Configuracion::firstOrFail();

        return view('contenido.paginas.configuracion-general.configuracion-general', [
            'configuracion' => $configuracion,
        ]);
    }

    public function actualizar(UpdateConfiguracionGeneralRequest $request): RedirectResponse
    {
        $configuracion = Configuracion::firstOrFail();
        $on = fn ($campo) => $request->input($campo) === 'on';
        $validated = $request->validated();

        $usaDiaCorte = $request->input('habilitarDiasCorte') === 'on';

        $datos = [
            // --- General ---
            'version' => $validated['version'],
            'limite_menor_edad' => $validated['LimiteMenorEdad'],
            'nombre_app_personalizado' => $validated['nombreAppPersonalizada'] ?? null,
            'label_seccion_campos_extra' => $validated['labelSeccionCamposExtra'] ?? null,
            'visible_seccion_campos_extra' => $on('visibleSeccionCamposExtra'),
            'visible_seccion_campos_extra_grupo' => $on('visibleSeccionCamposExtraGrupo'),
            'usa_listas_geograficas' => $on('usaListasGeograficas'),
            'direccion_obligatoria' => $on('direccionObligatoria'),

            // --- Grupos ---
            'dia_corte_reportes_grupos' => $usaDiaCorte ? ($request->input('diaCorteReporteGrupos') ?: null) : null,
            'dia_recordatorio_para_reporte_grupos' => $usaDiaCorte ? ($request->input('diaRecordatorioParaReporteGrupos') ?: null) : null,
            'hora_recordatorio_para_reporte_grupos' => $usaDiaCorte ? ($request->input('horaRecordatorioParaReporteGrupos') ?: null) : null,
            'dias_plazo_reporte_grupo' => ! $usaDiaCorte ? ($request->input('diaPlazoReporteGrupo') ?: null) : null,
            'reportar_grupo_cualquier_dia' => $on('reportarGrupoCualquierDia'),
            'sumar_encargado_asistencia_grupo' => $on('sumarEncargadoAsistenciaGrupo'),
            'maximos_niveles_grafico_ministerio' => $validated['maximosNivelesGraficoMinisterio'],
            'titulo_seccion_reunion_grupo' => $validated['tituloSeccionReunionGrupo'] ?? null,

            'habilitar_nombre_grupo' => $on('habilitarNombreGrupo'),
            'nombre_grupo_obligatorio' => $on('habilitarNombreGrupo') ? $on('nombreGrupoObligatorio') : false,

            'habilitar_tipo_grupo' => $on('habilitarTipoGrupo'),
            'tipo_grupo_obligatorio' => $on('habilitarTipoGrupo') ? $on('tipoGrupoObligatorio') : false,

            'habilitar_telefono_grupo' => $on('habilitarTelefonoGrupo'),
            'telefono_grupo_obligatorio' => $on('habilitarTelefonoGrupo') ? $on('telefonoGrupoObligatorio') : false,

            'habilitar_tipo_vivienda_grupo' => $on('habilitarTipoViviendaGrupo'),
            'tipo_vivienda_grupo_obligatorio' => $on('habilitarTipoViviendaGrupo') ? $on('tipoViviendaGrupoObligatorio') : false,

            'habilitar_hora_reunion_grupo' => $on('habilitarHoraReunionGrupo'),
            'label_campo_hora_reunion_grupo' => $on('habilitarHoraReunionGrupo') ? ($validated['labelCampoHoraReunionGrupo'] ?? null) : null,
            'hora_reunion_grupo_obligatorio' => $on('habilitarHoraReunionGrupo') ? $on('horaReunionGrupoObligatorio') : false,

            'habilitar_dia_reunion_grupo' => $on('habilitarDiaReunionGrupo'),
            'label_campo_dia_reunion_grupo' => $on('habilitarDiaReunionGrupo') ? ($validated['labelCampoDiaReunionGrupo'] ?? null) : null,
            'dia_reunion_grupo_obligatorio' => $on('habilitarDiaReunionGrupo') ? $on('diaReunionGrupoObligatorio') : false,

            'habilitar_direccion_grupo' => $on('habilitarDireccionGrupo'),
            'label_direccion_grupo' => $on('habilitarDireccionGrupo') ? ($validated['labelDireccionGrupo'] ?? null) : null,
            'direccion_grupo_obligatorio' => $on('habilitarDireccionGrupo') ? $on('direccionGrupoObligatorio') : false,

            'habilitar_fecha_creacion_grupo' => $on('habilitarFechaCreacionGrupo'),
            'label_fecha_creacion_grupo' => $on('habilitarFechaCreacionGrupo') ? ($validated['labelCreacionGrupo'] ?? null) : null,
            'fecha_creacion_grupo_obligatorio' => $on('habilitarFechaCreacionGrupo') ? $on('fechaCreacionGrupoObligatorio') : false,

            'habilitar_campo_opcional1_grupo' => $on('habilitarCampoOpcional1Grupo'),
            'label_campo_opcional1' => $on('habilitarCampoOpcional1Grupo') ? ($validated['labelCampoOpcional1'] ?? null) : null,
            'campo_opcional1_obligatorio' => $on('habilitarCampoOpcional1Grupo') ? $on('campoOpcional1Obligatorio') : false,

            // --- Usuarios ---
            'correo_por_defecto' => $on('correoPorDefecto'),
            'identificacion_obligatoria' => $on('identificacionObligatoria'),
            'identificacion_solo_numerica' => $on('identificacionSoloNumerica'),
            'tiempo_para_definir_inactivo_grupo' => $validated['tiempoParaDefinirInactivoGrupo'] ?? null,
            'tiempo_para_definir_inactivo_reunion' => $validated['tiempoParaDefinirInactivoReunion'] ?? null,
            'edad_minima_logueo' => $validated['edadMinimaLogueo'],
            'enviar_correo_bienvenida_nuevo_asistente' => $on('enviarCorreoBienvenidaNuevoAsistente'),
            'banner_mensaje_bienvenida' => $on('bannerMensajeBienvenida'),
            'titulo_mensaje_bienvenida' => $on('bannerMensajeBienvenida') ? ($validated['tituloMensajeBienvenida'] ?? null) : null,
            'mensaje_bienvenida' => $request->input('mensajeBienvenida'),

            // --- Informes ---
            'nombre_resaltador_informe_mensual_reportes_grupo' => $validated['nombreResaltadorInformeMensualReportesGrupo'],
            'valor_minimo_resaltador_informe_mensual_reportes_grupo' => $validated['valorMinimoResaltadorInformeMensualReportesGrupo'],
            'valor_maximo_resaltador_informe_mensual_reportes_grupo' => $validated['valorMaximoResaltadorInformeMensualReportesGrupo'],

            // --- Reuniones ---
            'label_invitado_reuniones' => $validated['labelInvitadoReuniones'] ?? null,
            'label_observacion_invitados_modal' => $validated['labelObservacionInvitadosModal'] ?? null,
            'text_default_observacion_invitados_modal' => $validated['textDefaultObservacionInvitadosModal'] ?? null,
            'habilitar_observacion_anadir_invitados_modal' => $on('habilitarObservacionAnadirInvitadosModal'),
            'habilitar_contador_anadir_invitados_modal' => $on('habilitarContadorAnadirInvitadosModal'),

            // --- Punto de pago ---
            'mensaje_correo_punto_pago' => $validated['mensajeCorreoPuntoPago'] ?? null,
            'moneda_predeterminada_punto_pago' => $validated['monedaPredeterminadaPuntoPago'] ?? null,

            // --- Ingresos y Egresos ---
            'label_campoadicional1_ingresos' => $validated['labelCampoadicional1Ingresos'],
            'label_campoadicional2_ingresos' => $validated['labelCampoadicional2Ingresos'],
            'label_campoadicional1_egresos' => $validated['labelCampoadicional1Egresos'],
            'label_campoadicional2_egresos' => $validated['labelCampoadicional2Egresos'],

            // --- Reportes de grupo ---
            'tiene_sistema_aprobacion_de_reporte' => $on('tieneSistemaAprobacionDeReporte'),

            // --- Escuelas ---
            'opciones_extra_matriculas_escuelas' => $on('opcionesExtraMatriculasEscuelas'),
            'opcion_material_sede' => $on('opcionMaterialSede'),
            'habilitar_salones_con_estaciones' => $on('habilitarSalonesConEstaciones'),
            'items_mixtos_escuelas_deshabilitados' => $on('itemsMixtosEscuelasDeshabilitados'),
            'cierre_cortes_habilitado' => $on('cierreCortesHabilitado'),
            'habilitar_traslados' => $on('habilitarTraslados'),
            'cantidad_intentos_traslados' => $validated['cantidadIntentosTraslados'],
            'espacio_academico_habilitado' => $on('espacioAcademicoHabilitado'),
            'envio_material' => $on('envioMaterial'),
            'cantidad_dias_alerta_notas_maestro' => $validated['cantidadDiasAlertaNotasMaestro'] ?? null,
            'cantidad_intentos_auto_matricula' => $validated['cantidadIntentosAutoMatricula'] ?? null,
            'dias_plazo_maximo_actualizacion_automatricula' => $validated['diasPlazoMaximoActualizacionAutomatricula'] ?? null,
            'mensaje_exito_auto_matricula' => $validated['mensajeExitoAutoMatricula'] ?? null,
            'mensaje_error_auto_matricula' => $validated['mensajeErrorAutoMatricula'] ?? null,
            'mensaje_existe_auto_matricula' => $validated['mensajeExisteAutoMatricula'] ?? null,

            'edad_minima_consolidacion' => $validated['edadMinimaConsolidacion'],

            // --- Informes Evidencias Grupo ---
            'habilitar_campo_1_informe_evidencias_grupo' => $on('habilitarCampo1InformeEvidenciasGrupo'),
            'label_campo_1_informe_evidencias_grupo' => $validated['labelCampo1InformeEvidenciasGrupo'] ?? null,
            'campo_1_informe_evidencias_grupo_obligatorio' => $on('campo1InformeEvidenciasGrupoObligatorio'),

            'habilitar_campo_2_informe_evidencias_grupo' => $on('habilitarCampo2InformeEvidenciasGrupo'),
            'label_campo_2_informe_evidencias_grupo' => $validated['labelCampo2InformeEvidenciasGrupo'] ?? null,
            'campo_2_informe_evidencias_grupo_obligatorio' => $on('campo2InformeEvidenciasGrupoObligatorio'),

            'habilitar_campo_3_informe_evidencias_grupo' => $on('habilitarCampo3InformeEvidenciasGrupo'),
            'label_campo_3_informe_evidencias_grupo' => $validated['labelCampo3InformeEvidenciasGrupo'] ?? null,
            'campo_3_informe_evidencias_grupo_obligatorio' => $on('campo3InformeEvidenciasGrupoObligatorio'),

        ];

        if (! empty($datos['dia_corte_reportes_grupos'])) {
            $datos['dias_plazo_reporte_grupo'] = null;
        }

        $configuracion->update($datos);
        Cache::forget('configuracion_global');

        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }
}
