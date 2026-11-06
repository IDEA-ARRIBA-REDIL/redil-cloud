@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Peticiones')

<!-- Page -->
@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
    'resources/assets/vendor/libs/swiper/swiper.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
    'resources/assets/vendor/libs/swiper/swiper.js'
  ])
@endsection

@section('page-script')
<script type="module">

  const swiperContainer = document.querySelector('#swiper-with-pagination-cards');
  const swiper = new Swiper(swiperContainer, {
    slidesPerView: "auto",
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });


  $(function() {
    //esta bandera impide que entre en un bucle cuando se ejecuta la funcion cb(start, end)
    let band=0;
    moment.locale('es');

    function cb(start, end) {

      $('#filtroFechaIni').val(start.format('YYYY-MM-DD'));
      $('#filtroFechaFin').val(end.format('YYYY-MM-DD'));

      $('#filtroFechas span').html(start.format('YYYY-MM-DD') + ' hasta ' + end.format('YYYY-MM-DD'));
      if(band==1)
      $("#filtro").submit();
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

<script type="module">

  $(document).ready(function() {
    $('#filtroTipoPeticiones').select2({
      placeholder: 'Filtrar por tipo de petición',
      dropdownParent: $('#modalBusquedaAvanzada')
    });
    $('#filtroFormularios').select2({
      placeholder: 'Filtrar por formulario',
      dropdownParent: $('#modalBusquedaAvanzada')
    });
    $('#filtroSedes').select2({
      placeholder: 'Filtrar por sedes',
      dropdownParent: $('#modalBusquedaAvanzada')
    });
  });


  $(document).ready(function() {
    $('.select2GeneradorExcel').select2({
      dropdownParent: $('#modalGeneradorExcel')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalGeneradorExcel').on('scroll', function(event) {
    $(this).find(".select2GeneradorExcel").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });

  $(".clearAllItems").click(function() {
    let value = $(this).data('select');
    $('#' + value).val(null).trigger('change');
  });

  $(".selectAllItems").click(function() {
    let value = $(this).data('select');
    $("#" + value + " > option").prop("selected", true);
    $("#" + value).trigger("change");
  });
</script>

<script type="text/javascript">
  function modalRespuesta(peticionId, personaId)
  {
    Livewire.dispatch('modalRespuesta', { peticionId: peticionId, personaId: personaId });
  }

  function modalSeguimiento(peticionId, personaId)
  {
    Livewire.dispatch('modalSeguimiento', { peticionId: peticionId, personaId: personaId });
  }
</script>

<script type="text/javascript">
  function confirmarEliminacionSeleccionadas(tipo)
  {
    const seleccionados = Array.from(document.querySelectorAll('.check-peticion:checked')).map(cb => cb.value);
    const cantidad = seleccionados.length;

    if (cantidad === 0) {
      Swal.fire({
        title: 'Selecciona peticiones',
        text: 'Debes seleccionar al menos una petición para eliminar.',
        icon: 'info',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    let titulo = cantidad === 1
      ? '¿Estás seguro que deseas eliminar esta petición?'
      : '¿Estás seguro que deseas eliminar las <b>' + cantidad + '</b> peticiones seleccionadas?';

    Swal.fire({
      title: titulo,
      html: 'Se eliminarán las peticiones seleccionadas junto con sus seguimientos.<br><b class="text-danger">Esta acción no es reversible.</b>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger me-2 waves-effect waves-light',
        cancelButton: 'btn btn-outline-secondary waves-effect'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('inputPeticionesIdsEliminar').value = JSON.stringify(seleccionados);
        $('#eliminacionMasiva').attr('action', "/peticion/" + tipo + "/eliminaciones");
        $('#eliminacionMasiva').submit();
      }
    });
  }

  function confirmarEliminacionMasiva(cantidad, tipo)
  {
    if(cantidad>0)
    {
      let titulo = '¿Estás seguro que deseas elimina esta <b>'+cantidad+'</b> petición?';
      if(cantidad > 1)
      titulo = '¿Estás seguro que deseas eliminar las <b>'+cantidad+'</b> peticiones?';

      Swal.fire({
        title: titulo,
        html: 'Esta acción no es reversible.',
        icon: 'warning',
        showCancelButton: false,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $('#eliminacionMasiva').attr('action',"/peticion/"+tipo+"/eliminaciones");
          $('#eliminacionMasiva').submit();
        }
      });
    }else{
      Swal.fire({
        title: 'No hay peticiones por eliminar',
        html: 'Intenta nuevamente.',
        icon: 'info',
        showCloseButton: false,
        showCancelButton: false,
      });
    }

  }

  function confirmarEliminacion($peticionId)
  {
    Swal.fire({
      title: '¿Estás seguro que deseas elimina esta petición?',
      html: 'Esta acción no es reversible.',
      icon: 'warning',
      showCancelButton: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#eliminacion').attr('action',"/peticion/"+$peticionId+"/eliminacion");
        $('#eliminacion').submit();
      }
    })
  }
</script>

<script>
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {
      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;

      // Si es el tag de búsqueda por palabra clave
      if (field === 'buscar') {
        const inputBuscar = document.getElementById('buscar');
        if (inputBuscar) inputBuscar.value = '';

        const hiddenBuscar = document.querySelector('#busquedaAvanzada [name="buscar"]');
        if (hiddenBuscar) hiddenBuscar.value = '';

        document.getElementById('busquedaAvanzada').submit();
        return;
      }

      const form = document.getElementById('busquedaAvanzada');
      const input = form.querySelector('[id="' + field + '"]') || form.querySelector('[name="' + field + '"]');

      if (input && ($(input).hasClass('select2BusquedaAvanzada') || $(input).hasClass('select2'))) {
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
          const inputAux = form.querySelector('[id="' + fieldAux + '"]') || form.querySelector('[name="' + fieldAux + '"]');
          if (inputAux) inputAux.value = '';
        }
      }

      form.submit();
    });
  });

  $(document).ready(function() {
    $('#filtroOrigen').on('change', function() {
      if ($(this).val() === 'externos') {
        $('#contenedorFiltrosRegistrados').slideUp();
        $('#filtroSedes').val(null).trigger('change');
        $('#persona_id').val('');
      } else {
        $('#contenedorFiltrosRegistrados').slideDown();
      }
    });

    // Búsqueda con debounce automático (estándar REDIL)
    let timeoutBuscar;
    const delayBuscar = 700;

    $('#buscar').on('input', function() {
      clearTimeout(timeoutBuscar);
      const val = $(this).val().trim();
      if (val.length >= 3) {
        timeoutBuscar = setTimeout(function() {
          $('#filtro').submit();
        }, delayBuscar);
      } else if (val.length === 0) {
        // Al vaciar la caja de búsqueda se ejecuta automáticamente
        $('#filtro').submit();
      }
    });

    // Envío inmediato al presionar Enter
    $('#buscar').on('keydown', function(e) {
      if (e.key === 'Enter') {
        clearTimeout(timeoutBuscar);
        e.preventDefault();
        $('#filtro').submit();
      }
    });

    // Envío al hacer clic en el icono de la lupa
    $('#btnEjecutarBuscar').on('click', function() {
      clearTimeout(timeoutBuscar);
      $('#filtro').submit();
    });

    // Limpiar búsqueda con el botón 'x'
    $('#btnLimpiarBuscar').on('click', function() {
      clearTimeout(timeoutBuscar);
      $('#buscar').val('');
      $('#filtro').submit();
    });

    // Checkbox seleccionar todas
    $('#checkSeleccionarTodas').on('change', function() {
      const checked = this.checked;
      document.querySelectorAll('.check-peticion').forEach(cb => {
        cb.checked = checked;
      });
      actualizarSeleccion();
    });
  });

  function actualizarSeleccion() {
    const checkboxes = document.querySelectorAll('.check-peticion');
    const checked = document.querySelectorAll('.check-peticion:checked');
    const total = checkboxes.length;
    const cantSeleccionadas = checked.length;

    const contador = document.getElementById('contadorSeleccionadas');
    if (contador) contador.innerText = cantSeleccionadas;

    const checkTodas = document.getElementById('checkSeleccionarTodas');
    if (checkTodas) {
      checkTodas.checked = (cantSeleccionadas === total && total > 0);
      checkTodas.indeterminate = (cantSeleccionadas > 0 && cantSeleccionadas < total);
    }

    const barra = document.getElementById('barraAccionesMasivas');
    if (barra) {
      if (cantSeleccionadas > 0) {
        $(barra).slideDown();
      } else {
        $(barra).slideUp();
      }
    }

    // Marcar borde de tarjetas seleccionadas
    checkboxes.forEach(cb => {
      const card = document.getElementById('peticion-card-' + cb.value)?.querySelector('.card');
      if (card) {
        if (cb.checked) {
          card.classList.add('border-primary');
        } else {
          card.classList.remove('border-primary');
        }
      }
    });
  }

  function deseleccionarTodas() {
    document.querySelectorAll('.check-peticion').forEach(cb => {
      cb.checked = false;
    });
    const checkTodas = document.getElementById('checkSeleccionarTodas');
    if (checkTodas) {
      checkTodas.checked = false;
      checkTodas.indeterminate = false;
    }
    actualizarSeleccion();
  }

  function abrirModalAsignacionMasiva() {
    const seleccionados = Array.from(document.querySelectorAll('.check-peticion:checked')).map(cb => cb.value);
    if (seleccionados.length === 0) {
      Swal.fire({
        title: 'Selecciona peticiones',
        text: 'Debes seleccionar al menos una petición para asignar a un intercesor.',
        icon: 'info',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    Livewire.dispatch('modalAsignarIntercesorMasivo', { peticionesIds: seleccionados });
  }
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Seleccionamos todos los elementos que se pueden colapsar
    const collapseElements = document.querySelectorAll('.collapse');

    collapseElements.forEach(function(collapseEl) {
      // Escuchamos el evento que Bootstrap dispara ANTES de empezar a MOSTRAR el contenido
      collapseEl.addEventListener('show.bs.collapse', function() {
        // Buscamos el botón que controla este div en específico
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          // Cambiamos el ícono a 'menos'
          if(icon){
             icon.classList.remove('ti-plus');
             icon.classList.add('ti-minus');
          }
        }
      });

      // Escuchamos el evento que Bootstrap dispara ANTES de empezar a OCULTAR el contenido
      collapseEl.addEventListener('hide.bs.collapse', function() {
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          // Cambiamos el ícono a 'más'
          if(icon){
             icon.classList.remove('ti-minus');
             icon.classList.add('ti-plus');
          }
        }
      });
    });
  });
