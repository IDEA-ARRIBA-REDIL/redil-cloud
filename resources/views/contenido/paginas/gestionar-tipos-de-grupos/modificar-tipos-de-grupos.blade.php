@php
$configData = Helper::appClasses();
// Determina si estamos en modo edición o creación
$modoEdicion = isset($tipoGrupo);
@endphp

@extends('layouts/layoutMaster')

@section('title', $modoEdicion ? 'Editar Tipo de Grupo' : 'Crear Tipo de Grupo')

{{-- (Las secciones de page-style, vendor-script y page-script se mantienen igual) --}}
@section('page-style') ... @endsection
@section('vendor-script') ... @endsection
@section('page-script') ... @endsection

@push('scripts')
<script>
  // Lógica para reemplazar la imagen
  $('#btn-reemplazar').on('click', function() {
    // Oculta el mensaje de la imagen actual
    $('#info-imagen-actual').addClass('d-none');

    // Muestra el input para subir una nueva
    $('#contenedor-input-imagen').removeClass('d-none');
  });

  // Mostrar el input file cuando se presiona el botón "Adjuntar imagen/archivo"
  $(".botonSubirArchivo").click(function() {
    var input = $(this).data('input');
    $('#' + input).click();
  });

  // Mostrar el nombre del archivo seleccionado en el input de texto
  $('.inputFile').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    var input = $(this).data('input');
    $('#nombre_' + input).val(fileName);
  });

  // Lógica para mostrar el botón de reemplazo
  $(".btn-remplazar-archivo").click(function() {
    var archivoR = $(this).data('input');
    $("#mensaje_remplazar_" + archivoR).addClass('d-none');
    $("#div_input_" + archivoR).removeClass('d-none');
  });
</script>
@endpush

@section('content')

{{-- Título dinámico --}}
<h4 class="mb-1">{{ $modoEdicion ? 'Editar Tipo de Grupo: ' . $tipoGrupo->nombre : 'Crear Tipo de Grupo' }}</h4>
<p class="mb-4">{{ $modoEdicion ? 'Actualiza los datos del tipo de grupo.' : 'Registra un nuevo tipo de grupo en el sistema.' }}</p>

@include('layouts.status-msn')

