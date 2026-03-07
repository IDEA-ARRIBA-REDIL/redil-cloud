@php
$configData = Helper::appClasses();
use App\Models\Sede;
@endphp

@extends('layouts/blankLayout')

@section('title', 'Iglesia virtual')

<!-- Page Styles -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js'])
@endsection



@section('page-script')
<script type="module">
  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno',
      dropdownParent: $('#modalBusquedaAvanzada')
    });
  });

  $(function() {
    //esta bandera impide que entre en un bucle cuando se ejecuta la funcion cb(start, end)
    let band=0;
    moment.locale('es');

    function cb(start, end) {

      $('#filtroFechaIni').val(start.format('YYYY-MM-DD'));
      $('#filtroFechaFin').val(end.format('YYYY-MM-DD'));

      $('#filtroFechaIni2').val(start.format('YYYY-MM-DD'));
      $('#filtroFechaFin2').val(end.format('YYYY-MM-DD'));

      $('#filtroFechas span').html(start.format('YYYY-MM-DD') + ' hasta ' + end.format('YYYY-MM-DD'));
      if(band==1)
      $("#formBuscar").submit();
      band=1;
    }

    //comprobamos si existe la fecha incio y fecha fin y creamos las fechas con el formato aceptado
    @if(isset($filtroFechaIni))
      var fecha_ini = moment('{{$filtroFechaIni}}');
      fecha_ini.format("YYYY-MM-DD");
    @endif

    @if(isset($filtroFechaFin))
      var fecha_fin = moment('{{$filtroFechaFin}}');
      fecha_fin.format("YYYY-MM-DD");
    @endif

    @if(isset($filtroFechaIni) && isset($filtroFechaFin))
      cb(fecha_ini, fecha_fin);
    @else
      cb(moment().startOf('month'), moment().endOf('month'));
    @endif

    $('#filtroFechas').daterangepicker({
        ranges: {
            'Hoy': [moment(), moment()],
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Mes actual': [moment().startOf('month'), moment().endOf('month')],
            'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Año actual': [moment().startOf('year'), moment().endOf('year')],
            'Año anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        },
        "locale": {
          "format": "YYYY-MM-DD",
          "separator": " hasta ",
          "applyLabel": "Aplicar",
          "cancelLabel": "Cancelar",
          "fromLabel": "Desde",
          "toLabel": "Hasta",
          "customRangeLabel": "Otro rango",
          "monthNames": JSON.parse(<?php print json_encode(json_encode($meses)); ?>),
          "firstDay": 1
        },
        @if(isset($filtroFechaIni))
        "startDate": fecha_ini,
        @endif
        @if(isset($filtroFechaIni))
        "endDate": fecha_fin,
        @endif
        showDropdowns: true
      }, cb);
  });
</script>

<script type="text/javascript">
  // SweetAlert para eliminar un reporteReunion
  function confirmarEliminacion(reporteReunion) {
    Swal.fire({
      title: '¿Estás seguro que deseas eliminar este reporte de reunión?',
      html: `<strong>${reporteReunion.titulo ?? 'Sin título'}</strong><br>Esta acción no es reversible.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('eliminacion');
        form.setAttribute('action', `/reporteReunion/${reporteReunion.id}/eliminar`);
        form.submit();
      }
    });
  }
</script>

<script>
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {
      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;

      const form = document.getElementById('formBuscar');
      const input = form.querySelector('[id="' + field + '"]');

      if (field === 'filtro_elegibles') {
        const switchInput = form.querySelector('#filtroElegibles');
        if (switchInput) {
          switchInput.checked = false;
        }
      } else if (input && $(input).hasClass('select2BusquedaAvanzada')) {
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalAuthInvitado = document.getElementById('modalAuthInvitado');
    const modalReservaInvitado = document.getElementById('modalReservaInvitado');

    if (modalAuthInvitado) {
        modalAuthInvitado.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const guestAllowed = button.getAttribute('data-invitado-permitido') === 'true';
            const reporteId = button.getAttribute('data-reporte-id');
            const reservaUrl = button.getAttribute('data-reserva-url');
            const loginRedirectUrl = button.getAttribute('data-login-redirect-url');


            const modalTitle = modalAuthInvitado.querySelector('#modalAuthTitle');
            const modalMessage = modalAuthInvitado.querySelector('#modalAuthMessage');
            const btnInvitado = modalAuthInvitado.querySelector('#btnModalInvitado');
            const btnLogin = modalAuthInvitado.querySelector('#btnModalLogin');

            if (guestAllowed) {
            modalTitle.textContent = '¿Cómo deseas continuar?';
            modalMessage.textContent = 'Puedes iniciar sesión para guardar la reserva en tu cuenta o continuar como invitado.';
            btnInvitado.href = reservaUrl;
            btnInvitado.style.display = 'inline-block';
            btnLogin.textContent = 'Iniciar sesión';
            btnLogin.href = `{{ route('login') }}?redirect=${encodeURIComponent(loginRedirectUrl)}`;
          } else {
            modalTitle.textContent = 'Inicio de sesión requerido';
            modalMessage.textContent = 'Para realizar la reserva en esta reunión, es necesario que inicies sesión con tu cuenta.';
            btnInvitado.style.display = 'none';
            btnLogin.textContent = 'Entendido, ir a Iniciar sesión';
            btnLogin.href = `{{ route('login') }}?redirect=${encodeURIComponent(loginRedirectUrl)}`;
          }


        });
    }

    if (modalReservaInvitado) {
        modalReservaInvitado.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const reporteId = button.getAttribute('data-reporte-id');
            const hiddenInput = modalReservaInvitado.querySelector('input[name="reporte_id"]');
            hiddenInput.value = reporteId;
        });
    }
});
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script>
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


@auth
  @include('layouts/sections/navbar/navbar')
@else
  @include('layouts/sections/navbar/navbar-front')
@endif

@section('content')
<div class="container-fluid" style="padding-top: 100px">
   <div class="row">
    <div class="col-12 col-lg-10 offset-lg-1">
      @include('layouts.status-msn')
      <h4 class=" mb-1 fw-semibold text-primary">Proximas reuniones</h4>

      <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('reporteReunion.proximasReuniones') }}">
        <div class="row mt-5">

          <!-- Filtro por rango de fechas -->
          <div class="col-9 col-md-6 mb-3">
            <div class="input-group input-group-merge bg-white">
              <span class="input-group-text"><i class="ti ti-calendar"></i></span>
              <input type="text" id="filtroFechaIni" name="filtroFechaIni" class="form-control d-none" placeholder="">
              <input type="text" id="filtroFechaFin" name="filtroFechaFin" class="form-control d-none" placeholder="">
              <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
            </div>
          </div>


          <div class="col-12 col-md-6 d-flex justify-content-end">
            <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i> </button>
          </div>

          <div class="filter-tags py-3">
            <span class="text-black me-5">{{ $reportes->total() > 1 ? $reportes->total().' Reuniones' : $reportes->total().' Reunión' }} </span>
            @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
              @foreach($tagsBusqueda as $tag)
                <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
                  <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
                </button>
              @endforeach
              @if($bandera == 1)
                <a type="button" href="{{ route('reporteReunion.proximasReuniones') }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
                  <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
                </a>
              @endif
            @endif
          </div>


          <!-- offcanvas busqueda avanzada -->
          <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
            <div class="offcanvas-header my-1 px-8">
                <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
                  Filtros
                </h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-6 px-8">
              <div class="row">



                <!-- Filtro por reunión -->
                <div class="col-12 mb-3">
                  <label class="form-label">Filtrar por reuniones</label>
                  <select id="reuniones_id" name="reuniones_id[]" class="select2 form-select" data-allow-clear="true" data-placeholder="Seleccione las reuniones" multiple>
                    <option></option>
                    @foreach ($reuniones as $reunion)
                    <option
                      value="{{ $reunion->id }}"
                      {{ in_array($reunion->id, old('reuniones_id', $reunionesFiltradas ?? [])) ? 'selected' : '' }}>
                      {{ $reunion->nombre }}
                    </option>
                    @endforeach
                  </select>
                </div>

                <!-- Filtro por sede -->
                <div class="col-12 mb-3">
                  <label class="form-label">Filtrar por sedes</label>
                  <select id="sedes_id" name="sedes_id[]" class="select2 form-select" data-allow-clear="true" data-placeholder="Seleccione las sedes" multiple>
                    <option></option>
                    @foreach ($sedes as $sede)
                    <option value="{{ $sede->id }}" {{ in_array($sede->id, old('sedes_id', $sedesFiltradas ?? [])) ? 'selected' : '' }}>
                      {{ $sede->nombre }}
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

        </div>
      </form>

      <!-- lista de reuniones -->
      <div class="row g-4 mt-1">
        @foreach($reportes as $reporte)
        <div class="col-12 col-xl-4 col-md-6">

          <div class="card ">
            <img class="card-img-top object-fit-cover" style="height: 130px;"  src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/reportes-reuniones/'.$reporte->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/reportes-reuniones/'.$reporte->portada)}}" alt="Card imagen " />
            <div class="card-header pb-2">
              <div class="d-flex justify-content-between">
                <div class="d-flex align-items-start">
                  <div class="me-2 mt-1">
                    <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $reporte->reunion->nombre }}</h5>
                    <div class="client-info fw-semibold text-black">{{ $reporte->reunion->sede->nombre }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-body row">

              <div class="col-6">

                <div class="d-flex flex-row">
                  <i class="ti ti-calendar text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Fecha</small>
                    <small class="fw-semibold ms-1 text-black"> {{ Carbon\Carbon::parse($reporte->fecha)->format('Y-m-d') }}</small>
                  </div>
                </div>

                <div class="d-flex flex-row">
                  <i class="ti ti-calendar-clock text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Hora de reunión</small>
                    <small class="fw-semibold ms-1 text-black"> {{ Carbon\Carbon::parse($reporte->reunion->hora)->format('g:i a') }}</small>
                  </div>
                </div>

              </div>

              <div class="col-6 mb-2">

                <div class="d-flex flex-row">
                  <i class="ti ti-calendar-event text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Día</small>
                    <small class="fw-semibold ms-1 text-black"> {{ Ucfirst(Carbon\Carbon::parse($reporte->fecha)->locale('es')->translatedFormat('l')) }}</small>
                  </div>
                </div>

                <div class="d-flex flex-row">
                  <i class="ti ti-users text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Cupos</small>
                    <small class="fw-semibold ms-1 text-black"> {{ $reporte->habilitar_reserva  ? $reporte->obtenerCantidadDisponible() : 'No aplica'}}</small>
                  </div>
                </div>

              </div>


            </div>

            <div class="card-footer" style="background-color:#ededed!important">
              <div class="d-flex mt-3">
                @php
                  $estaEnPlazo = $reporte->sePuedeReservar();
                  $hayCupos = $reporte->hayAforoDisponible();
                @endphp

                @auth


                 @if($reporte->tengoReservasEnEsteReporte())
                    <a href="{{ route('reporteReunion.resumenReserva', ['reporteReunion' => $reporte,'user' => auth()->user()]) }}" class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light" >Mis reservas </a>
                  @endif

                  @php
                      $usuarioCumpleRequisitos = $reporte->elUsuarioPuedeReservar();
                  @endphp

                  @if (!$estaEnPlazo)
                      {{-- CASO 1: La reserva está cerrada o fuera de fecha. --}}
                      <button disabled class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">
                          Reserva cerrada
                      </button>

                  @elseif (!$hayCupos)
                      {{-- CASO 2: No hay cupos disponibles. Esta es la segunda validación más importante. --}}
                      <button disabled class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">
                          No hay cupos
                      </button>

                  @elseif ($usuarioCumpleRequisitos)
                      {{-- CASO 3: El usuario cumple con todo (plazo, cupos y requisitos). Botón de acción principal. --}}
                      <a href="{{ route('reporteReunion.miReserva', $reporte->id)}}?origen=proximasReuniones" class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">
                          Hacer reserva
                      </a>

                  @else
                    <button disabled class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">
                      Hacer reserva
                    </button>
                  @endif

                @elseguest
                    {{-- =================================================== --}}
                    {{--  CASO 2: EL USUARIO ES UN INVITADO (NO LOGUEADO)    --}}
                    {{-- =================================================== --}}
                      @if (!$estaEnPlazo)
                        <button disabled class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">Reserva cerrada</button>
                      @elseif (!$hayCupos)
                        <button disabled class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">No hay cupos</button>
                      @else
                        @if ($reporte->habilitar_reserva_invitados)
                            {{-- Se permiten invitados: el botón abre un modal con dos opciones --}}
                            <button type="button"
                                    class="btn-reservar-guest btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAuthInvitado"
                                    data-login-redirect-url="{{ route('reporteReunion.miReserva', [$reporte->id, 'origen' => 'proximasReuniones']) }}"
                                    data-invitado-permitido="true"
                                    data-reporte-id = "{{ $reporte->id}} "
                                    data-reserva-url="{{ route('reporteReunion.miReserva', $reporte->id) }}?invitado=1&origen=proximasReuniones">
                                Hacer reserva
                            </button>
                        @else
                            {{-- NO se permiten invitados: el botón abre un modal que obliga a iniciar sesión --}}
                            <button type="button"
                                    class="btn-reservar-guest btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAuthInvitado"
                                    data-invitado-permitido="false">
                                Hacer reserva
                            </button>
                        @endif
                      @endif



                @endguest

              </div>
            </div>
          </div>
        </div>

        @endforeach
      </div>
      <!--/ lista de reuniones -->

      <div class="row my-3 text-black">
        @if($reportes)
        <p> {{$reportes->lastItem()}} <b>de</b> {{$reportes->total()}} <b>reportes - Página</b> {{ $reportes->currentPage() }} </p>
        {!! $reportes->appends(request()->input())->links() !!}
        @endif
      </div>
    </div>
  </div>
</div>




  <div class="modal fade" id="modalAuthInvitado" tabindex="-1" aria-labelledby="modalAuthLabel" aria-hidden="true" >
    <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
      <div class="modal-content p-0">
        <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
          <p class="text-black fw-semibold mb-0 modal-title" id="modalAuthTitle">Acceso Requerido</p>
          <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
        </div>

        <div class="modal-body px-5 py-8">

          <div class="row">

            @error('errorGeneral')
              <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  {{ $message }}
                </div>
              </div>
            @enderror

            <div class="col-12 mb-3">
              <small class="text-black" id="modalAuthMessage">¿Se realizó el grupo? </small>
            </div>

          </div>

        </div>

        <div class="modal-footer border-top p-5">
          <a id="btnModalInvitado" href="#"
            class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect"
            data-bs-toggle="modal"
            data-bs-target="#modalReservaInvitado">
              Reservar como invitado
          </a>
          <a id="btnModalLogin" href="#" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Iniciar sesión</a>
        </div>
      </div>
    </div>
  </div>

  <form id="formulario" action="{{ route('reporteReunion.reservarComoInvitado') }}" method="POST">
    @csrf
    <div class="modal fade" id="modalReservaInvitado" tabindex="-1" aria-labelledby="modalReservaInvitadoLabel" aria-hidden="true" >
      <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
          <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
            <p class="text-black fw-semibold mb-0 modal-title">Reserva como Invitado</p>
            <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
          </div>

          <div class="modal-body px-5 py-8">

            <div class="row">
              <input type="hidden" name="reporte_id" id="formReporteId" value="">

              <div class="mb-6">
                  <label for="nombreInvitado" class="form-label">Nombre del Invitado</label>
                  <input type="text" id="nombreInvitado" onkeypress="return sinComillas(event)" class="form-control @error('nombreInvitado') is-invalid @enderror" name="nombreInvitado" required placeholder="Ingrese el nombre completo">
                  @error('nombreInvitado') <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div> @enderror
              </div>

              <div class="mb-6">
                <label for="emailInvitado" class="form-label">Email</label>
                <input type="email" id="emailInvitado" class="form-control @error('emailInvitado') is-invalid @enderror" name="emailInvitado" required placeholder="Ingrese el correo electrónico">
                @error('emailInvitado') <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div> @enderror
              </div>

            </div>

          </div>

          <div class="modal-footer border-top p-5">
            <button type="button" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light btnGuardar" data-bs-dismiss="modal">Confirmar Reserva</button>
          </div>
        </div>
      </div>
    </div>
  </form>



@endsection
