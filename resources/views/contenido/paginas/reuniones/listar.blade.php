@php

$configData = Helper::appClasses();
use App\Models\Sede;

@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reuniones')

<!-- Page -->
@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/swiper/swiper.scss',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')

<script type="module">
  $(document).ready(function() {
    $('.select2').select2({
      dropdownParent: $('#modalBusquedaAvanzada')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalBusquedaAvanzada').on('scroll', function(event) {
    $(this).find(".select2").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });
</script>

<script>
  function confirmacionBajaAlta(reunionId, tipo) {
    Livewire.dispatch('confirmacionBajaAlta', {
      reunionId: reunionId,
      tipo: tipo
    });
  }

  function comprobarSiTieneRegistros(reunionId) {
    Livewire.dispatch('comprobarSiTieneRegistros', {
      reunionId: reunionId
    });
  }

  function eliminacionForzada(reunionId) {
    Livewire.dispatch('confirmarEliminacion', {
      reunionId: reunionId
    });
  }
</script>

<script>
  const buscarInput = document.getElementById('buscar');
  const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
  const formularioBuscar = document.getElementById('formBuscar');
  let timeoutId;
  const delay = 1000; // Tiempo en milisegundos después de dejar de escribir para enviar el formulario

  buscarInput.addEventListener('input', function() {
    clearTimeout(timeoutId); // Limpiar cualquier timeout anterior

    if (this.value.length >= 3) {
      timeoutId = setTimeout(() => {
        formularioBuscar.submit();
      }, delay);
    } else if (this.value.length == 0) {
      formularioBuscar.submit();
    }
  });

  btnBorrarBusquedaPorPalabra.addEventListener('click', function() {
    buscarInput.value = "";
    formularioBuscar.submit();
  });
</script>

<script>
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {
      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;

      const form = document.getElementById('busquedaAvanzada');
      const input = form.querySelector('[id="' + field + '"]');

      if (input && $(input).hasClass('select2BusquedaAvanzada')) {
        // Si es un Select2, usa el método 'val' de Select2 para eliminar la opción
        let currentValues = $(input).val();
        if (Array.isArray(currentValues)) {
          // Si es un select múltiple
          const newValue = currentValues.filter(v => v != value);
          $(input).val(newValue).trigger('change');
        } else {
          // Si es un select simple
          $(input).val(null).trigger('change');
        }
      } else if (input && input.tagName === 'SELECT' && input.multiple) {
        // Si es un select múltiple nativo (poco probable con Select2, pero por si acaso)
        let currentValues = Array.from(input.selectedOptions).map(option => option.value);
        const newValue = currentValues.filter(v => v != value);
        for (let i = 0; i < input.options.length; i++) {
          input.options[i].selected = newValue.includes(input.options[i].value);
        }
        $(input).trigger('change'); // Dispara el evento change para otras posibles escuchas*/
      } else if (input && input.tagName === 'SELECT') {
        // Si es un select simple nativo
        input.value = '';
      } else if (input) {
        // Si es un input normal
        input.value = '';
        if (fieldAux) {
          const inputAux = form.querySelector('[id="' + fieldAux + '"]');
          inputAux.value = '';
        }
      }

      form.submit();
    });
  });
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Reuniones</h4>

@include('layouts.status-msn')

<div class="row pt-5">
  <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
    <div class="swiper-wrapper">

      <div class="swiper-slide">
        <div class="row equal-height-row  g-2">
          <!-- Todos -->
          <div class="col equal-height-col col-lg-3 col-12">
            <a href="{{  route('reuniones.lista', $tipo = 'todos') }}">
              <div class="h-100 card border rounded-3 shadow-sm">
                <div class="card-body d-flex flex-row p-3">

                  <div class="card-icon me-1 ">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/icono_indicador.png') : Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/icono_indicador.png') }}" alt="icono" class="me-2" width="50">
                  </div>

                  <div class="card-title mb-0 lh-sm">
                    <p class="text-black mb-0" style="font-size: .8125rem">Todas</p>
                    <h5 class="mb-0 me-2">{{ $contadorTodos }}</h5>
                  </div>

                </div>
              </div>
            </a>
          </div>
          <!--/ Todos -->

          <!-- Dados de baja -->
          <div class="col equal-height-col col-lg-3 col-12">
            <a href="{{  route('reuniones.lista', $tipo = 'baja') }}">
              <div class="h-100 card border rounded-3 shadow-sm">
                <div class="card-body d-flex flex-row p-3">

                  <div class="card-icon me-1 ">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/icono_indicador.png') : Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/icono_indicador.png') }}" alt="icono" class="me-2" width="50">
                  </div>

                  <div class="card-title mb-0 lh-sm">
                    <p class="text-black mb-0" style="font-size: .8125rem">Dados de baja</p>
                    <h5 class="mb-0 me-2">{{ $contadorBaja }}</h5>
                  </div>

                </div>
              </div>
            </a>
          </div>
          <!--/ Dados de baja -->
        </div>
      </div>
    </div>
    <div class="d-flex mt-5">
      <div class="swiper-pagination"></div>
    </div>
  </div>
