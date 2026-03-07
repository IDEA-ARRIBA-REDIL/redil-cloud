@php
$configData = Helper::appClasses();
use App\Models\Sede;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reportes de reunion')

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
      $("#busquedaAvanzada").submit();
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
      // Seleccionamos todos los elementos que se pueden colapsar
      const collapseElements = document.querySelectorAll('.card-body.collapse');

      collapseElements.forEach(function (collapseEl) {
          // Escuchamos el evento que Bootstrap dispara ANTES de empezar a MOSTRAR el contenido
          collapseEl.addEventListener('show.bs.collapse', function () {
              // Buscamos el botón que controla este div en específico
              const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
              if (triggerButton) {
                  const icon = triggerButton.querySelector('span.ti');
                  // Cambiamos el ícono a 'menos'
                  icon.classList.remove('ti-plus');
                  icon.classList.add('ti-minus');
              }
          });

          // Escuchamos el evento que Bootstrap dispara ANTES de empezar a OCULTAR el contenido
          collapseEl.addEventListener('hide.bs.collapse', function () {
              const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
              if (triggerButton) {
                  const icon = triggerButton.querySelector('span.ti');
                  // Cambiamos el ícono a 'más'
                  icon.classList.remove('ti-minus');
                  icon.classList.add('ti-plus');
              }
          });
      });
  });
</script>
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Reportes de reunión</h4>

