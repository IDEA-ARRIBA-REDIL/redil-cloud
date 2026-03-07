@extends('layouts.layoutMaster')

@section('title', 'Editar Tipo de Usuario')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',

])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',

])
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('#id_rol_dependiente').select2({
      placeholder: 'Seleccione un rol dependiente',
      allowClear: true,
      width: '100%'
    });

  });

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
<div class="container mt-4">
  <div class="card shadow-lg rounded-3">
    <div class="card-header text-dark">
      <h5 class="mb-0">Editar Tipo de Usuario</h5>
    </div>
    <div class="card-body">
      {{-- Formulario de edición --}}
      <form action="{{ route('tipo-usuario.actualizar', $tipoUsuario) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- === FILA 1 === --}}
        <div class="row">
          {{-- Nombre --}}
          <div class="col-md-4 mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" name="nombre" id="nombre" class="form-control"
              value="{{ old('nombre', $tipoUsuario->nombre) }}" required>
            @error('nombre')
            <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          {{-- Nombre plural --}}
          <div class="col-md-4 mb-3">
            <label for="nombre_plural" class="form-label">Nombre plural</label>
            <input type="text" name="nombre_plural" id="nombre_plural" class="form-control"
              value="{{ old('nombre_plural', $tipoUsuario->nombre_plural) }}">
          </div>

          {{-- Descripción --}}
          <div class="col-md-4 mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="1">{{ old('descripcion', $tipoUsuario->descripcion) }}</textarea>
          </div>
        </div>

        {{-- === FILA 2 === --}}
        <div class="row">
          {{-- Color --}}
          <div class="col-md-4 mb-3">
            <label for="color" class="form-label">Color</label>
            <input type="color" name="color" id="color" class="form-control form-control-color"
              value="{{ old('color', $tipoUsuario->color) }}">
          </div>

          {{-- Ícono --}}
          <div class="col-md-4 mb-3">
            <label for="icono" class="form-label">Ícono</label>
            <input type="text" name="icono" id="icono" class="form-control"
              value="{{ old('icono', $tipoUsuario->icono) }}">
            <small class="text-muted">Puedes usar clases de íconos como <code>ti ti-user</code>.</small>
          </div>

          {{-- Imagen --}}
          <div class="col-md-4 mb-3">
            <label for="imagen" class="form-label">Imagen (100x100 PNG)</label>

            @if ($tipoUsuario->imagen)
            {{-- Muestra la info de la imagen actual y el botón para reemplazar --}}
            <div id="info-imagen-actual">
              <div class="alert alert-info d-flex justify-content-between align-items-center p-2">
                <span class="text-truncate" style="font-size: 0.85rem;">
                  <i class="ti ti-photo ti-sm me-1"></i>
                  {{ $tipoUsuario->imagen }}
                </span>
                <button type="button" id="btn-reemplazar" class="btn btn-sm btn-outline-danger p-1">
                  <i class="ti ti-replace"></i>
                </button>
              </div>
            </div>
            @endif

            {{-- Contenedor del input para subir una nueva imagen --}}
            {{-- Estará oculto por defecto si ya existe una imagen --}}
            <div id="contenedor-input-imagen" class="{{ $tipoUsuario->imagen ? 'd-none' : '' }}">
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

        </div>

        {{-- === FILA 3 === --}}
        <div class="row">
          {{-- Orden --}}
          <div class="col-md-4 mb-3">
            <label for="orden" class="form-label">Orden</label>
            <input type="number" name="orden" id="orden" class="form-control"
              value="{{ old('orden', $tipoUsuario->orden) }}">
          </div>

          {{-- Puntaje --}}
          <div class="col-md-4 mb-3">
            <label for="puntaje" class="form-label">Puntaje</label>
            <input type="number" name="puntaje" id="puntaje" class="form-control"
              value="{{ old('puntaje', $tipoUsuario->puntaje) }}">
          </div>

          {{-- Rol dependiente --}}
          <div class="col-md-4 mb-3">
            <label for="id_rol_dependiente" class="form-label">Rol dependiente</label>
            <select name="id_rol_dependiente" id="id_rol_dependiente" class="form-select select2">
              <option value="">-- Sin dependencia --</option>
              @foreach ($tiposUsuarios as $rol)
              <option value="{{ $rol->id }}"
                {{ old('id_rol_dependiente', $tipoUsuario->id_rol_dependiente) == $rol->id ? 'selected' : '' }}>
                {{ $rol->nombre }}
              </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- === FILA 4 === --}}
        <div class="row">
          {{-- Tipo pastor --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="tipo_pastor" name="tipo_pastor"
                {{ old('tipo_pastor', $tipoUsuario->tipo_pastor) ? 'checked' : '' }}>
              <label for="tipo_pastor" class="form-check-label">Es tipo pastor</label>
            </div>
          </div>

          {{-- Tipo pastor principal --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="tipo_pastor_principal" name="tipo_pastor_principal"
                {{ old('tipo_pastor_principal', $tipoUsuario->tipo_pastor_principal) ? 'checked' : '' }}>
              <label for="tipo_pastor_principal" class="form-check-label">Es pastor principal</label>
            </div>
          </div>

          {{-- Visible --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="visible" name="visible"
                {{ old('visible', $tipoUsuario->visible) ? 'checked' : '' }}>
              <label for="visible" class="form-check-label">Mostrar en búsquedas</label>
            </div>
          </div>
        </div>

        {{-- === FILA 5 === --}}
        <div class="row">
          {{-- Seguimiento grupo --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="seguimiento_actividad_grupo" name="seguimiento_actividad_grupo"
                {{ old('seguimiento_actividad_grupo', $tipoUsuario->seguimiento_actividad_grupo) ? 'checked' : '' }}>
              <label for="seguimiento_actividad_grupo" class="form-check-label">Activar seguimiento</label>
            </div>
          </div>

          {{-- Seguimiento reunión --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="seguimiento_actividad_reunion" name="seguimiento_actividad_reunion"
                {{ old('seguimiento_actividad_reunion', $tipoUsuario->seguimiento_actividad_reunion) ? 'checked' : '' }}>
              <label for="seguimiento_actividad_reunion" class="form-check-label">Activar seguimiento</label>
            </div>
          </div>

          {{-- Consolidación --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="habilitado_para_consolidacion" name="habilitado_para_consolidacion"
                {{ old('habilitado_para_consolidacion', $tipoUsuario->habilitado_para_consolidacion) ? 'checked' : '' }}>
              <label for="habilitado_para_consolidacion" class="form-check-label">Listar en consolidación</label>
            </div>
          </div>
        </div>

        {{-- === FILA 6 === --}}
        <div class="row">
          {{-- Tipo usuario por defecto --}}
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="default" name="default"
                {{ old('default', $tipoUsuario->default) ? 'checked' : '' }}>
              <label for="default" class="form-check-label">Establecer como predeterminado</label>
            </div>
          </div>
        </div>

        {{-- === BOTONES === --}}
        <div class="mt-4">
          <button type="submit" class="btn btn-primary rounded-pill waves-effect waves-light">Actualizar</button>
          <a href="{{ route('tipo-usuario.listar') }}" class="btn btn-outline-secondary rounded-pill waves-effect waves-light">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