</div>

<hr>

<form id="formBuscar" class="forms-sample" method="GET" action="{{ route('reuniones.lista', ['tipo' => request('tipo')]) }}">
  <div class="row mt-5">
    <div class="col-9 col-md-4">
      <div class="input-group input-group-merge bg-white">
        <input id="buscar" name="buscar" type="text" value="{{$buscar}}" class="form-control" placeholder="Busqueda..." aria-describedby="btnBusqueda">
        @if($buscar)
        <span id="borrarBusquedaPorPalabra" class="input-group-text"><i class="ti ti-x"></i></span>
        @else
        <span class="input-group-text"><i class="ti ti-search"></i></span>
        @endif
      </div>
    </div>
    <div class="col-3 col-md-8 d-flex justify-content-end">
      <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5  me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i> </button>
    </div>

    <div class="filter-tags py-3">
      <span class="text-black me-5">{{ $reuniones->total() > 1 ? $reuniones->total() . ' Reuniones' : $reuniones->total() . ' Reunión' }}</span>
      @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
      @foreach($tagsBusqueda as $tag)
      <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
        <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
      </button>
      @endforeach
      @if($bandera == 1)
      <a type="button" href="{{ route('reuniones.lista', ['tipo' => request('tipo')]) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
        <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
      </a>
      @endif
      @endif
    </div>
  </div>
</form>


<form id="eliminarReunion" method="POST" action="">
  @csrf @method('DELETE')
</form>
<form id="darBaja" method="POST" action="">
  @csrf @method('DELETE')
</form>

