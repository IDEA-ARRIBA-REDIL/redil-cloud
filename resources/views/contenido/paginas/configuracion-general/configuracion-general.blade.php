@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Inicio')


@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/editor.scss'
])
@endsection

@section('head')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/quill/quill.js'
])
@endsection

@push('scripts')
<script type="module">
  const editor = new Quill('#editor', {
    bounds: '#editor',
    placeholder: 'Escribe aquí la respuesta de la persona',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{
          'header': 1
        }, {
          'header': 2
        }],
        [{
          'color': []
        }, {
          'background': []
        }],
        [{
          'align': []
        }],
        [{
          'size': ['small', false, 'large', 'huge']
        }],
        [{
          'header': [1, 2, 3, 4, 5, 6, false]
        }],
        [{
          'font': []
        }],
        [{
          'list': 'ordered'
        }, {
          'list': 'bullet'
        }, {
          'list': 'check'
        }],
        [{
          'indent': '-1'
        }, {
          'indent': '+1'
        }],
        ['link', 'image', 'video'],
        ['clean']
      ],
      imageResize: {
        modules: ['Resize', 'DisplaySize']
      },
    },
    theme: 'snow'
  });

  // Cargar contenido HTML si ya existe (old o configuración previa)
  editor.root.innerHTML = `{!! old('mensajeBienvenida', $configuracion->mensaje_bienvenida) !!}`;

  // Escuchar cambios y actualizar el input hidden
  editor.on('text-change', () => {
    document.getElementById('mensajeBienvenida').value = editor.root.innerHTML;
  });

  // Cargar el valor inicial también al principio
  document.getElementById('mensajeBienvenida').value = editor.root.innerHTML;
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const successMessage = "{{ session('success') }}";
    const errorMessage = "{{ session('error') }}";

    const diaCorte = document.getElementById('dia_corte_reporte_grupos');
    const diaPlazo = document.getElementById('dia_plazo_reporte_grupo');

    const switchDiasCorte = document.getElementById('habilitar_dias_corte');

    const contenedorCorte = document.getElementById('contenedor-corte');
    const contenedorPlazo = document.getElementById('contenedor-plazo');
    const contenedorRecordatorioDia = document.getElementById('contenedor-recordatorio-dia');
    const contenedorRecordatorioHora = document.getElementById('contenedor-recordatorio-hora');
    const selectDiaCorte = document.querySelector('#dia_corte_reporte_grupos');
    const selectDiaPlazo = document.querySelector('#dia_plazo_reporte_grupo');
    const switchHabilitarNombreGrupo = document.getElementById('habilitar_nombre_grupo');
    const switchHabilitarTipoGrupo = document.getElementById('habilitar_tipo_grupo');
    const switchHabilitarTelefonoGrupo = document.getElementById('habilitar_telefono_grupo');
    const switchHabilitarTipoViviendaGrupo = document.getElementById('habilitar_tipo_vivienda_grupo');
    const switchHabilitarHoraReunionGrupo = document.getElementById('habilitar_hora_reunion_grupo');
    const switchHabilitarDiaReunionGrupo = document.getElementById('habilitar_dia_reunion_grupo');
    const switchHabilitarDireccionGrupo = document.getElementById('habilitar_direccion_grupo');
    const switchHabilitarFechaCreacionGrupo = document.getElementById('habilitar_fecha_creacion_grupo');
    const switchHabilitarCampoAdicional1Grupo = document.getElementById('habilitar_campo_opcional1_grupo');

    // --- VARIABLES INFORME EVIDENCIAS GRUPO ---
    // Switches de habilitar
    const switchHabCampo1 = document.getElementById('habilitarCampo1InformeEvidenciasGrupo');
    const switchHabCampo2 = document.getElementById('habilitarCampo2InformeEvidenciasGrupo');
    const switchHabCampo3 = document.getElementById('habilitarCampo3InformeEvidenciasGrupo');
    
    // Contenedores a ocultar/mostrar
    // Campo 1
    const contLabel1 = document.getElementById('contenedor_labelCampo1InformeEvidenciasGrupo');
    const contOblig1 = document.getElementById('contenedor_campo1InformeEvidenciasGrupoObligatorio');
    // Campo 2
    const contLabel2 = document.getElementById('contenedor_labelCampo2InformeEvidenciasGrupo');
    const contOblig2 = document.getElementById('contenedor_campo2InformeEvidenciasGrupoObligatorio');
    // Campo 3
    const contLabel3 = document.getElementById('contenedor_labelCampo3InformeEvidenciasGrupo');
    const contOblig3 = document.getElementById('contenedor_campo3InformeEvidenciasGrupoObligatorio');

    if (successMessage) {
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: successMessage,
        timer: 3000,
        showConfirmButton: false,
        timerProgressBar: true
      });
    }

    if (errorMessage) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMessage,
        timer: 3000,
        showConfirmButton: false,
        timerProgressBar: true
      });
    }

    function toggleCampo(condicion, elemento) {
      if (!elemento) return;
      if (condicion) {
        elemento.classList.remove('d-none');
      } else {
        elemento.classList.add('d-none');
      }
    }

    function actualizarVisibilidadCampos() {
      toggleCampo(switchDiasCorte.checked, contenedorCorte);
      toggleCampo(switchDiasCorte.checked, contenedorRecordatorioDia);
      toggleCampo(switchDiasCorte.checked, contenedorRecordatorioHora);
      toggleCampo(!switchDiasCorte.checked, contenedorPlazo);

      toggleCampo(switchHabilitarNombreGrupo?.checked, document.getElementById('contenedor_nombre_grupo_obligatorio'));

      toggleCampo(switchHabilitarTipoGrupo?.checked, document.getElementById('contenedor_tipo_grupo_obligatorio'));

      toggleCampo(switchHabilitarTelefonoGrupo?.checked, document.getElementById('contenedor_telefono_grupo_obligatorio'));

      toggleCampo(switchHabilitarTipoViviendaGrupo?.checked, document.getElementById('contenedor_tipo_vivienda_grupo_obligatorio'));

      toggleCampo(switchHabilitarHoraReunionGrupo?.checked, document.getElementById('contenedor_label_campo_hora_reunion_grupo'));
      toggleCampo(switchHabilitarHoraReunionGrupo?.checked, document.getElementById('contenedor_hora_reunion_grupo_obligatorio'));

      toggleCampo(switchHabilitarDiaReunionGrupo?.checked, document.getElementById('contenedor_label_campo_dia_reunion_grupo'));
      toggleCampo(switchHabilitarDiaReunionGrupo?.checked, document.getElementById('contenedor_dia_reunion_grupo_obligatorio'));

      toggleCampo(switchHabilitarDireccionGrupo?.checked, document.getElementById('contenedor_label_direccion_grupo'));
      toggleCampo(switchHabilitarDireccionGrupo?.checked, document.getElementById('contenedor_direccion_grupo_obligatorio'));

      toggleCampo(switchHabilitarFechaCreacionGrupo?.checked, document.getElementById('contenedor_label_fecha_creacion_grupo'));
      toggleCampo(switchHabilitarFechaCreacionGrupo?.checked, document.getElementById('contenedor_fecha_creacion_grupo_obligatorio'));

      toggleCampo(switchHabilitarCampoAdicional1Grupo?.checked, document.getElementById('contenedor_label_campo_opcional1'));
      toggleCampo(switchHabilitarCampoAdicional1Grupo?.checked, document.getElementById('contenedor_campo_opcional1_obligatorio'));

      // --- LOGICA INFORME EVIDENCIAS ---
      toggleCampo(switchHabCampo1?.checked, contLabel1);
      toggleCampo(switchHabCampo1?.checked, contOblig1);

      toggleCampo(switchHabCampo2?.checked, contLabel2);
      toggleCampo(switchHabCampo2?.checked, contOblig2);

      toggleCampo(switchHabCampo3?.checked, contLabel3);
      toggleCampo(switchHabCampo3?.checked, contOblig3);
    }

    // Re-evaluar al cambiar el switch
    switchDiasCorte.addEventListener('change', actualizarVisibilidadCampos);
    diaCorte.addEventListener('change', actualizarVisibilidadCampos);
    diaPlazo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarNombreGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarTipoGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarTelefonoGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarTipoViviendaGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarHoraReunionGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarDiaReunionGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarDireccionGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarFechaCreacionGrupo.addEventListener('change', actualizarVisibilidadCampos);
    switchHabilitarCampoAdicional1Grupo.addEventListener('change', actualizarVisibilidadCampos);

    switchHabCampo1?.addEventListener('change', actualizarVisibilidadCampos);
    switchHabCampo2?.addEventListener('change', actualizarVisibilidadCampos);
    switchHabCampo3?.addEventListener('change', actualizarVisibilidadCampos);

    actualizarVisibilidadCampos();
  });