</script>
@endsection


@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Gestionar peticiones</h4>

@include('layouts.status-msn')

  <div class="row pt-5">
    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
      <div class="swiper-wrapper">
          <!-- Cards with few info -->
          @foreach( $indicadores->chunk(4) as $chunk )
          <div class="swiper-slide">
            <div class="row equal-height-row g-2">
              @foreach($chunk as $indicador )
              <div class="col equal-height-col col-lg-4 col-12">
                <a href="{{ route('peticion.gestionar', $indicador->url)  }}">
                  <div class="card border rounded-3 shadow-sm">
                    <div class="card-body d-flex flex-row p-3">

                      <div class="card-icon me-1">
                      <img src="{{ $indicador->imagen }}" alt="icono" class="me-2" width="50">
                      </div>

                      <div class="card-title mb-0">
                        <p class="text-black mb-0" style="font-size: .8125rem">{{ $indicador->nombre }}</p>
                        <h5 class="mb-0 me-2">{{ $indicador->cantidad }}</h5>
                      </div>

                    </div>
                  </div>
                </a>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
          <!--/ Cards with few info -->
      </div>
      <div class="d-flex mt-10">
          <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>

  <hr>

  <form id="filtro" class="forms-sample" method="GET" action="{{ route('peticion.gestionar', $tipo) }}">
    <!-- Parametros de filtros activos -->
    @if(!empty($filtroOrigen))
      <input type="hidden" name="filtroOrigen" value="{{ $filtroOrigen }}">
    @endif
    @if(!empty($filtroAsignacion))
      <input type="hidden" name="filtroAsignacion" value="{{ $filtroAsignacion }}">
    @endif
    @if(!empty($persona))
      <input type="hidden" name="persona_id" value="{{ $persona->id }}">
    @endif
    @if(isset($filtroTipoPeticiones) && is_array($filtroTipoPeticiones))
      @foreach($filtroTipoPeticiones as $tp)
        <input type="hidden" name="filtroTipoPeticiones[]" value="{{ $tp }}">
      @endforeach
    @endif
    @if(isset($filtroFormularios) && is_array($filtroFormularios))
      @foreach($filtroFormularios as $ff)
        <input type="hidden" name="filtroFormularios[]" value="{{ $ff }}">
      @endforeach
    @endif
    @if(isset($filtroSedes) && is_array($filtroSedes))
      @foreach($filtroSedes as $fs)
        <input type="hidden" name="filtroSedes[]" value="{{ $fs }}">
      @endforeach
    @endif
    @if(isset($filtroPaises) && is_array($filtroPaises))
      @foreach($filtroPaises as $fp)
        <input type="hidden" name="filtroPaises[]" value="{{ $fp }}">
      @endforeach
    @endif

    <div class="row mt-5 g-2">
      <!-- Buscador por palabra clave -->
      <div class="col-12 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <span class="input-group-text cursor-pointer" id="btnEjecutarBuscar" style="cursor: pointer;" title="Buscar"><i class="ti ti-search"></i></span>
          <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" class="form-control" placeholder="Buscar por nombre, correo, ciudad..." aria-label="Buscar...">
          @if($buscar)
            <span id="btnLimpiarBuscar" class="input-group-text" style="cursor: pointer;" title="Limpiar búsqueda"><i class="ti ti-x"></i></span>
          @endif
        </div>
      </div>

      <!-- Por rango de fechas  -->
      <div class="col-8 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
          <span class="input-group-text"><i class="ti ti-calendar"></i></span>
        </div>
        <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none" placeholder="">
        <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none" placeholder="">
      </div>

      <div class="col-4 col-md-4 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-4 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada">
          <span class="d-none d-md-inline fw-semibold">Filtros</span>
          <i class="ti ti-filter ms-md-1"></i>
        </button>
        @if($rolActivo->hasPermissionTo('peticiones.boton_descargar_excel'))
          <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-3 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalGeneradorExcel" title="Descargar excel">
            <i class="ti ti-file-download"></i> <span class="d-none d-md-inline">Excel</span>
          </button>
        @endif
      </div>

      <div class="filter-tags py-3">
        <span class="text-black me-5">{{ $peticiones->total() > 1 ? $peticiones->total().' Peticiones' : $peticiones->total().' Petición' }} </span>
        @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
          @foreach($tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($bandera == 1)
            <a type="button" href="{{ route('peticion.gestionar', $tipo) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>
    </div>
  </form>

  @if(($rolActivo->hasPermissionTo('peticiones.subitem_gestionar_intercesores') || $rolActivo->hasPermissionTo('peticiones.opcion_eliminacion_masiva')) && count($peticiones) > 0)
    <div id="barraAccionesMasivas" class="card border border-primary rounded-pill shadow-sm px-4 py-2 my-3 bg-white" style="display: none;">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="form-check m-0">
            <input class="form-check-input" type="checkbox" id="checkSeleccionarTodas">
            <label class="form-check-label fw-semibold text-black ms-1" for="checkSeleccionarTodas">
              Seleccionar todas (<span id="totalPaginaActual">{{ count($peticiones) }}</span>)
            </label>
          </div>
          <span class="badge bg-label-primary rounded-pill px-3 py-2 ms-2">
            <span id="contadorSeleccionadas">0</span> seleccionadas
          </span>
        </div>
        <div class="d-flex align-items-center gap-2">
          @if($rolActivo->hasPermissionTo('peticiones.subitem_gestionar_intercesores'))
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="abrirModalAsignacionMasiva()">
              <i class="ti ti-user-check me-1"></i> Asignar a intercesor
            </button>
          @endif
          @if($rolActivo->hasPermissionTo('peticiones.opcion_eliminacion_masiva'))
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmarEliminacionSeleccionadas('{{ $tipo }}')">
              <i class="ti ti-trash me-1"></i> Eliminar
            </button>
          @endif
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="deseleccionarTodas()">
            Deseleccionar
          </button>
        </div>
      </div>
    </div>
  @endif

  <!-- lista de peticiones -->
  <div class="row equal-height-row g-4 mt-1">
    @if(count($peticiones)>0)
      @foreach($peticiones as $peticion)
      <div class="col equal-height-col col-12 col-xl-4 col-lg-6 col-md-6" id="peticion-card-{{ $peticion->id }}">
        <div class="card rounded-3 shadow">
          
          <div class="card-header border-bottom d-flex align-items-center px-4 pt-3 pb-2" style="background-color:#F9F9F9!important">
            @if($rolActivo->hasPermissionTo('peticiones.subitem_gestionar_intercesores') || $rolActivo->hasPermissionTo('peticiones.opcion_eliminacion_masiva'))
              <div class="form-check me-2 mb-0">
                <input class="form-check-input check-peticion" type="checkbox" value="{{ $peticion->id }}" id="checkPeticion{{ $peticion->id }}" onchange="actualizarSeleccion()">
              </div>
            @endif
            <div class="flex-fill row">
              <div class="d-flex justify-content-between align-items-center">

                <div class="d-flex flex-column">
                  <h5 class="fw-semibold ms-1 text-black m-0 line-clamp-1" title="{{ $peticion->nombreUsuario }}">
                    {{ $peticion->nombreUsuario }}
                  </h5>             
                  <small class="text-black ms-1"><i>Fecha:</i> {{ $peticion->fecha ?? 'Sin fecha' }} </small>
                </div>

                <span class="badge rounded-pill fw-light mt-1 me-1 bg-label-primary">
                  {{ $peticion->tipoPeticion ? $peticion->tipoPeticion->nombre : 'No definido'}}
                </span>
                @if($peticion->formularioUsuario)
                  <span class="badge rounded-pill fw-light mt-1 me-1 bg-label-info" title="Registro desde formulario de usuario">
                    <i class="ti ti-forms me-1"></i>{{ $peticion->formularioUsuario->titulo ?? $peticion->formularioUsuario->nombre }}
                  </span>
                @endif
              </div>
            </div>

            <div class="">
              <div class="ms-auto">
                <div class="dropdown zindex-2 p-1 float-end">
                  <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    @if($rolActivo->hasPermissionTo('peticiones.subitem_gestionar_intercesores'))
                      <li><a class="dropdown-item" href="javascript:void(0);" onclick="Livewire.dispatch('modalAsignarIntercesor', { peticionId: '{{$peticion->id}}'})">Asignar a intercesor</a></li>
                    @endif
                    @if($rolActivo->hasPermissionTo('peticiones.opcion_eliminar'))
                      <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion('{{$peticion->id}}')">Eliminar</a></li>
                    @else
                    @endif
                    @if(!$rolActivo->hasPermissionTo('peticiones.subitem_gestionar_intercesores') && !$rolActivo->hasPermissionTo('peticiones.opcion_eliminar'))
                      <li><a class="dropdown-item disabled" href="javascript:void(0);">Sin acciones</a></li>
                    @endif
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row mt-4">
              
              <div class="col-6 d-flex flex-column mb-3">
                <small class="text-black">Creada por</small>
                <div class="d-flex align-items-center mt-1">
                  <div class="avatar avatar-sm me-2">
                    @if($peticion->autorCreacion && !empty($peticion->autorCreacion->foto) && !in_array($peticion->autorCreacion->foto, ['default-m.png', 'default-f.png']))
                      <img class="rounded-circle" src="{{ tenant_asset('img/usuarios/foto-usuario/'.$peticion->autorCreacion->foto) }}" alt="foto">
                    @else
                      <span class="avatar-initial rounded-circle bg-label-primary">{{ $peticion->autorCreacion ? $peticion->autorCreacion->inicialesNombre() : 'AG' }}</span>
                    @endif
                  </div>
                  <small class="fw-semibold text-black ">{{ $peticion->usuarioCreacion ?? 'No especificado'}}</small>
                </div>
              </div>

              <div class="col-6 d-flex flex-column mb-3">
                <small class="text-black">Asignada a</small>
                <div class="d-flex align-items-center mt-1">
                  <div class="avatar avatar-sm me-2">
                    @if($peticion->asignado && !empty($peticion->asignado->foto) && !in_array($peticion->asignado->foto, ['default-m.png', 'default-f.png']))
                      <img class="rounded-circle" src="{{ tenant_asset('img/usuarios/foto-usuario/'.$peticion->asignado->foto) }}" alt="foto">
                    @elseif($peticion->asignado)
                      <span class="avatar-initial rounded-circle bg-label-success">{{ $peticion->asignado->inicialesNombre() }}</span>
                    @else
                      <span class="avatar-initial rounded-circle bg-label-secondary"><i class="ti ti-user-x"></i></span>
                    @endif
                  </div>
                  <small class="fw-semibold text-black text-truncate" title="{{ $peticion->usuarioAsignadoNombre ?? 'Sin asignar' }}">{{ $peticion->usuarioAsignadoNombre ?? 'Sin asignar'}}</small>
                </div>
              </div>

              <div class="col-12">
                <hr class="my-3 border-1">
              </div>

              <div class="col-6 d-flex flex-column mt-1">
                <small class="text-black">Correo</small>
                <small class="fw-semibold text-black text-truncate" title="{{ $peticion->emailUsuario ?? 'No especificado' }}">{{ $peticion->emailUsuario ?? 'No especificado'}}</small>
              </div>

              <div class="col-6 d-flex flex-column mt-1">
                <small class="text-black">Teléfono</small>
                <small class="fw-semibold text-black ">{{ $peticion->telefonosUsuario ?? 'No especificado' }}</small>
              </div>

              @php
                $tieneUbicacion = !empty($peticion->ciudad) || !empty($peticion->pais);
                $tieneSede = !empty($peticion->user_id) && !empty($peticion->nombreSede);
              @endphp

              @if($tieneUbicacion)
                <div class="{{ $tieneSede ? 'col-6' : 'col-12' }} d-flex flex-column mt-2">
                  <small class="text-black">Ubicación</small>
                  <small class="fw-semibold text-black text-truncate" title="@if($peticion->ciudad){{ $peticion->ciudad }}@endif{{ $peticion->ciudad && $peticion->pais ? ', ' : '' }}{{ $peticion->pais ? $peticion->pais->nombre : '' }}">
                    <i class="ti ti-map-pin me-1 text-muted"></i>
                    @if($peticion->ciudad){{ $peticion->ciudad }}@endif{{ $peticion->ciudad && $peticion->pais ? ', ' : '' }}{{ $peticion->pais ? $peticion->pais->nombre : '' }}
                  </small>
                </div>
              @endif

              @if($tieneSede)
                <div class="{{ $tieneUbicacion ? 'col-6' : 'col-12' }} d-flex flex-column mt-2">
                  <small class="text-black">Sede</small>
                  <small class="fw-semibold text-black text-truncate" title="{{ $peticion->nombreSede }}">
                    <i class="ti ti-building me-1 text-muted"></i>{{ $peticion->nombreSede }}
                  </small>
                </div>
              @endif


            </div>

            <div class="collapse" id="cardBodyPeticion{{ $peticion->id }}">
              <div class="col-12">
                <hr class="my-3 border-1">
              </div>

              <h6 class="fw-bold text-black px-4 mb-2">Petición</h6>
              <div class="px-4 mb-3" style="max-height: 150px; overflow-y: auto;">
                <p class="m-0 text-sm" style="font-size: 0.875rem;">{!! $peticion->descripcion !!}</p>
              </div>

              @if($peticion->estado > 1)
                <h6 class="fw-bold text-black px-4 mb-2 border-top pt-3">Seguimiento</h6>
                <div class="px-4 mb-3" style="max-height: 150px; overflow-y: auto;">
                  @foreach($peticion->seguimientos as $seguimiento)
                  <p class="text-secondary mt-0 mb-1"><small><b><i class="ti ti-user-circle"></i> Por:</b> {{$seguimiento->usuarioCreacion ? $seguimiento->usuarioCreacion->nombre(3) : 'No definido' }}</small></p>
                  <p class="m-0 text-sm" style="font-size: 0.875rem;">{!! $seguimiento->descripcion !!}</p>
                  @if(!$loop->last) <hr class="my-2"> @endif
                  @endforeach
                </div>
              @endif

              @if($peticion->estado==2)
                <h6 class="fw-bold text-black px-4 mb-2 border-top pt-3">Respuesta</h6>
                <div class="px-4 mb-3" style="font-size: 0.875rem;">
                  {!! $peticion->respuesta !!}
                </div>
              @endif

              <div class="d-flex justify-content-center gap-2 mt-4 px-3 mb-2">
                <button type="button" onclick="Livewire.dispatch('modalResponder', { peticionId: '{{$peticion->id}}', personaId: '{{$peticion->user_id ?? 0}}'})" class="btn btn-sm rounded-pill btn-primary waves-effect"> <i class="ti ti-messages me-1"></i> Responder</button>
              </div>

            </div>
          </div>

          <div class="card-footer border-top p-1">
            <div class="d-flex justify-content-center">
              <button type="button"
                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2 align-items-center justify-content-center d-flex btn-collapse-card"
                data-bs-toggle="collapse"
                data-bs-target="#cardBodyPeticion{{ $peticion->id }}"
                aria-expanded="false"
                aria-controls="cardBodyPeticion{{ $peticion->id }}">
                <span class="ti ti-plus"></span>
              </button>
            </div>
          </div>

        </div>
      </div>
      @endforeach
    @else
    <div class="mt-5 mb-5 py-5 w-100">
      <center>
        <i class="ti ti-notes ti-xl text-muted"></i>
        <p class="text-muted mt-2">No se encontraron peticiones.</p>
      </center>
    </div>
    @endif
  </div>
  <!--/ lista de peticiones -->

  <div class="row my-3">
    @if($peticiones)
    {!! $peticiones->appends(request()->input())->links() !!}
    @endif
  </div>

  @livewire('Peticiones.gestionar-peticiones')

  <form id="eliminacionMasiva" method="POST" action="">
    @csrf
    <input type="hidden" name="peticiones_ids" id="inputPeticionesIdsEliminar">
    <textarea id="parametros-busqueda" name="parametrosBusqueda" class="d-none">{{json_encode(request()->input())}}</textarea>
  </form>

  <form id="eliminacion" method="POST" action="">
    @csrf
  </form>

   <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('peticion.gestionar', $tipo) }}">
    <input type="hidden" name="buscar" value="{{ $buscar }}">
    <input type="hidden" name="filtroFechaIni" value="{{ $filtroFechaIni }}">
    <input type="hidden" name="filtroFechaFin" value="{{ $filtroFechaFin }}">
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
              Filtros
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
            <div class="row">

              <!-- Tipo de solicitante -->
              <div class="col-12 mb-3">
                <label class="form-label" for="filtroOrigen">Tipo de solicitante</label>
                <select id="filtroOrigen" name="filtroOrigen" class="form-select">
                  <option value="" {{ empty($filtroOrigen) ? 'selected' : '' }}>Todas (Registradas y Externas)</option>
                  <option value="registrados" {{ $filtroOrigen === 'registrados' ? 'selected' : '' }}>Solo usuarios registrados</option>
                  <option value="externos" {{ $filtroOrigen === 'externos' ? 'selected' : '' }}>Solo peticiones externas</option>
                </select>
              </div>

              <!-- Por tipo peticion -->
              <div class="col-12 mb-3">
                <label class="form-label">Por tipo de peticiones</label>
                <select id="filtroTipoPeticiones" name="filtroTipoPeticiones[]" class="select2 form-select" multiple>
                  @foreach($tiposPeticiones as $tipoPeticion)
                  <option value="{{ $tipoPeticion->id }}" {{ $filtroTipoPeticiones && in_array($tipoPeticion->id,$filtroTipoPeticiones) ? 'selected' : '' }}>{{ $tipoPeticion->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por formulario -->
              <div class="col-12 mb-3">
                <label class="form-label">Por formulario</label>
                <select id="filtroFormularios" name="filtroFormularios[]" class="select2 form-select" multiple>
                  @foreach($formulariosUsuario as $formulario)
                  <option value="{{ $formulario->id }}" {{ isset($filtroFormularios) && in_array($formulario->id, $filtroFormularios) ? 'selected' : '' }}>
                    {{ $formulario->titulo ?? $formulario->nombre }}
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Filtros exclusivos de usuarios registrados (Sedes y Persona) -->
              <div id="contenedorFiltrosRegistrados" class="col-12" style="display: {{ $filtroOrigen === 'externos' ? 'none' : 'block' }};">
                <!-- Por sede -->
                <div class="col-12 mb-3">
                  <label class="form-label">Por sedes</label>
                  <select id="filtroSedes" name="filtroSedes[]" class="select2 form-select" multiple>
                    @foreach($sedes as $sede)
                    <option value="{{ $sede->id }}" {{ isset($filtroSedes) && in_array($sede->id, $filtroSedes) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Por persona -->
                @livewire('Usuarios.usuarios-para-busqueda', [
                  'id' => 'persona_id',
                  'class' => 'col-12 col-md-12 mb-3',
                  'label' => 'Filtrar por persona',
                  'estiloSeleccion' => 'pequeno',
                  'placeholder' => 'Filtrar por persona',
                  'tipoBuscador' => 'unico',
                  'queUsuariosCargar' => $queUsuariosCargar,
                  'conDadosDeBaja' => 'no',
                  'modulo' => 'peticiones',
                  'obligatorio' => false,
                  'usuarioSeleccionadoId' => $persona ? $persona->id : ''
                ])
              </div>

              <!-- Por estado de asignación -->
              <div class="col-12 mb-3">
                <label class="form-label">Estado de asignación</label>
                <select id="filtroAsignacion" name="filtroAsignacion" class="form-select">
                  <option value="">Todas</option>
                  <option value="asignadas" {{ isset($filtroAsignacion) && $filtroAsignacion === 'asignadas' ? 'selected' : '' }}>Asignadas</option>
                  <option value="sin_asignar" {{ isset($filtroAsignacion) && $filtroAsignacion === 'sin_asignar' ? 'selected' : '' }}>Sin asignar</option>
                  <option value="asignadas_a_mi" {{ isset($filtroAsignacion) && $filtroAsignacion === 'asignadas_a_mi' ? 'selected' : '' }}>Asignadas a mí</option>
                </select>
              </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  <!-- offcanvas generador de excel  -->
  <form class="forms-sample" method="POST" action="{{ route('peticion.generarExcel', $tipo) }}">
    @csrf
    <textarea id="parametros-busqueda-excel" name="parametrosBusqueda" class="d-none">{{json_encode(request()->input())}}</textarea>
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalGeneradorExcel" aria-labelledby="modalGeneradorExcelLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalGeneradorExcelLabel">
              Exportar a excel
            </h4>

            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
           <div class="mb-4">
              <span class="text-black ti-14px mb-4">Selecciona los campos que deseas exportar en el archivo excel.</span>
            </div>

             <div class="row">
                <!-- Información petición-->
                <div class="col-12 mb-3">
                  <label for="informacionCamposPeticiones" class="form-label">Información campos petición <br>
                    (<a href="javascript:;" data-select="informacionCamposPeticiones" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCamposPeticiones" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                  </label>
                  <select id="informacionCamposPeticiones" name="informacionCamposPeticiones[]" class="select2GeneradorExcel form-select" multiple>
                    @foreach($camposPeticiones as $campoPeticion)
                    <option value="{{ $campoPeticion->id }}">{{ $campoPeticion->nombre }}</option>
                    @endforeach
                  </select>
                </div>


            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Exportar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

@endsection
