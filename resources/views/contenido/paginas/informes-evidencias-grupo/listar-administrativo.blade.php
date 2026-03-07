@extends('layouts.layoutMaster')

@section('title', 'Administración de informes de evidencias')

@section('page-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js'
  ])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    moment.locale('es');

    var fecha_ini = moment('{{ $filtroFechaIni }}');
    var fecha_fin = moment('{{ $filtroFechaFin }}');

    // Mostrar el rango inicial en el input
    $('#filtroFechas').val(fecha_ini.format('YYYY-MM-DD') + ' hasta ' + fecha_fin.format('YYYY-MM-DD'));

    $('#filtroFechas').daterangepicker({
      startDate: fecha_ini,
      endDate: fecha_fin,
      ranges: {
        'Hoy': [moment(), moment()],
        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
        'Mes actual': [moment().startOf('month'), moment().endOf('month')],
        'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        'Año actual': [moment().startOf('year'), moment().endOf('year')],
        'Año anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
      },
      locale: {
        format: "YYYY-MM-DD",
        separator: " hasta ",
        applyLabel: "Aplicar",
        cancelLabel: "Cancelar",
        fromLabel: "Desde",
        toLabel: "Hasta",
        customRangeLabel: "Otro rango",
        monthNames: @json($meses),
        firstDay: 1
      },
      showDropdowns: true
    });

    // Se ejecuta cuando el usuario selecciona un rango y hace clic en aplicar
    $('#filtroFechas').on('apply.daterangepicker', function(ev, picker) {
      $('#filtroFechaIni').val(picker.startDate.format('YYYY-MM-DD'));
      $('#filtroFechaFin').val(picker.endDate.format('YYYY-MM-DD'));
      $("#filtro").submit();
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Escuchar el evento de cambio del grupo en Livewire para enviar el formulario
    window.addEventListener('grupo-seleccionado', event => {
        $("#filtro").submit();
    });

    const forms = document.querySelectorAll('.form-eliminar');
    forms.forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esto!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, eliminarlo!',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            this.submit();
          }
        });
      });
    });
  });
</script>
@endsection

@section('content')

@include('layouts.status-msn')

<h4 class="mb-1 fw-semibold text-primary">Administración de informes de evidencias</h4>
<p class="mb-4 text-black">Consulta y gestiona los informes de evidencia de los grupos bajo tu supervisión.</p>


<form id="filtro" class="row" method="GET" action="{{ route('grupo.informesEvidenciaAdministrativo') }}">
    <!-- Filtro por Grupo (Livewire) -->
    <div class="col-12 col-md-5 mb-3">
        @livewire('Grupos.grupos-para-busqueda',[
            'id' => 'filtroGrupo',
            'class' => 'col-12',
            'label' => 'Filtrar a partir del grupo',
            'conDadosDeBaja' => 'no',
            'grupoSeleccionadoId' => $parametrosBusqueda->filtroGrupo,
            'estiloSeleccion' => 'pequeno'
        ])
    </div>

    <!-- Filtro por Fechas -->
    <div class="col-12 col-md-5 mb-3"> 
        <label class="form-label">Rango de fechas</label>
        <div class="input-group input-group-merge">
            <input id="filtroFechas" name="filtroFechas" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
        </div>
        <input type="text" id="filtroFechaIni" name="filtroFechaIni" value="{{ $filtroFechaIni }}" class="form-control d-none">
        <input type="text" id="filtroFechaFin" name="filtroFechaFin" value="{{ $filtroFechaFin }}" class="form-control d-none">
    </div>

    <div class="col-12 col-md-2 d-flex align-items-center">
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-search me-1"></i> Buscar
        </button>
    </div>
</form>

@if($informes->count() > 0)
<div class="row">
  @foreach($informes as $informe)
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
             <h5 class="card-title text-primary mb-1">{{ $informe->nombre }}</h5>
             <p class="mb-1 text-black fw-bold">{{ $informe->grupo->nombre }}</p>
             <p class="mb-1 text-black small">
                @foreach($informe->grupo->encargados as $encargado)
                    {{ $encargado->nombre(3) }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
             </p>
             <p class="card-text text-black mb-2 small">
                <i class="ti ti-calendar me-1"></i> {{ $informe->fecha }}
             </p>
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="dropdownMenuButton{{$informe->id}}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="ti ti-dots-vertical"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{$informe->id}}">
              @if($rolActivo->hasPermissionTo('grupos.opcion_ver_informe_evidencia'))
              <a class="dropdown-item" href="{{ route('grupo.informeEvidencia.ver', [$informe->grupo, $informe]) }}">
                Ver
              </a>
              @endif
              @if($rolActivo->hasPermissionTo('grupos.opcion_editar_informe_evidencia'))
              <a class="dropdown-item" href="{{ route('grupo.informeEvidencia.editar', [$informe->grupo, $informe]) }}">
                Editar
              </a>
              @endif

              @if($rolActivo->hasPermissionTo('grupos.opcion_descargar_informe_evidencia'))
              <a class="dropdown-item" href="{{ route('grupo.informeEvidencia.descargar', [$informe->grupo, $informe]) }}">
                Descargar
              </a>
              @endif

              <hr class="dropdown-divider">
                
              @if($rolActivo->hasPermissionTo('grupos.opcion_eliminar_informe_evidencia'))
              <form action="{{ route('grupo.informeEvidencia.eliminar', [$informe->grupo, $informe]) }}" method="POST" class="d-inline form-eliminar">
                @csrf
                @method('DELETE')
                <input type="hidden" name="source" value="admin">
                <button type="submit" class="dropdown-item text-danger">
                  Eliminar
                </button>
              </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div class="mt-4">
  {{ $informes->appends(request()->query())->links() }} 
</div>
@else
  <div class="my-10 text-center">
    <i class="ti ti-file ti-xl mb-2"></i>
    <p>No se encontraron informes con los filtros seleccionados.</p>
  </div>
@endif

@endsection
