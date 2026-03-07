@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection
@section('page-script')
@section('page-script')
<script type="module">

  // 1. CREA UNA FUNCIÓN QUE CONTENGA TODA TU LÓGICA
  function inicializarFormulario() {
    console.log("Inicializando formulario DESPUÉS de Livewire...");

    // Tu código de Flatpickr
    $(".fecha-picker").flatpickr({
      dateFormat: "Y-m-d"
    });

    // Tu código de Select2
    $('.select2').select2({
      width: '100px', // Nota: considera usar '100%' para que sea responsive
      allowClear: true,
      placeholder: 'Ninguno'
    });

    // Tu código de input numérico
    document.querySelectorAll('input[type="number"]').forEach(input => {
      input.addEventListener('input', () => {
        if (input.value < 0) input.value = 0;
      });
    });

    // Tu lógica para mostrar/ocultar
    const tipoPersonaSelect = $('#tipoPersona');
    const containerAsistente = $('#container-asistente');
    const containerNombre = $('#container-nombre');

    function actualizarVisibilidadPersona() {
      const tipoSeleccionado = tipoPersonaSelect.val();
      if (tipoSeleccionado === '1') {
        containerAsistente.removeClass('d-none');
        containerNombre.addClass('d-none');
      } else if (tipoSeleccionado === '2') {
        containerAsistente.addClass('d-none');
        containerNombre.removeClass('d-none');
      } else {
        containerAsistente.addClass('d-none');
        containerNombre.addClass('d-none');
      }
    }

    // Ejecútalo una vez para el estado inicial
    actualizarVisibilidadPersona();

    // Y pon el listener para cambios futuros
    tipoPersonaSelect.on('change', actualizarVisibilidadPersona);
  }


  // 2. ESCUCHA LA SEÑAL DE LIVEWIRE Y LUEGO EJECUTA TU FUNCIÓN
  document.addEventListener('livewire:initialized', () => {
    inicializarFormulario();
  });


  // Tu otro script para el 'submit' puede quedar como está
  $('#formulario').submit(function() {
    $('.btnGuardar').attr('disabled', 'disabled');

    Swal.fire({
      title: "Espera un momento",
      text: "Ya estamos guardando...",
      icon: "info",
      showCancelButton: false,
      showConfirmButton: false,
      showDenyButton: false
    });
  });
</script>