@include('layouts.status-msn')


  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('reporteReunion.lista') }}">
    <div class="row mt-5">

      <!-- Filtro por rango de fechas -->
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <span class="input-group-text"><i class="ti ti-calendar"></i></span>
          <input type="text" id="filtroFechaIni" name="filtroFechaIni" class="form-control d-none" placeholder="">
          <input type="text" id="filtroFechaFin" name="filtroFechaFin" class="form-control d-none" placeholder="">
          <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
        </div>
      </div>

      <div class="col-3 col-md-8 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5  me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i> </button>
      </div>

      <div class="filter-tags py-3">
        <span class="text-black me-5">{{ $reportes->total() > 1 ? $reportes->total().' Reportes' : $reportes->total().' Reporte' }} </span>
        @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
          @foreach($tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($bandera == 1)
            <a type="button" href="{{ route('reporteReunion.lista') }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>

    </div>
  </form>


  <!-- lista de reportes -->
  <div class="row equal-height-row g-4 mt-1">

    @if(count($reportes)>0)
      @foreach($reportes as $reporte)
        <div class=" col equal-height-col col-12 ">
            <div class="card rounded-3 shadow">
                <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
                  <div class="flex-fill row">

                    <div class="col-2 col-md-3">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Cod:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->id }}</small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Reunión:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->reunion?->nombre ?? 'Reunión Eliminada' }}</small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="d-flex flex-row">
                        <i class="ti ti-calendar text-black"></i>
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Fecha y hora:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->fecha }}, {{ Carbon\Carbon::parse($reporte->hora)->format('g:i a') }} </small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <div class="d-flex flex-row">
                        <i class="ti ti-building text-black"></i>
                        <div class="d-flex flex-column">
                          <small class="text-black ms-1">Sede:</small>
                          <small class="fw-semibold ms-1 text-black ">{{ $reporte->reunion?->sede?->nombre ?? 'Sede no encontrada' }}</small>
                        </div>
                      </div>
                    </div>

                  </div>

                  <div class="">
                    <div class="ms-auto">
                      <div class="dropdown zindex-2 p-1 float-end">
                        <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect"  data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_ver_perfil_reporte_reunion'))
                            <li><a class="dropdown-item" href="">Resumen </a></li>
                          @endif

                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_modificar_reporte_reunion'))
                            <li><a class="dropdown-item" href="{{route('reporteReunion.editar', $reporte)}}">Editar </a></li>
                          @endif

                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_anadir_asistentes_reporte_reunion'))
                            @if($reporte->puedeAñadirAsistentes())
                              <li><a class="dropdown-item" href="{{route('reporteReunion.añadirAsistentes', $reporte)}}"> Añadir asistentes </a></li>
                            @endif
                          @endif

                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_anadir_asistentes_reservas_reunion'))
                            @if($reporte->habilitar_reserva && $reporte->puedeAñadirReservas())
                              <li><a class="dropdown-item" href="{{route('reporteReunion.añadirReservas', $reporte)}}"> Añadir reservas </a></li>
                              <li><a class="dropdown-item" href="{{route('reporteReunion.compartirLinkReserva', $reporte)}}">Compartir link de reserva</a></li>
                            @endif
                          @endif

                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_descargar_informe_servidores_reporte_reunion'))
                          <li><a class="dropdown-item" href=""> Descargar informe servidores</a></li>
                          @endif

                          @if($reporte->habilitar_reserva && $rolActivo->hasPermissionTo('reporte_reuniones.opcion_descargar_informe_reservas_reporte_reunion'))
                          <li><a class="dropdown-item" href="{{ route('reporteReunion.exportarReservasExcel', ['reporteReunionId' => $reporte->id]) }}"> Descargar informe de reservas</a></li>
                          @endif

                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_descargar_informe_asistencias_reporte_reunion'))
                          <li><a class="dropdown-item" href="{{ route('reporteReunion.exportarAsistenciasExcel', ['reporteReunionId' => $reporte->id]) }}"> Descargar informe de asistencias</a></li>
                          @endif

                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_descargar_informe_visualizaciones_reporte_reunion'))
                          <li><a class="dropdown-item" href=""> Descargar informe visualizaciones</a></li>
                          @endif

                          <hr class="dropdown-divider">
                          @if($rolActivo->hasPermissionTo('reporte_reuniones.opcion_eliminar_reporte_reunion'))
                            <li><a class="dropdown-item text-danger" href="javascript:void(0);" data-nombre="" data-id="{{$reporte->id}}" onclick='confirmarEliminacion(@json($reporte))'>Eliminar </a></li>
                          @endif
                        </ul>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="card-body p-4 collapse" id="cardBodyReporte{{ $reporte->id }}">
                    <div class="row">
                      <div class="col-12 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">Aforo:</small>
                            <small class="fw-semibold ms-1 text-black ">{{ $reporte->aforo }}</small>
                          </div>
                        </div>
                      </div>

                      <div class="col-6 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">Total asistencias</small>
                            <small class="fw-semibold ms-1 text-black ">{{$reporte->cantidad_asistencias}}</small>
                          </div>
                        </div>
                      </div>

                      <div class="col-6 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">¿Habilitado reserva?</small>
                            <small class="fw-semibold ms-1 text-black ">{{$reporte->habilitar_reserva ? 'Si' : 'No'}}</small>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-md-3">
                        <div class="d-flex flex-row">
                          <div class="d-flex flex-column">
                            <small class="text-black ms-1">¿Habilitado reserva invitados?</small>
                            <small class="fw-semibold ms-1 text-black ">{{$reporte->habilitar_reserva_invitados ? 'Si' : 'No'}}</small>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>

                <div class="card-footer border-top p-1">
                    <div class="d-flex justify-content-center">
                        <button type="button"
                                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                                data-bs-toggle="collapse"
                                data-bs-target="#cardBodyReporte{{ $reporte->id }}">
                            <span class="ti ti-plus"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
      @endforeach
    @else
    <div class="mt-5 mb-5 py-5">
      <center>
      <i class="ti ti-user ti-xl"></i>
      <p>La busqueda no arrojo ningun resultado.</p>
      </center>
    </div>
    @endif

  </div>
  <!--/ lista de reportes -->

  <div class="row my-3 text-black">
    @if($reportes)
    <p> {{$reportes->lastItem()}} <b>de</b> {{$reportes->total()}} <b>reportes - Página</b> {{ $reportes->currentPage() }} </p>
    {!! $reportes->appends(request()->input())->links() !!}
    @endif
  </div>

  <form id="eliminacion" method="POST" action="">
    @csrf
    @method('DELETE')
  </form>

  <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('reporteReunion.lista') }}">
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
              Filtros
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
          <div class="row">

            <input type="text" id="filtroFechaIni2" name="filtroFechaIni" class="form-control d-none" placeholder="">
            <input type="text" id="filtroFechaFin2" name="filtroFechaFin" class="form-control d-none" placeholder="">

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
              <select id="sedes" name="sedes_id[]" class="select2 form-select" data-allow-clear="true" data-placeholder="Seleccione las sedes" multiple>
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
  </form>

@endsection
