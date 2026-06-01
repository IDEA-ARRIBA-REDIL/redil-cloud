@php
$configData = Helper::appClasses();
$configuracion = \App\Models\Configuracion::find(1);
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Tipo de Grupo')

@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'
])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    $('#formulario').submit(function(){
      $('.btnGuardar').attr('disabled','disabled');

      Swal.fire({
        title: "Espera un momento",
        text: "Ya estamos guardando...",
        icon: "info",
        showCancelButton: false,
        showConfirmButton: false,
        showDenyButton: false
      });
    });

  });
</script>
@endsection

@push('scripts')
<script>
  // Lógica para reemplazar la imagen
  $('#btn-reemplazar').on('click', function() {
    // Oculta el mensaje de la imagen actual
    $('#info-imagen-actual').addClass('d-none');

    // Muestra el input para subir una nueva
    $('#contenedor-input-imagen').removeClass('d-none');
  });

  $('#btn-reemplazar-portada').on('click', function() {
    $('#info-portada-actual').addClass('d-none');
    $('#contenedor-input-portada').removeClass('d-none');
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

<h4 class=" mb-1 fw-semibold text-primary">Editar tipo de grupo: {{ $tipoGrupo->nombre }}</h4>
<p class="mb-4 text-black">Actualiza los datos del tipo de grupo.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" method="POST"
  action="{{ route('gestionar-tipos-de-grupos.actualizarTipoDeGrupo', $tipoGrupo->id) }}"
  enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  <div class="row">
    <div class="col-md-12 mt-10">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Información principal
        </h5>
        <div class="card-body">
          <div class="row">
            <!-- nombre --> 
            <div class="mb-3 col-12 col-md-6 col-md-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" placeholder="Nombre del tipo de grupo" value="{{ old('nombre', $tipoGrupo->nombre ?? '') }}">
              @error('nombre')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- nombre -->

            <!-- nombre_plural --> 
            <div class="mb-3 col-12 col-md-6 col-md-3">
              <label class="form-label">Nombre plural</label>
              <input type="text" name="nombre_plural" class="form-control" placeholder="Nombre del tipo de grupo" value="{{ old('nombre_plural', $tipoGrupo->nombre_plural ?? '') }}">
              @error('nombre_plural')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- nombre_plural -->

            <!-- descripcion -->
            <div class="mb-3 col-12">
              <label class="form-label">Descripción</label>
              <input type="text" name="descripcion" class="form-control" placeholder="Descripción breve" value="{{ old('descripcion', $tipoGrupo->descripcion ?? '') }}">
              @error('descripcion')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- descripcion -->

            <!-- imagen -->
            <div class="mb-3 col-12 col-md-6">
              <label for="imagen" class="form-label">Icono</label>

              @if ($tipoGrupo->imagen)
              {{-- Muestra la info de la imagen actual y el botón para reemplazar --}}
              <div id="info-imagen-actual" class="mb-2">
                <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <img src="{{ $tipoGrupo->imagen_url }}" alt="Imagen actual" class="rounded me-3 border" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="d-flex flex-column">
                      <span style="font-size: 0.75rem;" class="text-muted">Imagen actual</span>
                      <span class="text-truncate fw-semibold" style="font-size: 0.85rem;">{{ basename($tipoGrupo->imagen) }}</span>
                    </div>
                  </div>
                  <button type="button" id="btn-reemplazar" class="btn btn-icon btn-label-danger btn-sm" title="Quitar y reemplazar">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </div>
              @endif

              <div id="contenedor-input-imagen" class="{{ $tipoGrupo->imagen ? 'd-none' : '' }}">
                <div class="input-group">
                  <input type="file" id="imagen" name="imagen" class="form-control" accept="image/png">
                </div>
                @error('imagen')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
                @enderror
              </div>
            </div>
            <!-- imagen -->

            <!-- portada -->
            <div class="mb-3 col-12 col-md-6">
              <label for="portada" class="form-label">Portada</label>

              @if ($tipoGrupo->portada)
              <div id="info-portada-actual" class="mb-2">
                <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <img src="{{ $tipoGrupo->portada_url }}" alt="Portada actual" class="rounded me-3 border" style="width: 80px; height: 50px; object-fit: cover;">
                    <div class="d-flex flex-column">
                      <span style="font-size: 0.75rem;" class="text-muted">Portada actual</span>
                      <span class="text-truncate fw-semibold" style="font-size: 0.85rem;">{{ basename($tipoGrupo->portada) }}</span>
                    </div>
                  </div>
                  <button type="button" id="btn-reemplazar-portada" class="btn btn-icon btn-label-danger btn-sm" title="Quitar y reemplazar">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </div>
              @endif

              <div id="contenedor-input-portada" class="{{ $tipoGrupo->portada ? 'd-none' : '' }}">
                <div class="input-group">
                  <input type="file" id="portada" name="portada" class="form-control" accept="image/*">
                </div>
                @error('portada')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
                @enderror
              </div>
            </div>
            <!-- portada -->      

            <!-- geo_icono -->
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label">Icono del mapa</label>
              <input type="text" name="geo_icono" class="form-control" value="{{ old('geo_icono', $tipoGrupo->geo_icono ?? '') }}">
              @error('geo_icono')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- geo_icono -->

            <!-- color -->
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label">Color principal</label>
              <input type="color" name="color" id="color" class="form-control form-control-color" value="{{ old('color', $tipoGrupo->color ?? '#00A3FF') }}">
              @error('color')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- color -->
            
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Texto para los reportes
        </h5>
        <div class="card-body">
          <div class="row">
             
            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Título principal</label>
              <input type="text" name="titulo1_finalizar_reporte" class="form-control" value="{{ old('titulo1_finalizar_reporte', $tipoGrupo->titulo1_finalizar_reporte ?? 'Confirmar asistencia') }}">
              @error('titulo1_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo encargados</label>
              <input type="text" name="subtitulo_encargados_finalizar_reporte" class="form-control" value="{{ old('subtitulo_encargados_finalizar_reporte', $tipoGrupo->subtitulo_encargados_finalizar_reporte ?? 'Encargados') }}">
              @error('subtitulo_encargados_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo personas nuevas</label>
              <input type="text" name="subtitulo_sumatorias_adiccionales_finalizar_reporte" class="form-control" value="{{ old('subtitulo_sumatorias_adiccionales_finalizar_reporte', $tipoGrupo->subtitulo_sumatorias_adiccionales_finalizar_reporte ?? 'Personas nuevas') }}">
              @error('subtitulo_sumatorias_adiccionales_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo miembros</label>
              <input type="text" name="subtitulo_miembros_finalizar_reporte" class="form-control" value="{{ old('subtitulo_miembros_finalizar_reporte', $tipoGrupo->subtitulo_miebros_finalizar_reporte ?? 'Miembros del grupo') }}">
              @error('subtitulo_miembros_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo ofrendas</label>
              <input type="text" name="subtitulo_ofrendas_finalizar_reporte" class="form-control" value="{{ old('subtitulo_ofrendas_finalizar_reporte', $tipoGrupo->subtitulo_ofrendas_finalizar_reporte ?? 'Ofrendas') }}">
              @error('subtitulo_ofrendas_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Configuraciones numéricas
        </h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Orden</label>
              <input type="number" name="orden" class="form-control" value="{{ old('orden', $tipoGrupo->orden ?? 0) }}">
              @error('orden')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Metros de cobertura</label>
              <input type="number" name="metros_cobertura" class="form-control" value="{{ old('metros_cobertura', $tipoGrupo->metros_cobertura ?? 500) }}">
              @error('metros_cobertura')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Máx. reportes/semana</label>
              <input type="number" name="cantidad_maxima_reportes_semana" class="form-control" value="{{ old('cantidad_maxima_reportes_semana', $tipoGrupo->cantidad_maxima_reportes_semana ?? 1) }}">
              @error('cantidad_maxima_reportes_semana')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Días para inactividad</label>
              <input type="number" name="tiempo_para_definir_inactivo_grupo" class="form-control" value="{{ old('tiempo_para_definir_inactivo_grupo', $tipoGrupo->tiempo_para_definir_inactivo_grupo ?? 30) }}">
              @error('tiempo_para_definir_inactivo_grupo')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Horas link asistencia</label>
              <input type="number" name="horas_disponiblidad_link_asistencia" class="form-control" value="{{ old('horas_disponiblidad_link_asistencia', $tipoGrupo->horas_disponiblidad_link_asistencia ?? 0) }}">
              @error('horas_disponiblidad_link_asistencia')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>            
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">ID Tipo Usuario (Autom.)</label>
              <input type="number" name="automatizacion_tipo_usuario_id" class="form-control" value="{{ old('automatizacion_tipo_usuario_id', $tipoGrupo->automatizacion_tipo_usuario_id ?? '') }}">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Opciones y permisos 
        </h5>
        <div class="card-body">
          <div class="row">
            @php
            $checkboxes = [
              'seguimiento_actividad' => 'Seguimiento actividad', 
              'contiene_servidores' => 'Contiene servidores',
              'posible_grupo_sede' => 'Posible grupo sede', 
              'ingresos_individuales_discipulos' => 'Ingresos individuales discípulos',
              'ingresos_individuales_lideres' => 'Ingresos individuales líderes', 
              'registra_datos_planeacion' => 'Registra datos planeación',
              'servidores_solo_discipulos' => 'Servidores solo discípulos', 
              'visible_mapa_asignacion' => 'Visible en mapa',
              'tipo_evangelistico' => 'Tipo evangelístico', 
              'enviar_mensaje_bienvenida' => 'Enviar mensaje bienvenida',
              'sumar_encargado_asistencia_grupo' => 'Sumar encargado a la asistencia', 
              'registrar_inasistencia' => 'Registrar inasistencia',
              'inasistencia_obligatoria' => 'Inasistencia obligatoria'
            ];
            @endphp

            @foreach($checkboxes as $name => $label)
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $tipoGrupo->$name ?? false))>
                <label class="form-check-label">{{ $label }}</label>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Descripciones adicionales
        </h5>
        <div class="card-body">
          <div class="row">           

            <div class="col-12 mb-3">
              <label class="form-label">Mensaje de bienvenida</label>
              <textarea class="form-control" name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida', $tipoGrupo->mensaje_bienvenida ?? '') }}</textarea>
              @error('mensaje_bienvenida')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Descripción principal (Finalizar reporte)</label>
              <textarea class="form-control" name="descripcion1_finalizar_reporte" rows="3">{{ old('descripcion1_finalizar_reporte', $tipoGrupo->descripcion1_finalizar_reporte ?? 'Gestiona aquí las asistencias de los miembros del grupo.') }}</textarea>
              @error('descripcion1_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Descripción de ofrendas (Finalizar reporte)</label>
              <textarea class="form-control" name="descripcion_ofrendas_finalizar_reporte" rows="3">{{ old('descripcion_ofrendas_finalizar_reporte', $tipoGrupo->descripcion_ofrendas_finalizar_reporte ?? 'Ingresa el valor de las ofrendas recolectadas en el grupo.') }}</textarea>
              @error('descripcion_ofrendas_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >Actualizar</button>
    </div>
  </div>
  <!-- /botonera -->
</form>
@endsection
