@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Crear Tipo de Usuario')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'
])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    // Inicializar Select2
    $('#id_rol_dependiente').select2({
      placeholder: 'Seleccione un rol dependiente',
      allowClear: true
    });

    // Manejador del formulario
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

@section('content')
<h4 class="fw-semibold text-primary mb-1">Nuevo Tipo de Usuario</h4>
<p class="mb-4 text-black">Registra un nuevo tipo de usuario y define sus parámetros de comportamiento.</p>

@include('layouts.status-msn')

<form id="formulario" action="{{ route('tipo-usuario.crear') }}" method="POST" enctype="multipart/form-data">
  @csrf

  <div class="row">
    <!-- Columna Izquierda -->
    <div class="col-12">
      <!-- Card: Información básica -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Información básica</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombre" class="form-label">Nombre *</label>
              <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Líder" required>
              @error('nombre')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="nombre_plural" class="form-label">Nombre plural</label>
              <input type="text" name="nombre_plural" id="nombre_plural" class="form-control" value="{{ old('nombre_plural') }}" placeholder="Ej: Líderes">
            </div>
            <div class="col-12 mb-3">
              <label for="descripcion" class="form-label">Descripción</label>
              <textarea name="descripcion" id="descripcion" class="form-control" rows="2" placeholder="Breve descripción del rol...">{{ old('descripcion') }}</textarea>
            </div>
            <div class="col-md-3 mb-3">
              <label for="color" class="form-label">Color representativo</label>
              <input type="color" name="color" id="color" class="form-control form-control-color w-100" value="{{ old('color', '#666CE8') }}">
            </div>
            <div class="col-md-4 mb-3">
              <label for="icono" class="form-label">Clase del ícono</label>
              <input type="text" name="icono" id="icono" class="form-control" value="{{ old('icono') }}" placeholder="ti ti-user">
            </div>
            <div class="col-md-5 mb-3">
              <label for="imagen" class="form-label">Imagen (100x100 PNG)</label>
              <input class="form-control" type="file" name="imagen" id="imagen" accept="image/png">
              @error('imagen')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Parámetros de configuración -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Parámetros de configuración</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="orden" class="form-label">Orden de jerarquía</label>
              <input type="number" name="orden" id="orden" class="form-control" value="{{ old('orden', 0) }}">
            </div>
            <div class="col-md-4 mb-3">
              <label for="puntaje" class="form-label">Puntaje acumulable</label>
              <input type="number" name="puntaje" id="puntaje" class="form-control" value="{{ old('puntaje', 0) }}">
            </div>
            <div class="col-md-4 mb-3">
              <label for="id_rol_dependiente" class="form-label">Rol dependiente</label>
              <select name="id_rol_dependiente" id="id_rol_dependiente" class="form-select select2">
                <option value="">Sin dependencia</option>
                @foreach ($tiposUsuarios as $rol)
                <option value="{{ $rol->id }}" {{ old('id_rol_dependiente') == $rol->id ? 'selected' : '' }}>
                  {{ $rol->nombre }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna Derecha -->
    <div class="col-12">
      <!-- Card: Atributos y visibilidad -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Atributos y visibilidad</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="tipo_pastor" name="tipo_pastor" value="1" {{ old('tipo_pastor') ? 'checked' : '' }}>
                <label for="tipo_pastor" class="form-check-label fw-medium text-black">Es tipo pastor</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="tipo_pastor_principal" name="tipo_pastor_principal" value="1" {{ old('tipo_pastor_principal') ? 'checked' : '' }}>
                <label for="tipo_pastor_principal" class="form-check-label fw-medium text-black">Es pastor principal</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="visible" name="visible" value="1" {{ old('visible', true) ? 'checked' : '' }}>
                <label for="visible" class="form-check-label fw-medium text-black">Mostrar en búsquedas</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Seguimiento y consolidación -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Seguimiento y consolidación</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="seguimiento_actividad_grupo" name="seguimiento_actividad_grupo" value="1" {{ old('seguimiento_actividad_grupo', true) ? 'checked' : '' }}>
                <label for="seguimiento_actividad_grupo" class="form-check-label fw-medium text-black">Seguimiento de grupo</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="seguimiento_actividad_reunion" name="seguimiento_actividad_reunion" value="1" {{ old('seguimiento_actividad_reunion', true) ? 'checked' : '' }}>
                <label for="seguimiento_actividad_reunion" class="form-check-label fw-medium text-black">Seguimiento de reunión</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="habilitado_para_consolidacion" name="habilitado_para_consolidacion" value="1" {{ old('habilitado_para_consolidacion') ? 'checked' : '' }}>
                <label for="habilitado_para_consolidacion" class="form-check-label fw-medium text-black">Listar en consolidación</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Inactividad y automatización -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Inactividad y automatización</h5>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 mt-3">
              <label for="dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion" class="form-label">Días de inactividad para baja</label>
              <input type="number" name="dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion" id="dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion" class="form-control" value="{{ old('dias_de_seguimiento_para_dar_de_baja_por_no_iniciar_sesion', 0) }}">
              <small class="text-muted">0 para deshabilitar.</small>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="seguimiento_para_dar_de_baja_automaticamente" name="seguimiento_para_dar_de_baja_automaticamente" value="1" {{ old('seguimiento_para_dar_de_baja_automaticamente') ? 'checked' : '' }}>
                <label for="seguimiento_para_dar_de_baja_automaticamente" class="form-check-label fw-medium text-black">Habilitar baja automática</label>
              </div>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="default" name="default" value="1" {{ old('default') ? 'checked' : '' }}>
                <label for="default" class="form-check-label fw-medium text-black">Rol predeterminado</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex mb-1 mt-3">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">Guardar</button>
    </div>
  </div>
</form>
@endsection