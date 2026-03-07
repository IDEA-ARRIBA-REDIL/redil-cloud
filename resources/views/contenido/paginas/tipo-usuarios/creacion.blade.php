@extends('layouts.layoutMaster')

@section('title', 'Crear Tipo de Usuario')

@push('scripts')
<script>
  $(document).ready(function() {
    $('#id_rol_dependiente').select2({
      placeholder: 'Seleccione un rol dependiente',
      allowClear: true,
      width: '100%'
    });
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
      <h5 class="mb-0">Nuevo Tipo de Usuario</h5>
    </div>

    <div class="card-body">
      <form action="{{ route('tipo-usuario.crear') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- === FILA 1 === --}}
        <div class="row">
          {{-- Nombre --}}
          <div class="col-md-4 mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" name="nombre" id="nombre" class="form-control"
              value="{{ old('nombre') }}" required>
            @error('nombre')
            <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          {{-- Nombre plural --}}
          <div class="col-md-4 mb-3">
            <label for="nombre_plural" class="form-label">Nombre plural</label>
            <input type="text" name="nombre_plural" id="nombre_plural" class="form-control"
              value="{{ old('nombre_plural') }}">
          </div>

          {{-- Descripción --}}
          <div class="col-md-4 mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="1">{{ old('descripcion') }}</textarea>
          </div>
        </div>

        {{-- === FILA 2 === --}}
        <div class="row">
          {{-- Color --}}
          <div class="col-md-1 mb-3">
            <label for="color" class="form-label">Color</label>
            <input type="color" name="color" id="color" class="form-control form-control-color"
              value="{{ old('color') }}">
          </div>

          {{-- Ícono --}}
          <div class="col-md-4 mb-3">
            <label for="icono" class="form-label">Ícono</label>
            <input type="text" name="icono" id="icono" class="form-control"
              value="{{ old('icono') }}">
          </div>

          <!-- Imagen -->
          <div class="col-md-7 mb-3 {{ $campo->pivot->class ?? '' }}">
            <label id="label_imagen" class="form-label" for="imagen">
              Imagen
            </label>

            <div class="mb-3">

              <input class="form-control" type="file" name="imagen" id="imagen">
            </div>

            @error('imagen')
            <div class="text-danger ti-12px mt-2">
              <i class="ti ti-circle-x"></i> {{ $message }}
            </div>
            @enderror
          </div>
          <!--/ Imagen -->

        </div>

        {{-- === FILA 3 === --}}
        <div class="row">
          {{-- Orden --}}
          <div class="col-md-4 mb-3">
            <label for="orden" class="form-label">Orden</label>
            <input type="number" name="orden" id="orden" class="form-control"
              value="{{ old('orden') }}">
          </div>

          {{-- Puntaje --}}
          <div class="col-md-4 mb-3">
            <label for="puntaje" class="form-label">Puntaje</label>
            <input type="number" name="puntaje" id="puntaje" class="form-control"
              value="{{ old('puntaje', 0) }}">
          </div>

          {{-- Rol dependiente --}}
          <div class="col-md-4 mb-3">
            <label for="id_rol_dependiente" class="form-label">Rol dependiente</label>
            <select name="id_rol_dependiente" id="id_rol_dependiente" class="form-select select2">
              <option value=""> Sin dependencia </option>
              @foreach ($tiposUsuarios as $rol)
              <option value="{{ $rol->id }}"
                {{ old('id_rol_dependiente') == $rol->id ? 'selected' : '' }}>
                {{ $rol->nombre }}
              </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- === FILA 4 === --}}
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="tipo_pastor" name="tipo_pastor" value="1"
                {{ old('tipo_pastor') ? 'checked' : '' }}>
              <label for="tipo_pastor" class="form-check-label">Es tipo pastor</label>
            </div>
          </div>

          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="tipo_pastor_principal" name="tipo_pastor_principal" value="1"
                {{ old('tipo_pastor_principal') ? 'checked' : '' }}>
              <label for="tipo_pastor_principal" class="form-check-label">Es pastor principal</label>
            </div>
          </div>

          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="visible" name="visible" value="1"
                {{ old('visible', true) ? 'checked' : '' }}>
              <label for="visible" class="form-check-label">Mostrar en búsquedas</label>
            </div>
          </div>
        </div>

        {{-- === FILA 5 === --}}
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="seguimiento_actividad_grupo" name="seguimiento_actividad_grupo" value="1"
                {{ old('seguimiento_actividad_grupo', true) ? 'checked' : '' }}>
              <label for="seguimiento_actividad_grupo" class="form-check-label">Seguimiento de grupo</label>
            </div>
          </div>

          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="seguimiento_actividad_reunion" name="seguimiento_actividad_reunion" value="1"
                {{ old('seguimiento_actividad_reunion', true) ? 'checked' : '' }}>
              <label for="seguimiento_actividad_reunion" class="form-check-label">Seguimiento de reunión</label>
            </div>
          </div>

          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="habilitado_para_consolidacion" name="habilitado_para_consolidacion" value="1"
                {{ old('habilitado_para_consolidacion') ? 'checked' : '' }}>
              <label for="habilitado_para_consolidacion" class="form-check-label">Listar en consolidación</label>
            </div>
          </div>
        </div>

        {{-- === FILA 6 === --}}
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="default" name="default" value="1"
                {{ old('default') ? 'checked' : '' }}>
              <label for="default" class="form-check-label">Establecer como predeterminado</label>
            </div>
          </div>
        </div>

        {{-- === BOTONES === --}}
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a href="{{ route('tipo-usuario.listar') }}" class="btn btn-secondary">Cancelar</a>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection