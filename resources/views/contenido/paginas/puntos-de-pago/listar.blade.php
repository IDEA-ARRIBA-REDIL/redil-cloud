@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Puntos de pago')

<!-- Page -->
@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/swiper/swiper.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/moment/moment.js',
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
        }else if(this.value.length == 0)
        {
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
        if(fieldAux)
        {
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

<h4 class=" mb-1 fw-semibold text-primary"> Puntos de pago</h4>

@include('layouts.status-msn')

<div class="row pt-5">
    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
      <div class="swiper-wrapper">

          <div class="swiper-slide">
            <div class="row equal-height-row  g-2">
              <!-- Todos -->
              <div class="col equal-height-col col-lg-3 col-12">
                <a href="{{  route('puntosDePago.listado', $tipo = 'todos') }}">
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
                <a href="{{  route('puntosDePago.listado', $tipo = 'baja') }}">
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

  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('puntosDePago.listado', ['tipo' => request('tipo')]) }}">
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
        <span class="text-black me-5">{{ $puntosDePago->total() > 1 ? $puntosDePago->total() . ' Puntos de pago' : $puntosDePago->total() . ' Punto de pago' }}</span>
        @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
          @foreach($tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($bandera == 1)
            <a type="button" href="{{ route('puntosDePago.listado', ['tipo' => request('tipo')]) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>
    </div>
  </form>



 <!-- lista de puntos de pago-->
  <div class="row g-4 mt-1">
    @foreach($puntosDePago as $puntoDePago)
    <div class="col-12 col-xl-4 col-md-6">

      <div class="card ">
        <div class="card-header pb-2">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-start">
              <div class="me-2 mt-1">
                <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $puntoDePago->nombre }}</h5>
              </div>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">

                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">

          <div class="d-flex flex-row justify-content-between mb-2">
            <div class="d-flex flex-row">
              <i class="ti ti-building text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Sede:</small>
                <small class="fw-semibold ms-1 text-black ">{{ $puntoDePago->sede->nombre }}</small>
              </div>
            </div>
          </div>

          <div class="d-flex flex-row justify-content-between mb-2">
            <div class="d-flex flex-row">
              <i class="ti ti-cash-register text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Cajas:</small>
                <small class="fw-semibold ms-1 text-black ">{{ $puntoDePago->cajas->count() }}</small>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
    @endforeach
  </div>
  <!--/ lista de puntos de pago -->

  <div class="row my-3">
    @if($puntosDePago)
    <p> {{$puntosDePago->lastItem()}} <b>de</b> {{$puntosDePago->total()}} <b>puntos de pago - Página</b> {{ $puntosDePago->currentPage() }} </p>
    {!! $puntosDePago->appends(request()->input())->links() !!}
    @endif
  </div>

  <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('puntosDePago.listado', ['tipo' => request('tipo')]) }}">
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
              Filtros
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
          <div class="row">

            <div class="col-12 mb-3">
              <label for="buscar" class="form-label">Por palabra</label>
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
