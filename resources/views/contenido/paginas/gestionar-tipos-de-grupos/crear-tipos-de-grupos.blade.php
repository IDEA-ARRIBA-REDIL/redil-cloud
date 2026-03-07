@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Crear Tipo de Grupo')

{{-- Estilos y scripts (sin cambios) --}}
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/pickr/pickr-themes.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/pickr/pickr.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'
])
@endsection

@section('page-script')
{{-- (Tu script para el color picker Pickr.js va aquí, no necesita cambios) --}}
@endsection

@push('scripts')
{{-- (Tus scripts para manejo de archivos van aquí, sin cambios) --}}
@endpush

@section('content')

<h4 class="mb-1">Crear Tipo de Grupo</h4>
<p class="mb-4">Registra un nuevo tipo de grupo en el sistema.</p>

@include('layouts.status-msn')

<form role="form" method="POST" action="{{ route('gestionar-tipos-de-grupos.crearTipoDeGrupo') }}" enctype="multipart/form-data">
  @csrf

  <div class="card p-4 w-100">
    <div class="row g-3">

      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Información General</h5>
      </div>

      <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" placeholder="Nombre del tipo de grupo" value="{{ old('nombre') }}">
        @error('nombre')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Nombre plural</label>
        <input type="text" name="nombre_plural" class="form-control" placeholder="Ej: Grupos de Amistad" value="{{ old('nombre_plural') }}">
        @error('nombre_plural')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      {{-- Imagen --}}
      <div class="col-md-4 mb-3">
        <label for="imagen" class="form-label">Imagen (100x100px)</label>
        <div id="contenedor-input-imagen">
          <div class="input-group">
            <input type="file" id="imagen" name="imagen" class="form-control" accept="image/png">
          </div>
          @error('imagen')
          <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
          @enderror
        </div>
      </div>
      {{-- /Imagen --}}

      <div class="col-md-8">
        <label class="form-label">Descripción</label>
        <input type="text" name="descripcion" class="form-control" placeholder="Descripción breve" value="{{ old('descripcion') }}">
        @error('descripcion')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Icono del mapa</label>
        <input type="text" name="geo_icono" class="form-control" value="{{ old('geo_icono') }}">
        @error('geo_icono')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label class="form-label">Color principal</label>
        <div class="pickr-wrapper">
          <div id="pickr-container"></div>
          <input type="hidden" id="color_hex" name="color" value="{{ old('color', '#00A3FF') }}">
        </div>
        @error('color')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Textos para Finalizar Reporte</h5>
      </div>

      {{-- Bucle @foreach expandido --}}
      <div class="col-md-6">
        <label class="form-label">Título principal</label>
        <input type="text" name="titulo1_finalizar_reporte" class="form-control" value="{{ old('titulo1_finalizar_reporte', 'Confirmar asistencia') }}">
        @error('titulo1_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">Subtítulo encargados</label>
        <input type="text" name="subtitulo_encargados_finalizar_reporte" class="form-control" value="{{ old('subtitulo_encargados_finalizar_reporte', 'Encargados') }}">
        @error('subtitulo_encargados_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">Subtítulo personas nuevas</label>
        <input type="text" name="subtitulo_sumatorias_adiccionales_finalizar_reporte" class="form-control" value="{{ old('subtitulo_sumatorias_adiccionales_finalizar_reporte', 'Personas nuevas') }}">
        @error('subtitulo_sumatorias_adiccionales_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">Subtítulo miembros</label>
        <input type="text" name="subtitulo_miembros_finalizar_reporte" class="form-control" value="{{ old('subtitulo_miembros_finalizar_reporte', 'Miembros del grupo') }}">
        @error('subtitulo_miembros_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">Subtítulo ofrendas</label>
        <input type="text" name="subtitulo_ofrendas_finalizar_reporte" class="form-control" value="{{ old('subtitulo_ofrendas_finalizar_reporte', 'Ofrendas') }}">
        @error('subtitulo_ofrendas_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
      {{-- Fin del bloque expandido --}}


      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Configuraciones Numéricas</h5>
      </div>

      <div class="col-md-3">
        <label class="form-label">Orden</label>
        <input type="number" name="orden" class="form-control" value="{{ old('orden') }}">
        @error('orden')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Metros de cobertura</label>
        <input type="number" name="metros_cobertura" class="form-control" value="{{ old('metros_cobertura', 500) }}">
        @error('metros_cobertura')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Máx. reportes/semana</label>
        <input type="number" name="cantidad_maxima_reportes_semana" class="form-control" value="{{ old('cantidad_maxima_reportes_semana', 1) }}">
        @error('cantidad_maxima_reportes_semana')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Días para inactividad</label>
        <input type="number" name="tiempo_para_definir_inactivo_grupo" class="form-control" value="{{ old('tiempo_para_definir_inactivo_grupo', 30) }}">
        @error('tiempo_para_definir_inactivo_grupo')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Horas link asistencia</label>
        <input type="number" name="horas_disponiblidad_link_asistencia" class="form-control" value="{{ old('horas_disponiblidad_link_asistencia', 0) }}">
        @error('horas_disponiblidad_link_asistencia')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>


      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Opciones y Permisos</h5>
      </div>

      {{-- Checkboxes con valores por defecto aplicados --}}
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="seguimiento_actividad" value="1" {{ old('seguimiento_actividad') ? 'checked' : '' }}><label class="form-check-label">Seguimiento actividad</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="contiene_servidores" value="1" {{ old('contiene_servidores') ? 'checked' : '' }}><label class="form-check-label">Contiene servidores</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="posible_grupo_sede" value="1" {{ old('posible_grupo_sede') ? 'checked' : '' }}><label class="form-check-label">Posible grupo sede</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="ingresos_individuales_discipulos" value="1" {{ old('ingresos_individuales_discipulos', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Ingresos individuales discípulos</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="ingresos_individuales_lideres" value="1" {{ old('ingresos_individuales_lideres', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Ingresos individuales líderes</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="registra_datos_planeacion" value="1" {{ old('registra_datos_planeacion') ? 'checked' : '' }}><label class="form-check-label">Registra datos planeación</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="servidores_solo_discipulos" value="1" {{ old('servidores_solo_discipulos', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Servidores solo discípulos</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="visible_mapa_asignacion" value="1" {{ old('visible_mapa_asignacion', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Visible en mapa</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="tipo_evangelistico" value="1" {{ old('tipo_evangelistico') ? 'checked' : '' }}><label class="form-check-label">Tipo evangelístico</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="enviar_mensaje_bienvenida" value="1" {{ old('enviar_mensaje_bienvenida') ? 'checked' : '' }}><label class="form-check-label">Enviar mensaje bienvenida</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="sumar_encargado_asistencia_grupo" value="1" {{ old('sumar_encargado_asistencia_grupo') ? 'checked' : '' }}><label class="form-check-label">Sumar encargado a asistencia</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="registrar_inasistencia" value="1" {{ old('registrar_inasistencia') ? 'checked' : '' }}><label class="form-check-label">Registrar inasistencia</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="inasistencia_obligatoria" value="1" {{ old('inasistencia_obligatoria') ? 'checked' : '' }}><label class="form-check-label">Inasistencia obligatoria</label></div>
      </div>


      <div class="col-12">
        <h5 class="mt-4 border-bottom pb-2">Descripciones Adicionales</h5>
      </div>

      <div class="col-12">
        <label class="form-label">Mensaje de bienvenida</label>
        <textarea class="form-control" name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida') }}</textarea>
        @error('mensaje_bienvenida')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-12">
        <label class="form-label">Descripción principal (Finalizar reporte)</label>
        <textarea class="form-control" name="descripcion1_finalizar_reporte" rows="3">{{ old('descripcion1_finalizar_reporte', 'Gestiona aquí las asistencias de los miembros del grupo.') }}</textarea>
        @error('descripcion1_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>

      <div class="col-12">
        <label class="form-label">Descripción de ofrendas (Finalizar reporte)</label>
        <textarea class="form-control" name="descripcion_ofrendas_finalizar_reporte" rows="3">{{ old('descripcion_ofrendas_finalizar_reporte', 'Ingresa el valor de las ofrendas recolectadas en el grupo.') }}</textarea>
        @error('descripcion_ofrendas_finalizar_reporte')
        <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="mt-4 pt-4 border-top text-center">
      <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar</button>
      <a href="{{ route('gestionar-tipos-de-grupos.listar') }}" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
    </div>
  </div>
</form>

@endsection