</script>
@endpush

@section('content')
@include('layouts.status-msn')

<h4 class="mb-1 fw-semibold text-primary">Configuraciones generales </h4>

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('configuracion-general.actualizar', $configuracion) }}"
  enctype="multipart/form-data">
  @csrf
  @method('PATCH')
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Información general</h5>

        <div class="card-body">
          <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <label for="html5-time-input" class="form-label">Versión</label>
              <input class="form-control" name="version" type="number" value="{{$configuracion->version}}" id="html5-time-input" />´
              @error('version')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <label for="html5-time-input" class="form-label">Limite menor edad</label>
              <input class="form-control" name="LimiteMenorEdad" type="number" value="{{$configuracion->limite_menor_edad}}" id="html5-time-input" />
              @error('LimiteMenorEdad')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6 col-sm-6 col-12 mb-3">
              <label for="html5-time-input" class="form-label">Nombre app personalizada</label>
              <input class="form-control" name="nombreAppPersonalizada" type="text" value="{{$configuracion->nombre_app_personalizado}}" id="html5-time-input" />
            </div>
            <div class="col-md-6 col-sm-6 col-12 mb-3">
              <label for="html5-time-input" class="form-label">Ruta almacenamiento</label>
              <input class="form-control" name="rutaAlmacenamiento" type="text" value="{{$configuracion->ruta_almacenamiento}}" id="html5-time-input" />
            </div>

            <div class="col-md-6 col-sm-6 col-12 mb-3">
              <label for="html5-time-input" class="form-label">Label seccion campos extra</label>
              <input class="form-control" name="labelSeccionCamposExtra" type="text" value="{{$configuracion->label_seccion_campos_extra}}" id="html5-time-input" />
            </div>
            <div class="col-md-6 col-sm-6 col-12 mb-3 ">
              <label for="html5-time-input" class="form-label">Seccion campos extra</label>
              <input class="form-control" name="seccionCamposExtra" type="number" id="html5-time-input" />
            </div>
            <div class="col-md-6 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Sección visible campos extra grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->visible_seccion_campos_extra_grupo) class="switch-input" id="toggleListasGeograficas" name="visibleSeccionCamposExtraGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Sección visible campos extra?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->visible_seccion_campos_extra) class="switch-input" id="toggleListasGeograficas" name="visibleSeccionCamposExtra" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Usa listas geograficas?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->usa_listas_geograficas) class="switch-input" id="toggleListasGeograficas" name="usaListasGeograficas" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Logo personalizado?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->logo_personalizado) class="switch-input" id="toggleListasGeograficas" name="logoPersonalizado" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Dirección obligatoria?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->direccion_obligatoria) class="switch-input" id="toggleListasGeograficas" name="direccionObligatoria" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Grupos</h5>

        <div class="card-body">
          <div class="row">

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar días corte?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked(!is_null($configuracion->dia_corte_reportes_grupos)) class="switch-input" id="habilitar_dias_corte" name="habilitarDiasCorte" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div id="contenedor-corte" class="mb-3 col-12 col-md-4">
              <label class="form-label" for="dia_corte_reporte_grupos">
                Día corte reportes grupos
              </label>
              <select id="dia_corte_reporte_grupos" name="diaCorteReporteGrupos"
                class="select2 form-select" data-allow-clear="true">
                <option value="" selected>Ninguno</option>
                @foreach (Helper::diasDeLaSemana() as $dia)
                <option value="{{$dia->id}}"
                  {{ old('diaCorteReporteGrupos', $configuracion->dia_corte_reportes_grupos ) == $dia->id ? 'selected' : '' }}>
                  {{$dia->nombre}}
                </option>
                @endforeach
              </select>
              @error('diaCorteReporteGrupos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div id="contenedor-recordatorio-dia" class="mb-3 col-12 col-md-4">
              <label class="form-label" for="dia_recordatorio_para_reporte_grupos">
                Día recordatorio reporte
              </label>
              <select id="dia_recordatorio_para_reporte_grupos" name="diaRecordatorioParaReporteGrupos"
                class="select2 form-select" data-allow-clear="true">
                <option value="" selected>Ninguno</option>
                @foreach (Helper::diasDeLaSemana() as $dia)
                <option value="{{$dia->id}}"
                  {{ old('diaRecordatorioParaReporteGrupos', $configuracion->dia_recordatorio_para_reporte_grupos ) == $dia->id ? 'selected' : '' }}>
                  {{$dia->nombre}}
                </option>
                @endforeach
              </select>
              @error('diaRecordatorioParaReporteGrupos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div id="contenedor-recordatorio-hora" class="mb-3 col-12 col-md-4">
              <label class="form-label" for="hora_recordatorio_para_reporte_grupos">
                Hora recordatorio reporte
              </label>
              <select class="form-select select2" name="horaRecordatorioParaReporteGrupos" id="hora_recordatorio_para_reporte_grupos">
                <option value="">Seleccione una hora</option>
                @for($h = 0; $h < 24; $h++)
                  @foreach(['00', '30'] as $m)
                    @php 
                      $hora = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $m; 
                      // Formato para comparar con el valor de la BD (que puede venir como HH:MM:SS)
                      $valorDb = $configuracion->hora_recordatorio_para_reporte_grupos ? substr($configuracion->hora_recordatorio_para_reporte_grupos, 0, 5) : '';
                    @endphp
                    <option value="{{ $hora }}" {{ $valorDb == $hora ? 'selected' : '' }}>
                      {{ $hora }}
                    </option>
                  @endforeach
                @endfor
              </select>
              @error('horaRecordatorioParaReporteGrupos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div id="contenedor-plazo" class="mb-3 col-12 col-md-4">
              <label class="form-label" for="dia_plazo_reporte_grupo">
                Día plazo reporte grupo
              </label>
              <input class="form-control" name="diaPlazoReporteGrupo" type="numeric" value="{{$configuracion->dias_plazo_reporte_grupo}}" id="dia_plazo_reporte_grupo" />
              @error('diaPlazoReporteGrupo')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Reportar grupo en cualquier día?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->reportar_grupo_cualquier_dia) class="switch-input" id="reportar_grupo_cualquier_dia" name="reportarGrupoCualquierDia" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <!--  -->
            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Sumar encargado asistencia grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->sumar_encargado_asistencia_grupo) class="switch-input" id="sumar_encargado_asistencia_grupo" name="sumarEncargadoAsistenciaGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3">
              <label for="html5-time-input" class="form-label">Maximos niveles gráfico ministerio</label>
              <input class="form-control" name="maximosNivelesGraficoMinisterio" type="text" value="{{$configuracion->maximos_niveles_grafico_ministerio}}" id="maximos_niveles_grafico_ministerio" />
              @error('maximosNivelesGraficoMinisterio')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <!-- -->
            <div class="col-12 col-md-4 mb-3">
              <label for="html5-time-input" class="form-label">Titulo sección reunion grupo</label>
              <input class="form-control" name="tituloSeccionReunionGrupo" type="text" value="{{$configuracion->titulo_seccion_reunion_grupo}}" id="titulo_seccion_reunion_grupo" />
            </div>
            <!-- -->
            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Habilitar nombre grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_nombre_grupo) class="switch-input" id="habilitar_nombre_grupo" name="habilitarNombreGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3" id="contenedor_nombre_grupo_obligatorio">
              <div class="form-label">¿Nombre de grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->nombre_grupo_obligatorio) class="switch-input" id="nombre_grupo_obligatorio" name="nombreGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Habilitar tipo grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_tipo_grupo) class="switch-input" id="habilitar_tipo_grupo" name="habilitarTipoGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3" id="contenedor_tipo_grupo_obligatorio">
              <div class="form-label">¿Tipo grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->tipo_grupo_obligatorio) class="switch-input" id="tipo_grupo_obligatorio" name="tipoGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Habilitar telefono grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_telefono_grupo) class="switch-input" id="habilitar_telefono_grupo" name="habilitarTelefonoGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3" id="contenedor_telefono_grupo_obligatorio">
              <div class="form-label">¿Telefono grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->telefono_grupo_obligatorio) class="switch-input" id="telefono_grupo_obligatorio" name="telefonoGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Habilitar tipo vivienda grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_tipo_vivienda_grupo) class="switch-input" id="habilitar_tipo_vivienda_grupo" name="habilitarTipoViviendaGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3" id="contenedor_tipo_vivienda_grupo_obligatorio">
              <div class="form-label">¿Tipo vivienda grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->tipo_vivienda_grupo_obligatorio) class="switch-input" id="tipo_vivienda_grupo_obligatorio" name="tipoViviendaGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Habilitar hora reunion grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_hora_reunion_grupo) class="switch-input" id="habilitar_hora_reunion_grupo" name="habilitarHoraReunionGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-12 col-md-4 mb-3" id="contenedor_label_campo_hora_reunion_grupo">
              <label for="html5-time-input" class="form-label">Label campo hora reunion grupo</label>
              <input class="form-control" name="labelCampoHoraReunionGrupo" type="text" id="label_hora_reunion_grupo" />
            </div>

            <div class="col-12 col-md-4 mb-3" id="contenedor_hora_reunion_grupo_obligatorio">
              <div class="form-label">¿Hora reunion grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->hora_reunion_grupo_obligatorio) class="switch-input" id="hora_reunion_grupo_obligatorio" name="horaReunionGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-12 col-md-4 mb-3">
              <div class="form-label">¿Habilitar día reunion grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_dia_reunion_grupo) class="switch-input" id="habilitar_dia_reunion_grupo" name="habilitarDiaReunionGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-12 col-md-4 mb-3" id="contenedor_label_campo_dia_reunion_grupo">
              <label for="html5-time-input" class="form-label">Label campo día reunion grupo</label>
              <input class="form-control" name="labelCampoDiaReunionGrupo" type="text" id="label_campo_dia_reunion_grupo" />
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_dia_reunion_grupo_obligatorio">
              <div class="form-label">¿Día reunion grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->dia_reunion_grupo_obligatorio) class="switch-input" id="dia_reunion_grupo_obligatorio" name="diaReunionGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar direccion grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_direccion_grupo) class="switch-input" id="habilitar_direccion_grupo" name="habilitarDireccionGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_label_direccion_grupo">
              <label for="html5-time-input" class="form-label">Label dirección grupo</label>
              <input class="form-control" name="labelDireccionGrupo" type="text" value="{{$configuracion->label_direccion_grupo}}" />
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_direccion_grupo_obligatorio">
              <div class="form-label">¿Dirección grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->direccion_grupo_obligatorio) class="switch-input" id="direccion_grupo_obligatorio" name="direccionGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar fecha creación grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_fecha_creacion_grupo) class="switch-input" id="habilitar_fecha_creacion_grupo" name="habilitarFechaCreacionGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-12 col-md-4 mb-3" id="contenedor_label_fecha_creacion_grupo">
              <label for="html5-time-input" class="form-label">Label fecha creación grupo</label>
              <input class="form-control" name="labelCreacionGrupo" type="text" value="{{$configuracion->label_fecha_creacion_grupo}}" id="label_fecha_creacion_grupo" />
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_fecha_creacion_grupo_obligatorio">
              <div class="form-label">¿Fecha creación grupo obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->fecha_creacion_grupo_obligatorio) class="switch-input" id="fecha_creacion_grupo_obligatorio" name="fechaCreacionGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar campo opcional 1 grupo?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_campo_opcional1_grupo) class="switch-input" id="habilitar_campo_opcional1_grupo" name="habilitarCampoOpcional1Grupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
            <div class="col-12 col-md-4 mb-3" id="contenedor_label_campo_opcional1">
              <label for="html5-time-input" class="form-label">Label campo opcional 1</label>
              <input class="form-control" name="labelCampoOpcional1" type="text" value="{{$configuracion->label_campo_opcional1}}" id="label_campo_opcional1" />
            </div>
            <div class="col-12 col-md-4 mb-3" id="contenedor_campo_opcional1_obligatorio">
              <div class="form-label">¿Campo opcional 1 obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->campo_opcional1_obligatorio) class="switch-input" id="campo_opcional1_obligatorio" name="campoOpcional1Obligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Informe de evidencia para grupos</h5>

        <div class="card-body">
          <div class="row">
            
            {{-- Switch: Habilitar Campo 1 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar campo 1?</div>
              <label class="switch switch-lg">
                <input type="checkbox" 
                       @checked($configuracion->habilitar_campo_1_informe_evidencias_grupo) 
                       class="switch-input" 
                       id="habilitarCampo1InformeEvidenciasGrupo" 
                       name="habilitarCampo1InformeEvidenciasGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Sí</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Input: Label Campo 1 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_labelCampo1InformeEvidenciasGrupo">
              <label for="labelCampo1InformeEvidenciasGrupo" class="form-label">Label del campo 1</label>
              <input class="form-control" 
                     name="labelCampo1InformeEvidenciasGrupo" 
                     type="text" 
                     value="{{ old('labelCampo1InformeEvidenciasGrupo', $configuracion->label_campo_1_informe_evidencias_grupo) }}" 
                     id="labelCampo1InformeEvidenciasGrupo" 
                     placeholder="Ej: Testimonio" />
              @error('labelCampo1InformeEvidenciasGrupo')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Switch: Obligatorio Campo 1 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_campo1InformeEvidenciasGrupoObligatorio">
              <div class="form-label">¿El campo 1 es obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" 
                       @checked($configuracion->campo_1_informe_evidencias_grupo_obligatorio) 
                       class="switch-input" 
                       id="campo1InformeEvidenciasGrupoObligatorio" 
                       name="campo1InformeEvidenciasGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Sí</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>  
          </div>

          <div class="row">                        

            {{-- Switch: Habilitar Campo 2 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar campo 2?</div>
              <label class="switch switch-lg">
                <input type="checkbox" 
                       @checked($configuracion->habilitar_campo_2_informe_evidencias_grupo) 
                       class="switch-input" 
                       id="habilitarCampo2InformeEvidenciasGrupo" 
                       name="habilitarCampo2InformeEvidenciasGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Sí</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Input: Label Campo 2 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_labelCampo2InformeEvidenciasGrupo">
              <label for="labelCampo2InformeEvidenciasGrupo" class="form-label">Label del campo 2</label>
              <input class="form-control" 
                     name="labelCampo2InformeEvidenciasGrupo" 
                     type="text" 
                     value="{{ old('labelCampo2InformeEvidenciasGrupo', $configuracion->label_campo_2_informe_evidencias_grupo) }}" 
                     id="labelCampo2InformeEvidenciasGrupo" 
                     placeholder="Ej: Petición" />
              @error('labelCampo2InformeEvidenciasGrupo')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Switch: Obligatorio Campo 2 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_campo2InformeEvidenciasGrupoObligatorio">
              <div class="form-label">¿El campo 2 es obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" 
                       @checked($configuracion->campo_2_informe_evidencias_grupo_obligatorio) 
                       class="switch-input" 
                       id="campo2InformeEvidenciasGrupoObligatorio" 
                       name="campo2InformeEvidenciasGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Sí</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

          </div>

          <div class="row">       
            {{-- Switch: Habilitar Campo 3 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Habilitar campo 3?</div>
              <label class="switch switch-lg">
                <input type="checkbox" 
                       @checked($configuracion->habilitar_campo_3_informe_evidencias_grupo) 
                       class="switch-input" 
                       id="habilitarCampo3InformeEvidenciasGrupo" 
                       name="habilitarCampo3InformeEvidenciasGrupo" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Sí</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Input: Label Campo 3 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_labelCampo3InformeEvidenciasGrupo">
              <label for="labelCampo3InformeEvidenciasGrupo" class="form-label">Label del campo 3</label>
              <input class="form-control" 
                     name="labelCampo3InformeEvidenciasGrupo" 
                     type="text" 
                     value="{{ old('labelCampo3InformeEvidenciasGrupo', $configuracion->label_campo_3_informe_evidencias_grupo) }}" 
                     id="labelCampo3InformeEvidenciasGrupo" 
                     placeholder="Ej: Observación" />
              @error('labelCampo3InformeEvidenciasGrupo')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Switch: Obligatorio Campo 3 --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3" id="contenedor_campo3InformeEvidenciasGrupoObligatorio">
              <div class="form-label">¿El campo 3 es obligatorio?</div>
              <label class="switch switch-lg">
                <input type="checkbox" 
                       @checked($configuracion->campo_3_informe_evidencias_grupo_obligatorio) 
                       class="switch-input" 
                       id="campo3InformeEvidenciasGrupoObligatorio" 
                       name="campo3InformeEvidenciasGrupoObligatorio" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Sí</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>


          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Usuario</h5>
        <div class="card-body">
          <div class="row"> 
            {{-- Correo por defecto --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Correo por defecto?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->correo_por_defecto) class="switch-input" id="correo_por_defecto" name="correoPorDefecto" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>
    
            {{-- Identificación obligatoria --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Identificación obligatoria?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->identificacion_obligatoria) class="switch-input" id="identificacion_obligatoria" name="identificacionObligatoria" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Identificación solo numérica --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Identificación solo numérica?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->identificacion_solo_numerica) class="switch-input" id="identificacion_solo_numerica" name="identificacionSoloNumerica" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Tiempo inactivo grupo --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="tiempo_inactivo_grupo" class="form-label">Tiempo para definir inactivo grupo (días)</label>
              <input class="form-control" name="tiempoParaDefinirInactivoGrupo" type="text" value="{{ $configuracion->tiempo_para_definir_inactivo_grupo }}" id="tiempo_inactivo_grupo" />
            </div>

            {{-- Tiempo inactivo reunión --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="tiempo_inactivo_reunion" class="form-label">Tiempo para definir inactivo reunión (días)</label>
              <input class="form-control" name="tiempoParaDefinirInactivoReunion" type="text" value="{{ $configuracion->tiempo_para_definir_inactivo_reunion }}" id="tiempo_inactivo_reunion" />
            </div>

            {{-- Edad mínima logueo --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="edad_minima_logueo" class="form-label">Edad mínima para logueo</label>
              <input class="form-control" name="edadMinimaLogueo" type="text" value="{{ $configuracion->edad_minima_logueo }}" id="edad_minima_logueo" />
              @error('edadMinimaLogueo')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Enviar correo bienvenida nuevo asistente --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Enviar correo de bienvenida a nuevo asistente?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->enviar_correo_bienvenida_nuevo_asistente) class="switch-input" id="enviar_correo_bienvenida_nuevo_asistente" name="enviarCorreoBienvenidaNuevoAsistente" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Mostrar banner mensaje bienvenida --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Mostrar banner de mensaje de bienvenida?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->banner_mensaje_bienvenida) class="switch-input" id="banner_mensaje_bienvenida" name="bannerMensajeBienvenida" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">Si</span>
                  <span class="switch-off">No</span>
                </span>
              </label>
            </div>

            {{-- Título del mensaje de bienvenida --}}
            <div class="col-md-6 col-sm-12 mb-3">
              <label for="titulo_mensaje_bienvenida" class="form-label">Título del mensaje de bienvenida</label>
              <input class="form-control" name="tituloMensajeBienvenida" type="text" value="{{ $configuracion->titulo_mensaje_bienvenida }}" id="titulo_mensaje_bienvenida" />
            </div>

            <div class="col-12 mb-2 px-2">
              <label for="mensajeBienvenida" class="form-label">Mensaje de bienvenida</label>

              {{-- Editor Quill --}}
              <div id="editor" style="min-height: 200px;"></div>

              {{-- Input oculto para enviar el contenido HTML --}}
              <input type="hidden" id="mensajeBienvenida" name="mensajeBienvenida">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Informes</h5>
        <div class="card-body">
          <div class="row">
            {{-- Nombre resaltador informe mensual reportes grupo --}}
            <div class="col-md-6 col-sm-12 mb-3">
              <label for="nombre_resaltador_informe_mensual_reportes_grupo" class="form-label">
                Nombre resaltador informe mensual (reportes grupo)
              </label>
              <input class="form-control"
                name="nombreResaltadorInformeMensualReportesGrupo"
                type="text"
                value="{{ $configuracion->nombre_resaltador_informe_mensual_reportes_grupo }}"
                id="nombre_resaltador_informe_mensual_reportes_grupo" />
              @error('nombreResaltadorInformeMensualReportesGrupo')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Valor mínimo resaltador informe mensual reportes grupo --}}
            <div class="col-md-3 col-sm-6 mb-3">
              <label for="valor_minimo_resaltador_informe_mensual_reportes_grupo" class="form-label">
                Valor mínimo resaltador
              </label>
              <input class="form-control"
                name="valorMinimoResaltadorInformeMensualReportesGrupo"
                type="number"
                value="{{ $configuracion->valor_minimo_resaltador_informe_mensual_reportes_grupo }}"
                id="valor_minimo_resaltador_informe_mensual_reportes_grupo" />
              @error('valorMinimoResaltadorInformeMensualReportesGrupo')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Valor máximo resaltador informe mensual reportes grupo --}}
            <div class="col-md-3 col-sm-6 mb-3">
              <label for="valor_maximo_resaltador_informe_mensual_reportes_grupo" class="form-label">
                Valor máximo resaltador
              </label>
              <input class="form-control"
                name="valorMaximoResaltadorInformeMensualReportesGrupo"
                type="number"
                value="{{ $configuracion->valor_maximo_resaltador_informe_mensual_reportes_grupo }}"
                id="valor_maximo_resaltador_informe_mensual_reportes_grupo" />
              @error('valorMaximoResaltadorInformeMensualReportesGrupo')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Reuniones</h5>
        <div class="card-body">
          <div class="row">

            {{-- Label observación invitados modal --}}
            <div class="col-12 col-md-12 mb-3">
              <label for="label_observacion_invitados_modal" class="form-label">Label observación invitados modal</label>
              <textarea class="form-control" name="labelObservacionInvitadosModal" id="label_observacion_invitados_modal" rows="2">{{ $configuracion->label_observacion_invitados_modal }}</textarea>
            </div>

            {{-- Texto por defecto observación invitados modal --}}
            <div class="col-md-12 col-sm-12 mb-3">
              <label for="text_default_observacion_invitados_modal" class="form-label">Texto por defecto observación invitados</label>
              <textarea class="form-control" name="textDefaultObservacionInvitadosModal" id="text_default_observacion_invitados_modal" rows="2">{{ $configuracion->text_default_observacion_invitados_modal }}</textarea>
            </div>

            {{-- Label invitado reuniones --}}
            <div class="col-12 col-md-4 col-12 mb-3">
              <label for="label_invitado_reuniones" class="form-label">Label invitado reuniones</label>
              <input class="form-control" name="labelInvitadoReuniones" type="text" value="{{ $configuracion->label_invitado_reuniones }}" id="label_invitado_reuniones" />
            </div>

            {{-- Switches --}}
            <div class="col-12 col-md-4 col-12 mb-3">
              <div class="form-label">¿Habilitar observación al añadir invitados?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_observacion_anadir_invitados_modal) class="switch-input" id="habilitar_observacion_anadir_invitados_modal" name="habilitarObservacionAnadirInvitadosModal" />
                <span class="switch-toggle-slider"><span class="switch-on">Sí</span><span class="switch-off">No</span></span>
              </label>
            </div>

            <div class="col-12 col-md-4 col-12 mb-3">
              <div class="form-label">¿Habilitar contador al añadir invitados?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->habilitar_contador_anadir_invitados_modal) class="switch-input" id="habilitar_contador_anadir_invitados_modal" name="habilitarContadorAnadirInvitadosModal" />
                <span class="switch-toggle-slider"><span class="switch-on">Sí</span><span class="switch-off">No</span></span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Punto de pago</h5>
        <div class="card-body">
          <div class="row">

          {{-- Mensaje correo punto de pago --}}
          <div class="col-md-12 mb-3">
            <label for="mensaje_correo_punto_pago" class="form-label">Mensaje correo punto de pago</label>
            <textarea class="form-control" name="mensajeCorreoPuntoPago" id="mensaje_correo_punto_pago" rows="3">{{ $configuracion->mensaje_correo_punto_pago }}</textarea>
          </div>

          {{-- Moneda predeterminada punto de pago --}}
          <div class="col-md-4 col-sm-6 col-12 mb-3">
            <label for="moneda_predeterminada_punto_pago" class="form-label">Moneda predeterminada punto de pago</label>
            <input class="form-control" name="monedaPredeterminadaPuntoPago" type="text" value="{{ $configuracion->moneda_predeterminada_punto_pago }}" id="moneda_predeterminada_punto_pago" />
          </div>

          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Ingresos y Egresos</h5>
        <div class="card-body">
          <div class="row">

            {{-- Labels Ingresos --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="label_campoadicional1_ingresos" class="form-label">Campo adicional 1 ingresos</label>
              <input class="form-control" name="labelCampoadicional1Ingresos" type="text" value="{{ $configuracion->label_campoadicional1_ingresos }}" id="label_campoadicional1_ingresos" />
              @error('labelCampoadicional1Ingresos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="label_campoadicional2_ingresos" class="form-label">Campo adicional 2 ingresos</label>
              <input class="form-control" name="labelCampoadicional2Ingresos" type="text" value="{{ $configuracion->label_campoadicional2_ingresos }}" id="label_campoadicional2_ingresos" />
              @error('labelCampoadicional2Ingresos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            {{-- Labels Egresos --}}
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="label_campoadicional1_egresos" class="form-label">Campo adicional 1 egresos</label>
              <input class="form-control" name="labelCampoadicional1Egresos" type="text" value="{{ $configuracion->label_campoadicional1_egresos }}" id="label_campoadicional1_egresos" />
              @error('labelCampoadicional1Egresos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="label_campoadicional2_egresos" class="form-label">Campo adicional 2 egresos</label>
              <input class="form-control" name="labelCampoadicional2Egresos" type="text" value="{{ $configuracion->label_campoadicional2_egresos }}" id="label_campoadicional2_egresos" />
              @error('labelCampoadicional2Egresos')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>  
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Reportes de Grupo</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">¿Tiene sistema de aprobación de reporte?</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->tiene_sistema_aprobacion_de_reporte) class="switch-input" id="tiene_sistema_aprobacion_de_reporte" name="tieneSistemaAprobacionDeReporte" />
                <span class="switch-toggle-slider"><span class="switch-on">Sí</span><span class="switch-off">No</span></span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Escuelas</h5>
        <div class="card-body">
          <div class="row">

            @php
            $escuelaSwitches = [
            'opciones_extra_matriculas_escuelas' => '¿Opciones extra en matrículas?',
            'habilitar_salones_con_estaciones' => '¿Habilitar salones con estaciones?',
            'items_mixtos_escuelas_deshabilitados' => '¿Deshabilitar items mixtos en escuelas?',
            'cierre_cortes_habilitado' => '¿Cierre de cortes habilitado?',
            'habilitar_traslados' => '¿Habilitar traslados?',
            'espacio_academico_habilitado' => '¿Espacio académico habilitado?'
            ];
            @endphp

            @foreach ($escuelaSwitches as $id => $label)
            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <div class="form-label">{{ $label }}</div>
              <label class="switch switch-lg">
                <input type="checkbox" @checked($configuracion->$id) class="switch-input" id="{{ $id }}" name="{{ \Illuminate\Support\Str::camel($id) }}" />
                <span class="switch-toggle-slider"><span class="switch-on">Sí</span><span class="switch-off">No</span></span>
              </label>
            </div>
            @endforeach

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="cantidad_dias_alerta_notas_maestro" class="form-label">Días alerta notas maestro</label>
              <input class="form-control" name="cantidadDiasAlertaNotasMaestro" type="text" value="{{ $configuracion->cantidad_dias_alerta_notas_maestro }}" id="cantidad_dias_alerta_notas_maestro" />
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="cantidad_intentos_auto_matricula" class="form-label">Intentos automatricula</label>
              <input class="form-control" name="cantidadIntentosAutoMatricula" type="text" value="{{ $configuracion->cantidad_intentos_auto_matricula }}" id="cantidad_intentos_auto_matricula" />
            </div>

            <div class="col-md-4 col-sm-6 col-12 mb-3">
              <label for="dias_plazo_maximo_actualizacion_automatricula" class="form-label">Días plazo máximo actualización</label>
              <input class="form-control" name="diasPlazoMaximoActualizacionAutomatricula" type="text" value="{{ $configuracion->dias_plazo_maximo_actualizacion_automatricula }}" id="dias_plazo_maximo_actualizacion_automatricula" />
            </div>

            <div class="col-md-12 mb-3">
              <label for="mensaje_exito_auto_matricula" class="form-label">Mensaje éxito automatrícula</label>
              <textarea class="form-control" name="mensajeExitoAutoMatricula" id="mensaje_exito_auto_matricula" rows="2">{{ $configuracion->mensaje_exito_auto_matricula }}</textarea>
            </div>

            <div class="col-md-12 mb-3">
              <label for="mensaje_error_auto_matricula" class="form-label">Mensaje error automatrícula</label>
              <textarea class="form-control" name="mensajeErrorAutoMatricula" id="mensaje_error_auto_matricula" rows="2">{{ $configuracion->mensaje_error_auto_matricula }}</textarea>
            </div>

            <div class="col-md-12 mb-3">
              <label for="mensaje_existe_auto_matricula" class="form-label">Mensaje ya existe automatrícula</label>
              <textarea class="form-control" name="mensajeExisteAutoMatricula" id="mensaje_existe_auto_matricula" rows="2">{{ $configuracion->mensaje_existe_auto_matricula }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Consolidación</h5>
        <div class="card-body">
          <div class="row">

            <div class="col-md-3 col-sm-6 col-12 mb-3">
              <label for="html5-time-input" class="form-label">Limite menor edad</label>
              <input class="form-control" name="edadMinimaConsolidacion" type="number" value="{{$configuracion->edad_minima_consolidacion}}" id="html5-time-input" />
              @error('edadMinimaConsolidacion')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="d-flex mb-1 mt-5">
        <div class="me-auto">
          <button type="submit" class="btn btn-primary rounded-pill btnGuardar me-1">Guardar</button>
          <button type="reset" class="btn rounded-pill btn-label-secondary">Cancelar</button>
        </div>
      </div>
    </div> 
</form>

@endsection