<!-- lista de reuniones -->
<div class="row g-4 mt-1">
  @foreach($reuniones as $reunion)

  <div class="col-12 col-xl-4 col-md-6">
    <div class="card ">
       <img class="card-img-top object-fit-cover" style="height: 130px;"  src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/'.$reunion->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/reuniones/'.$reunion->portada)}}" alt="Card imagen " />

      <div class="card-header">
        <div class="d-flex justify-content-between">
          <div class="d-flex align-items-start">
            <div class="me-2 mt-1">
              <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $reunion->nombre }}</h5>
            </div>
          </div>
          <div class="ms-auto">
            <div class="dropdown zindex-2 p-1 float-end">
              <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                @if ($rolActivo->hasPermissionTo('reuniones.opcion_modificar_reunion') && $reunion->trashed()==FALSE)
                <li><a class="dropdown-item" href="{{ route('reuniones.editar', $reunion) }}">Editar</a></li>
                @endif

                @if ($rolActivo->hasPermissionTo('reuniones.opcion_dar_de_baja_alta_reunion') && $reunion->trashed()==TRUE)
                <li><a class="dropdown-item confirmacionDarBaja" href="javascript:void(0);" data-id="{{ $reunion->id }}" onclick="confirmacionBajaAlta('{{ $reunion->id }}', 'alta')">Dar de alta</a></li>
                @endif
                <hr class="dropdown-divider">
                @if ($rolActivo->hasPermissionTo('reuniones.opcion_dar_de_baja_alta_reunion') && $reunion->trashed()==FALSE)
                <li><a class="dropdown-item confirmacionDarBaja text-danger" href="javascript:void(0);" data-id="{{ $reunion->id }}" onclick="confirmacionBajaAlta('{{ $reunion->id }}', 'baja')">Dar de baja</a></li>
                @endif

                @if ($rolActivo->hasPermissionTo('reuniones.opcion_eliminar_reunion') && $reunion->trashed()!=TRUE)
                <li><a class="dropdown-item confirmacionEliminar text-danger" href="javascript:void(0);" data-id="{{ $reunion->id }}" onclick="comprobarSiTieneRegistros('{{ $reunion->id }}')">Eliminar</a></li>
                @endif

              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body">

        <div class="d-flex flex-column mb-3">

          <div class="d-flex flex-row">
            <i class="ti ti-building-church text-black"></i>
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Sede</small>
              <small class="fw-semibold ms-1 text-black"> {{ $reunion->sede->nombre}}</small>
            </div>
          </div>

          <div class="d-flex flex-row">
            <i class="ti ti-calendar-clock text-black"></i>
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Hora de reunión</small>
              <small class="fw-semibold ms-1 text-black"> {{ Carbon\Carbon::parse($reunion->hora)->format('g:i a') }}</small>
            </div>
          </div>

          <div class="d-flex flex-row">
            <i class="ti ti-square-check text-black"></i>
            <div class="d-flex flex-column">
              <small class="text-black ms-1">¿Habilitado reserva?</small>
              <small class="fw-semibold ms-1 text-black"> {{$reunion->habilitar_reserva ? 'Si' : 'No'}}</small>
            </div>
          </div>

          <div class="d-flex flex-row">
            <i class="ti ti-square-check text-black"></i>
            <div class="d-flex flex-column">
              <small class="text-black ms-1">¿Asistencia solo con reservación?</small>
              <small class="fw-semibold ms-1 text-black"> {{ $reunion->habilitar_reserva ? ( $reunion->solo_reservados_pueden_asistir ? 'Si' : 'No') : 'No aplica'}}</small>
            </div>
          </div>

          <div class="d-flex flex-row">
            <i class="ti ti-users text-black"></i>
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Aforo</small>
              <small class="fw-semibold ms-1 text-black"> {{ $reunion->habilitar_reserva  ? $reunion->aforo : 'No aplica'}}</small>
            </div>
          </div>

        </div>

      </div>


        <div class="card-footer" style="background-color:#ededed!important">
          <div class="d-flex mt-3">
            @if($rolActivo->hasPermissionTo('reporte_reuniones.nuevo_reporte_reunion'))
              @if ($reunion->trashed())
                  {{-- Si la reunión está borrada, muestra el botón con la clase 'disabled' y sin funcionalidad --}}
                  <a class="btn btn-sm rounded-pill w-100 btn-primary disabled waves-effect waves-light mx-1 py-1 fw-light"
                    aria-disabled="true"
                    onclick="return false;"
                    href="#">
                      Crear reporte
                  </a>
              @else
                  {{-- Si la reunión está activa, muestra el enlace normal --}}
                  <a href="{{ route('reporteReunion.nuevo', $reunion->id) }}"
                    class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">
                      Crear reporte
                  </a>
              @endif
            @endif
          </div>
        </div>
    </div>
  </div>
  @endforeach
</div>
<!--/ lista de reuniones -->

<div class="row my-3">
  @if ($reuniones)
  <p> {{ $reuniones->lastItem() }} <b>de</b> {{ $reuniones->total() }} <b>reuniones - Página</b>
    {{ $reuniones->currentPage() }}
  </p>
  {!! $reuniones->appends(request()->input())->links() !!}
  @endif
</div>

@livewire('Reuniones.modal-dar-baja')

<!-- offcanvas busqueda avanzada -->
<form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('reuniones.lista', ['tipo' => request('tipo')]) }}">
  <div class="offcanvas offcanvas-end event-sidebar modalSelect2" tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
    <div class="offcanvas-header my-1 px-8">
      <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
        Filtros
      </h4>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-6 px-8">
      <div class="row">

        <div class="col-12 mb-3">
          <label for="nameBasic" class="form-label">Por palabra</label>
          <input id="buscar" name="buscar" type="text" value="{{$buscar}}" class="form-control" placeholder="Buscar por nombre, email, identificación">
        </div>

        <div class="col-12 mb-3">

          <label for="buscar" class="form-label">Por sede</label>
          <select id="selectSede" name="sede_id[]" class="select2 form-select" multiple
            data-allow-clear="true" data-placeholder="Seleccione una sede">
            @foreach ($sedes as $s)
            <option value="{{ $s->id }}"
              {{ in_array($s->id, request('sede_id', [])) ? 'selected' : '' }}>
              {{ $s->nombre }}
            </option>
            @endforeach
          </select>
        </div>

      </div>
    </div>
    <div class="offcanvas-footer p-5 border-top border-2 px-8">
      <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
      <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
    </div>
  </div>
</form>

@endsection
