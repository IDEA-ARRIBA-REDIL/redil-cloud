@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar tipo de actividad')

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

@section('content')
<h4 class="fw-semibold text-primary mb-1">Editar tipo de actividad: {{ $tipoActividad->nombre }}</h4>
<p class="mb-4 text-black">Modifica los parámetros de configuración para este tipo de actividad.</p>

@include('layouts.status-msn')

<form id="formulario" action="{{ route('gestionar-tipos-de-actividad.actualizar', $tipoActividad->id) }}" method="POST">
  @csrf
  @method('PATCH')

  <div class="row">
    <!-- Columna Izquierda: Información Principal y Configuraciones -->
    <div class="col-12">
      <!-- Card: Información Principal -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Información principal</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-8 col-12 mb-3">
              <label class="form-label" for="nombre">Nombre</label>
              <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $tipoActividad->nombre) }}" placeholder="Ej: Escuela de Líderes" required />
              @error('nombre')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4 col-12 mb-3">
              <label class="form-label" for="color">Color representativo</label>
              <input type="color" id="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', $tipoActividad->color ?? '#666CE8') }}" title="Elige un color" />
              @error('color')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-12">
              <label class="form-label" for="descripcion">Descripción</label>
              <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Escribe una breve descripción..." required>{{ old('descripcion', $tipoActividad->descripcion) }}</textarea>
              @error('descripcion')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Card: Configuraciones y restricciones -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Configuraciones y restricciones</h5>
        <div class="card-body">
          <div class="row g-3">
            @php
              $configuraciones = [
                ['id' => 'requiere_inscripcion', 'label' => 'Requiere inscripción'],
                ['id' => 'requiere_inicio_sesion', 'label' => 'Requiere inicio de sesión'],
                ['id' => 'es_gratuita', 'label' => 'Es gratuita'],
                ['id' => 'permite_abonos', 'label' => 'Permite abonos'],
                ['id' => 'tipo_escuelas', 'label' => 'Tipo escuelas (LMS)'],
                ['id' => 'inscripcion_parientes', 'label' => 'Permitir inscripción de parientes'],
              ];
            @endphp
            @foreach($configuraciones as $config)
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="{{ $config['id'] }}" name="{{ $config['id'] }}" {{ old($config['id'], $tipoActividad->{$config['id']}) ? 'checked' : '' }}>
                <label class="form-check-label fw-medium text-black" for="{{ $config['id'] }}">{{ $config['label'] }}</label>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- Columna Derecha: Compras e Inscripciones, y Restricciones de Edad -->
    <div class="col-12">
      <!-- Card: Compras e inscripciones -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Compras e inscripciones</h5>
        <div class="card-body">
          <div class="row g-3">
            @php
              $compras = [
                ['id' => 'unica_compra', 'label' => 'Única compra'],
                ['id' => 'multiples_compras', 'label' => 'Múltiples compras'],
                ['id' => 'unica_inscripcion', 'label' => 'Única inscripción'],
                ['id' => 'multiples_inscripciones', 'label' => 'Múltiples inscripciones'],
              ];
            @endphp
            @foreach($compras as $compra)
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="{{ $compra['id'] }}" name="{{ $compra['id'] }}" {{ old($compra['id'], $tipoActividad->{$compra['id']}) ? 'checked' : '' }}>
                <label class="form-check-label fw-medium text-black" for="{{ $compra['id'] }}">{{ $compra['label'] }}</label>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Card: Restricciones de edad -->
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">Restricciones de edad</h5>
        <div class="card-body">
          <div class="row g-3">
            @php
              $restricciones = [
                ['id' => 'aplicar_restriccion_menores', 'label' => 'Aplicar restricción de menores'],
                ['id' => 'solo_menores_de_edad', 'label' => 'Solo para menores de edad'],
              ];
            @endphp
            @foreach($restricciones as $restriccion)
            <div class="col-12 col-md-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="{{ $restriccion['id'] }}" name="{{ $restriccion['id'] }}" {{ old($restriccion['id'], $tipoActividad->{$restriccion['id']}) ? 'checked' : '' }}>
                <label class="form-check-label fw-medium text-black" for="{{ $restriccion['id'] }}">{{ $restriccion['label'] }}</label>
              </div>
            </div>
            @endforeach
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