<form role="form" method="POST"
  action="{{ $modoEdicion ? route('gestionar-tipos-de-grupos.actualizarTipoDeGrupo', $tipoGrupo->id) : route('gestionar-tipos-de-grupos.crearTipoDeGrupo') }}"
  enctype="multipart/form-data">
  @csrf
  @if($modoEdicion)
  @method('PATCH')
  @endif

  <div class="card p-4 w-100">
    <div class="row g-3">

      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Información General</h5>
      </div>

      <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" placeholder="Nombre del tipo de grupo" value="{{ old('nombre', $tipoGrupo->nombre ?? '') }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nombre plural</label>
        <input type="text" name="nombre_plural" class="form-control" placeholder="Ej: Grupos de Amistad" value="{{ old('nombre_plural', $tipoGrupo->nombre_plural ?? '') }}">
      </div>

      {{-- Imagen --}}
      <div class="col-md-4 mb-3">
        <label for="imagen" class="form-label">Imagen</label>

        @if ($tipoGrupo->imagen)
        {{-- Muestra la info de la imagen actual y el botón para reemplazar --}}
        <div id="info-imagen-actual">
          <div class="alert alert-info d-flex justify-content-between align-items-center p-2">
            <span class="text-truncate" style="font-size: 0.85rem;">
              <i class="ti ti-photo ti-sm me-1"></i>
              {{ $tipoGrupo->imagen }}
            </span>
            <button type="button" id="btn-reemplazar" class="btn btn-sm p-1">
              <i class="ti ti-circle-x"></i>
            </button>
          </div>
        </div>
        @endif

        {{-- Contenedor del input para subir una nueva imagen --}}
        {{-- Estará oculto por defecto si ya existe una imagen --}}
        <div id="contenedor-input-imagen" class="{{ $tipoGrupo->imagen ? 'd-none' : '' }}">
          <div class="input-group">
            <input type="file" id="imagen" name="imagen" class="form-control" accept="image/png">
          </div>
          @error('imagen')
          <div class="text-danger ti-12px mt-2">
            <i class="ti ti-circle-x"></i> {{ $message }}
          </div>
          @enderror
        </div>
      </div>
      {{-- /Imagen --}}

      <div class="col-md-8">
        <label class="form-label">Descripción</label>
        <input type="text" name="descripcion" class="form-control" placeholder="Descripción breve" value="{{ old('descripcion', $tipoGrupo->descripcion ?? '') }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">Icono</label>
        <input type="text" name="geo_icono" class="form-control" id="geoIconoInput">
      </div>
      <div class="col-md-4">
        <label class="form-label">Color principal</label>
        <div class="pickr-wrapper">
          <div id="pickr-container"></div>
          <input type="hidden" id="color_hex" name="color" value="{{ old('color', $tipoGrupo->color ?? '#00A3FF') }}">
        </div>
      </div>

      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Textos para Finalizar Reporte</h5>
      </div>
      <div class="col-md-6">
        <label class="form-label">Título principal</label>
        <input type="text" name="titulo1_finalizar_reporte" class="form-control" value="{{ old('titulo1_finalizar_reporte', $tipoGrupo->titulo1_finalizar_reporte ?? 'Confirmar asistencia') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Subtítulo encargados</label>
        <input type="text" name="subtitulo_encargados_finalizar_reporte" class="form-control" value="{{ old('subtitulo_encargados_finalizar_reporte', $tipoGrupo->subtitulo_encargados_finalizar_reporte ?? 'Encargados') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Subtítulo personas nuevas</label>
        <input type="text" name="subtitulo_sumatorias_adiccionales_finalizar_reporte" class="form-control" value="{{ old('subtitulo_sumatorias_adiccionales_finalizar_reporte', $tipoGrupo->subtitulo_sumatorias_adiccionales_finalizar_reporte ?? 'Personas nuevas') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Subtítulo miembros</label>
        <input type="text" name="subtitulo_miembros_finalizar_reporte" class="form-control" value="{{ old('subtitulo_miebros_finalizar_reporte', $tipoGrupo->subtitulo_miebros_finalizar_reporte ?? 'Miembros del grupo') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Subtítulo ofrendas</label>
        <input type="text" name="subtitulo_ofrendas_finalizar_reporte" class="form-control" value="{{ old('subtitulo_ofrendas_finalizar_reporte', $tipoGrupo->subtitulo_ofrendas_finalizar_reporte ?? 'Ofrendas') }}">
      </div>


      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Configuraciones Numéricas</h5>
      </div>
      <div class="col-md-3">
        <label class="form-label">Orden</label>
        <input type="number" name="orden" class="form-control" value="{{ old('orden', $tipoGrupo->orden ?? '0') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Metros de cobertura</label>
        <input type="number" name="metros_cobertura" class="form-control" value="{{ old('metros_cobertura', $tipoGrupo->metros_cobertura ?? '500') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Máx. reportes/semana</label>
        <input type="number" name="cantidad_maxima_reportes_semana" class="form-control" value="{{ old('cantidad_maxima_reportes_semana', $tipoGrupo->cantidad_maxima_reportes_semana ?? '1') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Días para inactividad</label>
        <input type="number" name="tiempo_para_definir_inactivo_grupo" class="form-control" value="{{ old('tiempo_para_definir_inactivo_grupo', $tipoGrupo->tiempo_para_definir_inactivo_grupo ?? '30') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Horas link asistencia</label>
        <input type="number" name="horas_disponiblidad_link_asistencia" class="form-control" value="{{ old('horas_disponiblidad_link_asistencia', $tipoGrupo->horas_disponiblidad_link_asistencia ?? '0') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">ID Tipo Usuario (Autom.)</label>
        <input type="number" name="automatizacion_tipo_usuario_id" class="form-control" value="{{ old('automatizacion_tipo_usuario_id', $tipoGrupo->automatizacion_tipo_usuario_id ?? '') }}">
      </div>

      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Opciones y Permisos</h5>
      </div>
      @php
      $checkboxes = [
      'seguimiento_actividad' => 'Seguimiento actividad', 'contiene_servidores' => 'Contiene servidores',
      'posible_grupo_sede' => 'Posible grupo sede', 'ingresos_individuales_discipulos' => 'Ingresos individuales discípulos',
      'ingresos_individuales_lideres' => 'Ingresos individuales líderes', 'registra_datos_planeacion' => 'Registra datos planeación',
      'servidores_solo_discipulos' => 'Servidores solo discípulos', 'visible_mapa_asignacion' => 'Visible en mapa',
      'tipo_evangelistico' => 'Tipo evangelístico', 'enviar_mensaje_bienvenida' => 'Enviar mensaje bienvenida',
      'sumar_encargado_asistencia_grupo' => 'Sumar encargado a la asistencia', 'registrar_inasistencia' => 'Registrar inasistencia',
      'inasistencia_obligatoria' => 'Inasistencia obligatoria'
      ];
      @endphp

      @foreach($checkboxes as $name => $label)
      <div class="col-md-3">
        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $tipoGrupo->$name ?? false))>
          <label class="form-check-label">{{ $label }}</label>
        </div>
      </div>
      @endforeach


      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Descripciones Adicionales</h5>
      </div>
      <div class="col-12">
        <label class="form-label">Mensaje de bienvenida</label>
        <textarea class="form-control" name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida', $tipoGrupo->mensaje_bienvenida ?? '') }}</textarea>
      </div>
      <div class="col-12">
        <label class="form-label">Descripción principal (Finalizar reporte)</label>
        <textarea class="form-control" name="descripcion1_finalizar_reporte" rows="3">{{ old('descripcion1_finalizar_reporte', $tipoGrupo->descripcion1_finalizar_reporte ?? 'Gestiona aquí las asistencias de los miembros del grupo.') }}</textarea>
      </div>
      <div class="col-12">
        <label class="form-label">Descripción de ofrendas (Finalizar reporte)</label>
        <textarea class="form-control" name="descripcion_ofrendas_finalizar_reporte" rows="3">{{ old('descripcion_ofrendas_finalizar_reporte', $tipoGrupo->descripcion_ofrendas_finalizar_reporte ?? 'Ingresa el valor de las ofrendas recolectadas en el grupo.') }}</textarea>
      </div>

    </div>

    <div class="mt-4 pt-4 border-top text-center">
      <button type="submit" class="btn btn-primary rounded-pill px-4">{{ $modoEdicion ? 'Actualizar' : 'Guardar' }}</button>
      <a href="{{ route('gestionar-tipos-de-grupos.modificarTipoDeGrupo') }}" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
    </div>
  </div>
</form>
@endsection

<script>
  document.getElementById('imagenInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validar extensión
    if (file.type !== 'image/png') {
      alert("Solo se permiten archivos PNG.");
      event.target.value = "";
      return;
    }

    // Validar dimensiones
    const img = new Image();
    img.src = URL.createObjectURL(file);
    img.onload = function() {
      if (img.width !== 100 || img.height !== 100) {
        alert("La imagen debe ser exactamente de 100x100 píxeles.");
        event.target.value = "";
      }
    };
  });

  document.getElementById('geoIconoInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validar extensión
    if (file.type !== 'image/png') {
      alert("Solo se permiten archivos PNG.");
      event.target.value = "";
      return;
    }

    // Validar dimensiones
    const img = new Image();
    img.src = URL.createObjectURL(file);
    img.onload = function() {
      if (img.width !== 100 || img.height !== 100) {
        alert("El icono debe ser exactamente de 100x100 píxeles.");
        event.target.value = "";
      }
    };
  });
</script>