@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Crear ingreso</h4>
<p class="mb-4 text-black">Registra tus ingresos financieros.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('finanzas.crearIngreso') }}"  enctype="multipart/form-data">
  @csrf

  <div class="card mb-4">
    <h5 class="card-header text-black fw-semibold">
      Información principal
    </h5>

    <div class="card-body">
      <div class="row">

        <div wire:ignore class="col-12 col-md-4 mb-3" wire:ignore>
          <label class="form-label">Fecha</label>
          <input type="text" value="{{ old('fecha') }}" id='fecha' name="fecha" placeholder="Fecha del ingreso" class="fecha form-control fecha-picker">
            @if ($errors->has('fecha'))<div class="text-danger form-label">{{ $errors->first('fecha') }}</div>@endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="valor" class="form-label">Valor</label>
          <input type="number" min="0" name="valor" value="{{ old('valor') }}" class="form-control"
            id="valor" placeholder="Valor" aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('valor'))
          <div class="text-danger form-label">{{ $errors->first('valor') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Monedas</label>
          <select id="continente" name="moneda" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione una moneda" data-allow-clear="true">
            <option value="">Seleccione una moneda</option>
            @foreach ($monedas as $moneda)
            <option value="{{ $moneda->id }}" {{ old('moneda') == $moneda->id ? 'selected' : '' }}>{{ $moneda->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('moneda'))
          <div class="text-danger form-label">{{ $errors->first('moneda') }}</div>
          @endif
        </div>


        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Tipo de ofrenda</label>
          <select id="continente" name="tipoOfrenda" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un tipo de ofrenda" data-allow-clear="true">
            <option value="">Seleccione un tipo de ofrenda</option>
            @foreach ($ofrendas as $ofrenda)
            <option value="{{ $ofrenda->id }}" {{ old('tipoOfrenda') == $ofrenda->id ? 'selected' : '' }}>{{ $ofrenda->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('tipoOfrenda'))
          <div class="text-danger form-label">{{ $errors->first('tipoOfrenda') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-8 mb-3">
          <label class="form-label" for="payment-note">Descrición</label>
          <textarea class="form-control" name="descripcion" id="descripcion" rows="2">{{ old('descripcion') }}</textarea>
          @if ($errors->has('descripcion'))
          <div class="text-danger form-label">{{ $errors->first('descripcion') }}</div>
          @endif
        </div>



        <div class="col-12 col-md-4 mb-3" wire:ignore>
          <label class="form-label">Tipo de persona</label>
          <select id="tipoPersona" name="tipoPersona" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un tipo de persona" data-allow-clear="true">
            <option value="">Seleccione un tipo de persona</option>
            <option {{ old('tipoPersona', '1') == '1' ? 'selected' : '' }} value="1">Persona interna</option>
            <option {{ old('tipoPersona') == '2' ? 'selected' : '' }} value="2">Persona externa</option>
          </select>

          @if ($errors->has('tipoPersona'))
          <div class="text-danger form-label">{{ $errors->first('tipoPersona') }}</div>
          @endif
        </div>

        <!-- asistente ingreso -->
        <div id="container-nombre" class="@if ($tipoPersona == true) d-none @endif col-4 mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" id="nombre"
            placeholder="Nombre" aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('nombre'))<div class="text-danger form-label">{{ $errors->first('nombre') }}</div>@endif
        </div>
        <!-- asistente ingreso -->


            @livewire('Usuarios.usuarios-para-busqueda', [
            'id' => 'persona',
            'tipoBuscador' => 'unico',
            'conDadosDeBaja' => 'no',
            'class' => 'col-12 col-md-4 mb-3',
            'label' => 'Selecciona un usuario',
            'placeholder' => 'Seleccione un usuario',
            'queUsuariosCargar' => 'todos',
            'modulo' => 'asistente-ingreso',
            'estiloSeleccion' => 'pequeno',
            'usuarioSeleccionadoId' => old('persona') ?  old('persona') : ''
            ])

        <div class="col-12 col-md-4 mb-3">
          <label for="identificacion" class="form-label">Identificación</label>
          <input type="text" name="identificacion" value="{{ old('identificacion') }}" class="form-control"
            id="identificacion" placeholder="Identificación"
            aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('identificacion'))
          <div class="text-danger form-label">{{ $errors->first('identificacion') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Tipo de identificación</label>
          <select id="tipoIdentificacion" name="tipoIdentificacion" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un tipo de persona" data-allow-clear="true">
            <option value="">Seleccione un tipo de persona</option>
            @foreach ($identificaciones as $identificacion)
            <option value="{{ $identificacion->id }}" {{ old('tipoIdentificacion') == $identificacion->id ? 'selected' : '' }}>{{ $identificacion->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('tipoIdentificacion'))
          <div class="text-danger form-label">{{ $errors->first('tipoIdentificacion') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3" wire:key="telefono-field" wire:ignore>
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="number" min="0" name="telefono" value="{{ old('telefono') }}" class="form-control"
            id="telefono" placeholder="Teléfono" aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('telefono'))
          <div class="text-danger form-label">{{ $errors->first('telefono') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="direccion" class="form-label">Dirección</label>
          <input type="text" name="direccion" value="{{ old('direccion') }}" class="form-control"
            id="direccion" placeholder="Dirección" aria-describedby="defaultFormControlHelp" />
          @if ($errors->has('direccion'))
          <div class="text-danger form-label">{{ $errors->first('direccion') }}</div>
          @endif
        </div>


        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Sede</label>
          <select id="sede" name="sede" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione una sede" data-allow-clear="true">
            <option value="">Seleccione una sede</option>
            @foreach ($sedes as $sede)
            <option value="{{ $sede->id }}" {{ old('sede') == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('sede'))
          <div class="text-danger form-label">{{ $errors->first('sede') }}</div>
          @endif
        </div>


        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Caja</label>
          <select id="continente" name="caja" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione una caja" data-allow-clear="true">
            <option value="">Seleccione una caja</option>
            @foreach ($cajas as $caja)
            <option value="{{ $caja->id }}" {{ old('caja') == $caja->id ? 'selected' : '' }}>{{ $caja->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('caja'))
          <div class="text-danger form-label">{{ $errors->first('caja') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label class="form-label">Centro de costos</label>
          <select id="centro_de_costos_ingresos" name="centro_de_costos_ingresos" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un tipo de persona" data-allow-clear="true">
            <option value="">Seleccione un centro de costo</option>
            @foreach ($centroDeCostosIngresos as $centro)
            <option value="{{ $centro->id }}" {{ old('centro_de_costos_ingresos') == $centro->id ? 'selected' : '' }}>{{ $centro->nombre }}</option>
            @endforeach
          </select>
          @if ($errors->has('centro_de_costos_ingresos'))
          <div class="text-danger form-label">{{ $errors->first('centro_de_costos_ingresos') }}</div>
          @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="campoAdiciona1" class="form-label">Campo adicional 1</label>
          <input type="text" name="campoAdicional1" value="{{ old('campoAdicional1') }}" class="form-control"
            id="campoAdiciona1" placeholder="Campo adicional 1"
            aria-describedby="defaultFormControlHelp" />
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="campoAdiciona2" class="form-label">Campo adicional 2</label>
          <input type="text" name="campoAdicional2" value="{{ old('campoAdicional2') }}" class="form-control"
            id="campoAdiciona2" placeholder="Campo adicional 2"
            aria-describedby="defaultFormControlHelp" />
        </div>

      </div>
    </div>
  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >
        <span class="align-middle me-sm-1 me-0 ">Guardar</span>
      </button>
    </div>
  </div>
  <!-- /botonera -->

</form>
@endsection
