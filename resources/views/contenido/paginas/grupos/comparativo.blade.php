@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Comparativo de Grupos')

@section('page-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
<style>
  .select2-container--default .select2-selection--single,
  .select2-container--default .select2-selection--multiple {
    border: none !important;
    background-color: transparent !important;
  }

  /* Quitar borde a Bootstrap Select */
  .bootstrap-select .btn.dropdown-toggle {
    border: none !important;
    box-shadow: none !important;
  }
  
  .accordion-button::after {
    display: none !important;
  }
  .accordion-button .accordion-icon {
    transition: transform 0.3s ease;
  }
  .accordion-button:not(.collapsed) .accordion-icon {
    transform: rotate(180deg);
  }
  
  .card-kpi-sm {
      min-height: 100px;
  }
  .text-label-kpi {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 1px;
  }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    
    // Configuración Flatpickr Español (reutilizada)
    flatpickr.l10ns.es = {
      weekdays: { shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'], longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] },
      months: { shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'], longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] },
      ordinal: () => 'º',
      firstDayOfWeek: 1,
      rangeSeparator: ' a ',
      time_24hr: true,
    };

    const fpConfig = {
      mode: "range",
      dateFormat: "Y-m-d",
      locale: "es",
      weekNumbers: true,
      onClose: function(selectedDates, dateStr, instance) {
          if (selectedDates.length > 0) {
              const start = getMonday(selectedDates[0]);
              const end = getSunday(selectedDates[selectedDates.length - 1]);
              instance.setDate([start, end], true);
          }
      }
    };

    flatpickr("#rango_fechas_a", fpConfig);
    flatpickr("#rango_fechas_b", fpConfig);

    function getMonday(d) {
      d = new Date(d);
      var day = d.getDay(), diff = d.getDate() - day + (day == 0 ? -6 : 1); 
      return new Date(d.setDate(diff));
    }
    function getSunday(d) {
        d = new Date(d);
        var day = d.getDay(), diff = d.getDate() + (day == 0 ? 0 : 7 - day); 
        return new Date(d.setDate(diff));
    }

    // Select2
    $('.select2').select2({
      width: '100%',
      allowClear: true,
      language: { noResults: () => "No se encontraron resultados" }
    });
    
    // Lógica Dependiente Bloques -> Sedes
    // (Simplificada para no repetir mucho código, pero funcional)
    const $selectBloques = $('#filtro_bloques');
    const $selectSedes = $('#filtro_sedes');
    let $opcionesOriginales = $selectSedes.find('option').clone();

    $selectBloques.on('change', function() {
        const bloqueId = $(this).val();
        $selectSedes.empty();
        if (bloqueId) {
            $selectSedes.prop('disabled', false);
            $selectSedes.append('<option value="">Todas las sedes</option>');
            $opcionesOriginales.each(function() {
                if ($(this).data('bloque-id') == bloqueId) {
                    $selectSedes.append($(this).clone());
                }
            });
        } else {
            $selectSedes.prop('disabled', true);
            $selectSedes.append('<option value="">Todas las sedes</option>');
        }
        $selectSedes.trigger('change.select2'); 
    });

    if (!$selectBloques.val()) {
         $selectSedes.prop('disabled', true);
    } else {
         $selectSedes.prop('disabled', false);
         $selectBloques.trigger('change');
         const sedePreseleccionada = "{{ isset($sedesSeleccionadas) && count($sedesSeleccionadas) == 1 ? $sedesSeleccionadas[0] : '' }}";
         if(sedePreseleccionada) {
             $selectSedes.val(sedePreseleccionada).trigger('change.select2');
         }
    }
  });
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div class="d-flex align-items-center">
    <a href="{{ route('grupos.dashboard') }}" class="btn btn-icon btn-outline-primary rounded-pill me-2">
      <i class="ti ti-arrow-left"></i> 
    </a>
    <h4 class="m-0 fw-semibold text-primary">Comparativo de periodos</h4>
  </div>
</div>

<!-- Filtros Compartidos -->
<form id="formFiltros" action="{{ route('grupos.comparativo') }}" method="GET">
  <div class="row bg-white rounded-3 p-0 m-0 mb-4 shadow-sm border border-gray">
    <div class="row col-12 col-md-11 p-0 m-0">
      
      <!-- Rango Predefinido -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex flex-column align-items-center">
        <label class="form-label text-center pb-0 mb-0">Periodo A</label>
        <input type="text" class="form-control flatpickr-range text-center border-0" id="rango_fechas_a" name="rango_fechas_a" value="{{ $rangoA }}" placeholder="Periodo A">
      </div>

      <!-- Flatpickr Range -->
      <div class="col-12 col-md-3 border-end border-gray p-0 d-flex flex-column align-items-center">
         <label class="form-label text-center pb-0 mb-0">Periodo B</label>
         <input type="text" class="form-control flatpickr-range text-center border-0 " id="rango_fechas_b" name="rango_fechas_b" value="{{ $rangoB }}" placeholder="Periodo B">
     </div>
      
      <!-- Tipos de grupo -->
      <div class="col-12 col-md-2 border-none border-end p-0 d-flex align-items-center">      
        <select class="selectpicker form-select w-100 border-0" id="filtro_tipo_grupo" name="filtro_tipo_grupo[]" multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Borrar" data-style="btn-default border-0">
          @foreach($tiposGrupo as $tipo)
            <option value="{{ $tipo->id }}" {{ in_array($tipo->id, $tiposSeleccionados) ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Agrupaciones -->
      <div class="col-12 col-md-2 border-end border-gray p-0 d-flex align-items-center">
        <select class="select2 form-select" id="filtro_bloques" name="filtro_bloques">
          <option value="">Todas</option>
          @foreach($bloques as $bloque)
              <option value="{{ $bloque->id }}" {{ (isset($bloquesSeleccionados) && count($bloquesSeleccionados) == 1 && $bloquesSeleccionados[0] == $bloque->id) ? 'selected' : '' }}>{{ $bloque->nombre }}</option>
          @endforeach
        </select> 
      </div>

      <!-- Sede -->
      <div class="col-12 col-md-2 border-end border-gray p-0 d-flex align-items-center">
        <select class="select2 form-select" id="filtro_sedes" name="filtro_sedes" {{ !(isset($bloquesSeleccionados) && count($bloquesSeleccionados) == 1) ? 'disabled' : '' }}>
          <option value="">Todas</option>
          @foreach($bloques as $bloque)
              @foreach($bloque->sedes as $sede)
                  <option value="{{ $sede->id }}" data-bloque-id="{{ $bloque->id }}" {{ (isset($sedesSeleccionadas) && count($sedesSeleccionadas) == 1 && $sedesSeleccionadas[0] == $sede->id) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
              @endforeach
          @endforeach
        </select>
      </div>

    </div>
    
    <!-- Botón Filtrar -->
    <div class="col-12 col-md-1 p-0">
    <button type="submit" class="btn btn-xl btn-primary w-100 rounded-0 rounded-end h-100 px-auto fs-6">Filtrar</button>
    </div>
  </div>
</form>


<!-- Contenido Comparativo -->
<div class="row mt-10">
    
    <!-- COLUMNA PERIODO A -->
    <div class="col-12 col-xl-6 border-end pe-xl-4 mb-4 mb-xl-0">
        <h5 class="text-black border-bottom pb-2 mb-4 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Periodo A</span>
            <small class="text-black">{{ $rangoA }}</small>
        </h5>
        
        @include('contenido.paginas.grupos.partials.column_stats', ['stats' => $statsA, 'suffix' => 'A', 'color' => 'primary'])
    </div>

    <!-- COLUMNA PERIODO B -->
    <div class="col-12 col-xl-6 ps-xl-4">
        <h5 class="text-black border-bottom pb-2 mb-4 d-flex justify-content-between align-items-center">
            <span>Periodo B</span>
            <small class="text-black">{{ $rangoB }}</small>
        </h5>

        @include('contenido.paginas.grupos.partials.column_stats', ['stats' => $statsB, 'suffix' => 'B', 'color' => 'info'])
    </div>

</div>
@endsection
