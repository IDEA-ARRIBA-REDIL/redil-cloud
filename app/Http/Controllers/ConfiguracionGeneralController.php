<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionGeneralController extends Controller
{
  // Muestra la vista principal con toda la configuración
  public function configuracionGeneral()
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('configuraciones.subitem_general');

    // Usamos siempre la primera configuración
    $configuracion = Configuracion::first();
    return view('contenido.paginas.configuracion-general.configuracion-general', [
      'configuracion' => $configuracion
    ]);
  }

  // Actualiza solo los datos enviados desde el modal (AJAX o un form parcial)
  public function actualizar(Request $request)
  {
    // return $request;
    $configuracion = Configuracion::firstOrFail();
    $on = fn($campo) => $request->input($campo) === 'on';

    // Validamos los campos básicos
    $validated = $request->validate([
      // General
      'version' => 'required|numeric',
      'LimiteMenorEdad' => 'required|integer',
      'nombreAppPersonalizada' => 'nullable|string|max:255',
      'rutaAlmacenamiento' => 'nullable|string',
      'labelSeccionCamposExtra' => 'nullable|string|max:100',
      'seccionCamposExtra' => 'nullable|numeric', // Asumo que este campo existe aunque no se procesa abajo

      // Grupos
      'maximosNivelesGraficoMinisterio' => 'required|integer',
      'tituloSeccionReunionGrupo' => 'nullable|string|max:50',
      'labelCampoHoraReunionGrupo' => 'nullable|string|max:50',
      'labelCampoDiaReunionGrupo' => 'nullable|string|max:50',
      'labelDireccionGrupo' => 'nullable|string|max:100',
      'labelCreacionGrupo' => 'nullable|string|max:100',
      'labelCampoOpcional1' => 'nullable|string|max:50',

      // Informe Evidencias Grupo
      'labelCampo1InformeEvidenciasGrupo' => 'nullable|string|max:50',
      'labelCampo2InformeEvidenciasGrupo' => 'nullable|string|max:50',
      'labelCampo3InformeEvidenciasGrupo' => 'nullable|string|max:50',

      // Usuario
      'tiempoParaDefinirInactivoGrupo' => 'nullable|integer',
      'tiempoParaDefinirInactivoReunion' => 'nullable|integer',
      'edadMinimaLogueo' => 'required|integer',
      'tituloMensajeBienvenida' => 'nullable|string|max:100',
      'mensajeBienvenida' => 'nullable|string', // Para el editor Quill

      // Informes
      'nombreResaltadorInformeMensualReportesGrupo' => 'required|string|max:50',
      'valorMinimoResaltadorInformeMensualReportesGrupo' => 'required|integer',
      'valorMaximoResaltadorInformeMensualReportesGrupo' => 'required|integer',

      // Reuniones
      'labelInvitadoReuniones' => 'nullable|string|max:50',
      'labelObservacionInvitadosModal' => 'nullable|string',
      'textDefaultObservacionInvitadosModal' => 'nullable|string',

      // Punto de Pago
      'mensajeCorreoPuntoPago' => 'nullable|string',
      'monedaPredeterminadaPuntoPago' => 'nullable|integer',

      // Ingresos y Egresos
      'labelCampoadicional1Ingresos' => 'required|string|max:50',
      'labelCampoadicional2Ingresos' => 'required|string|max:50',
      'labelCampoadicional1Egresos' => 'required|string|max:50',
      'labelCampoadicional2Egresos' => 'required|string|max:50',

      // Escuelas
      'cantidadDiasAlertaNotasMaestro' => 'nullable|integer',
      'cantidadIntentosAutoMatricula' => 'nullable|integer',
      'diasPlazoMaximoActualizacionAutomatricula' => 'nullable|integer',
      'mensajeExitoAutoMatricula' => 'nullable|string',
      'mensajeErrorAutoMatricula' => 'nullable|string',
      'mensajeExisteAutoMatricula' => 'nullable|string',

      // Consolidación
      'edadMinimaConsolidacion' => 'required|integer',

      // Informes Evidencias Grupo
      'labelCampo1InformeEvidenciasGrupo' => 'nullable|string|max:50',
      'labelCampo2InformeEvidenciasGrupo' => 'nullable|string|max:50',
      'labelCampo3InformeEvidenciasGrupo' => 'nullable|string|max:50',
    ],  [
      // Mensajes personalizados

      'version.required' => 'La versión del sistema es obligatoria.',
      'version.numeric' => 'La versión debe ser un número.',

      'LimiteMenorEdad.required' => 'Debe especificar un límite mínimo de edad.',
      'LimiteMenorEdad.integer' => 'El límite de edad debe ser un número entero.',

      'nombreAppPersonalizada.max' => 'El nombre personalizado de la app no debe superar los 255 caracteres.',

      'labelDireccionGrupo.max' => 'La etiqueta para la dirección del grupo no debe superar los 100 caracteres.',

      'maximosNivelesGraficoMinisterio.required' => 'Debe indicar los niveles máximos del gráfico del ministerio.',
      'maximosNivelesGraficoMinisterio.integer' => 'Los niveles máximos deben ser un número entero.',

      'edadMinimaLogueo.required' => 'Debe indicar una edad mínima para el inicio de sesión.',
      'edadMinimaLogueo.integer' => 'La edad mínima debe ser un número entero.',

      'labelCampoadicional1Ingresos.required' => 'Debe ingresar el nombre del campo adicional 1 en ingresos.',
      'labelCampoadicional2Ingresos.required' => 'Debe ingresar el nombre del campo adicional 2 en ingresos.',
      'labelCampoadicional1Egresos.required' => 'Debe ingresar el nombre del campo adicional 1 en egresos.',
      'labelCampoadicional2Egresos.required' => 'Debe ingresar el nombre del campo adicional 2 en egresos.',

      'nombreResaltadorInformeMensualReportesGrupo.required' => 'Debe indicar el nombre del resaltador para los informes mensuales.',
      'valorMinimoResaltadorInformeMensualReportesGrupo.required' => 'Debe indicar el valor mínimo del resaltador.',
      'valorMaximoResaltadorInformeMensualReportesGrupo.required' => 'Debe indicar el valor máximo del resaltador.',

      'edadMinimaConsolidacion.required' => 'Debe especificar un límite mínimo de edad.',
      'edadMinimaConsolidacion.integer' => 'El límite de edad debe ser un número entero.',
    ]);

    // Función que convierte "on" en true y todo lo demás en false
    $esVerdadero = fn($campo) => $request->input($campo) === 'on';

    $usaDiaCorte = $request->input('habilitarDiasCorte') === 'on';

    if ($usaDiaCorte) {
      $request->validate([
        'diaCorteReporteGrupos' => 'required',
        'diaRecordatorioParaReporteGrupos' => 'nullable',
        'horaRecordatorioParaReporteGrupos' => 'nullable',
      ], [
        'diaCorteReporteGrupos' => 'Día corte reporte grupo es un campo obligatorio'
      ]);
    } else {
      $request->validate([
        'diaPlazoReporteGrupo' => 'required'
      ], [
        'diaPlazoReporteGrupo' => 'Día plazo reporte grupo es un campo obligatorio'
      ]);
    }

    $datos = [
      // --- General ---
      'version' => $validated['version'],
      'limite_menor_edad' => $validated['LimiteMenorEdad'],
      'nombre_app_personalizado' => $validated['nombreAppPersonalizada'],
      'ruta_almacenamiento' => $validated['rutaAlmacenamiento'],
      'label_seccion_campos_extra' => $validated['labelSeccionCamposExtra'],
      'visible_seccion_campos_extra' => $on('visibleSeccionCamposExtra'),
      'visible_seccion_campos_extra_grupo' => $on('visibleSeccionCamposExtraGrupo'),
      'logo_personalizado' => $on('logoPersonalizado'),
      'usa_listas_geograficas' => $on('usaListasGeograficas'),
      'direccion_obligatoria' => $on('direccionObligatoria'),

      // --- Grupos ---
      'dia_corte_reportes_grupos' => $usaDiaCorte ? ($request->input('diaCorteReporteGrupos') ?: null) : null,
      'dia_recordatorio_para_reporte_grupos' => $usaDiaCorte ? ($request->input('diaRecordatorioParaReporteGrupos') ?: null) : null,
      'hora_recordatorio_para_reporte_grupos' => $usaDiaCorte ? ($request->input('horaRecordatorioParaReporteGrupos') ?: null) : null,
      'dias_plazo_reporte_grupo' => !$usaDiaCorte ? ($request->input('diaPlazoReporteGrupo') ?: null) : null,
      'reportar_grupo_cualquier_dia' => $on('reportarGrupoCualquierDia'),
      'sumar_encargado_asistencia_grupo' => $on('sumarEncargadoAsistenciaGrupo'),
      'maximos_niveles_grafico_ministerio' => $validated['maximosNivelesGraficoMinisterio'],
      'titulo_seccion_reunion_grupo' => $validated['tituloSeccionReunionGrupo'],

      'habilitar_nombre_grupo' => $on('habilitarNombreGrupo'),
      'nombre_grupo_obligatorio' => $on('habilitarNombreGrupo') ? $on('nombreGrupoObligatorio') : false,

      'habilitar_tipo_grupo' => $on('habilitarTipoGrupo'),
      'tipo_grupo_obligatorio' => $on('habilitarTipoGrupo') ? $on('tipoGrupoObligatorio') : false,

      'habilitar_telefono_grupo' => $on('habilitarTelefonoGrupo'),
      'telefono_grupo_obligatorio' => $on('habilitarTelefonoGrupo') ? $on('telefonoGrupoObligatorio') : false,

      'habilitar_tipo_vivienda_grupo' => $on('habilitarTipoViviendaGrupo'),
      'tipo_vivienda_grupo_obligatorio' => $on('habilitarTipoViviendaGrupo') ? $on('tipoViviendaGrupoObligatorio') : false,

      'habilitar_hora_reunion_grupo' => $on('habilitarHoraReunionGrupo'),
      'label_campo_hora_reunion_grupo' => $on('habilitarHoraReunionGrupo') ? $validated['labelCampoHoraReunionGrupo'] : null,
      'hora_reunion_grupo_obligatorio' => $on('habilitarHoraReunionGrupo') ? $on('horaReunionGrupoObligatorio') : false,

      'habilitar_dia_reunion_grupo' => $on('habilitarDiaReunionGrupo'),
      'label_campo_dia_reunion_grupo' => $on('habilitarDiaReunionGrupo') ? $validated['labelCampoDiaReunionGrupo'] : null,
      'dia_reunion_grupo_obligatorio' => $on('habilitarDiaReunionGrupo') ? $on('diaReunionGrupoObligatorio') : false,

      'habilitar_direccion_grupo' => $on('habilitarDireccionGrupo'),
      'label_direccion_grupo' => $on('habilitarDireccionGrupo') ? $validated['labelDireccionGrupo'] : null,
      'direccion_grupo_obligatorio' => $on('habilitarDireccionGrupo') ? $on('direccionGrupoObligatorio') : false,

      'habilitar_fecha_creacion_grupo' => $on('habilitarFechaCreacionGrupo'),
      'label_fecha_creacion_grupo' => $on('habilitarFechaCreacionGrupo') ? $validated['labelCreacionGrupo'] : null,
      'fecha_creacion_grupo_obligatorio' => $on('habilitarFechaCreacionGrupo') ? $on('fechaCreacionGrupoObligatorio') : false,

      'habilitar_campo_opcional1_grupo' => $on('habilitarCampoOpcional1Grupo'),
      'label_campo_opcional1' => $on('habilitarCampoOpcional1Grupo') ? $validated['labelCampoOpcional1'] : null,
      'campo_opcional1_obligatorio' => $on('habilitarCampoOpcional1Grupo') ? $on('campoOpcional1Obligatorio') : false,

      // --- Usuarios ---
      'correo_por_defecto' => $on('correoPorDefecto'),
      'identificacion_obligatoria' => $on('identificacionObligatoria'),
      'identificacion_solo_numerica' => $on('identificacionSoloNumerica'),
      'tiempo_para_definir_inactivo_grupo' => $validated['tiempoParaDefinirInactivoGrupo'],
      'tiempo_para_definir_inactivo_reunion' => $validated['tiempoParaDefinirInactivoReunion'],
      'edad_minima_logueo' => $validated['edadMinimaLogueo'],
      'enviar_correo_bienvenida_nuevo_asistente' => $on('enviarCorreoBienvenidaNuevoAsistente'),
      'banner_mensaje_bienvenida' => $on('bannerMensajeBienvenida'),
      'titulo_mensaje_bienvenida' => $on('bannerMensajeBienvenida') ? $validated['tituloMensajeBienvenida'] : null,
      'mensaje_bienvenida' => $validated['mensajeBienvenida'],

      // --- Informes ---
      'nombre_resaltador_informe_mensual_reportes_grupo' => $validated['nombreResaltadorInformeMensualReportesGrupo'],
      'valor_minimo_resaltador_informe_mensual_reportes_grupo' => $validated['valorMinimoResaltadorInformeMensualReportesGrupo'],
      'valor_maximo_resaltador_informe_mensual_reportes_grupo' => $validated['valorMaximoResaltadorInformeMensualReportesGrupo'],

      // --- Reuniones ---
      'label_invitado_reuniones' => $validated['labelInvitadoReuniones'],
      'label_observacion_invitados_modal' => $validated['labelObservacionInvitadosModal'],
      'text_default_observacion_invitados_modal' => $validated['textDefaultObservacionInvitadosModal'],
      'habilitar_observacion_anadir_invitados_modal' => $on('habilitarObservacionAnadirInvitadosModal'),
      'habilitar_contador_anadir_invitados_modal' => $on('habilitarContadorAnadirInvitadosModal'),

      // --- Punto de pago ---
      'mensaje_correo_punto_pago' => $validated['mensajeCorreoPuntoPago'],
      'moneda_predeterminada_punto_pago' => $validated['monedaPredeterminadaPuntoPago'],

      // --- Ingresos y Egresos ---
      'label_campoadicional1_ingresos' => $validated['labelCampoadicional1Ingresos'],
      'label_campoadicional2_ingresos' => $validated['labelCampoadicional2Ingresos'],
      'label_campoadicional1_egresos' => $validated['labelCampoadicional1Egresos'],
      'label_campoadicional2_egresos' => $validated['labelCampoadicional2Egresos'],

      // --- Reportes de grupo ---
      'tiene_sistema_aprobacion_de_reporte' => $on('tieneSistemaAprobacionDeReporte'),

      // --- Escuelas ---
      'opciones_extra_matriculas_escuelas' => $on('opcionesExtraMatriculasEscuelas'),
      'habilitar_salones_con_estaciones' => $on('habilitarSalonesConEstaciones'),
      'items_mixtos_escuelas_deshabilitados' => $on('itemsMixtosEscuelasDeshabilitados'),
      'cierre_cortes_habilitado' => $on('cierreCortesHabilitado'),
      'habilitar_traslados' => $on('habilitarTraslados'),
      'espacio_academico_habilitado' => $on('espacioAcademicoHabilitado'),
      'cantidad_dias_alerta_notas_maestro' => $validated['cantidadDiasAlertaNotasMaestro'],
      'cantidad_intentos_auto_matricula' => $validated['cantidadIntentosAutoMatricula'],
      'dias_plazo_maximo_actualizacion_automatricula' => $validated['diasPlazoMaximoActualizacionAutomatricula'],
      'mensaje_exito_auto_matricula' => $validated['mensajeExitoAutoMatricula'],
      'mensaje_error_auto_matricula' => $validated['mensajeErrorAutoMatricula'],
      'mensaje_existe_auto_matricula' => $validated['mensajeExisteAutoMatricula'],

      'edad_minima_consolidacion' => $validated['edadMinimaConsolidacion'],

      // --- Informes Evidencias Grupo ---
      'habilitar_campo_1_informe_evidencias_grupo' => $on('habilitarCampo1InformeEvidenciasGrupo'),
      'label_campo_1_informe_evidencias_grupo' => $validated['labelCampo1InformeEvidenciasGrupo'],
      'campo_1_informe_evidencias_grupo_obligatorio' => $on('campo1InformeEvidenciasGrupoObligatorio'),

      'habilitar_campo_2_informe_evidencias_grupo' => $on('habilitarCampo2InformeEvidenciasGrupo'),
      'label_campo_2_informe_evidencias_grupo' => $validated['labelCampo2InformeEvidenciasGrupo'],
      'campo_2_informe_evidencias_grupo_obligatorio' => $on('campo2InformeEvidenciasGrupoObligatorio'),

      'habilitar_campo_3_informe_evidencias_grupo' => $on('habilitarCampo3InformeEvidenciasGrupo'),
      'label_campo_3_informe_evidencias_grupo' => $validated['labelCampo3InformeEvidenciasGrupo'],
      'campo_3_informe_evidencias_grupo_obligatorio' => $on('campo3InformeEvidenciasGrupoObligatorio'),
    
    ];

    // Si se usa día de corte, ignoramos plazo de días (por lógica de negocio)
    if (!empty($datos['dia_corte_reportes_grupos'])) {
      $datos['dias_plazo_reporte_grupo'] = null;
    }

    // return $datos;

    $configuracion->update($datos);

    return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
  }
}
