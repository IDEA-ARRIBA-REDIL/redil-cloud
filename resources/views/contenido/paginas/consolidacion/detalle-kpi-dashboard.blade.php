@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Detalle KPI ' . Helper::termino('consolidacion', minuscula: true) . ' (Dashboard)')

@section('content')

  <div class="d-flex align-items-center mb-1">
    <a href="{{ route('consolidacion.dashboard', array_merge(request()->except(['kpi', 'buscar']), ['tab' => request('tab', 'indicador-1')])) }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver al Dashboard">
        <i class="ti ti-arrow-left text-primary fs-4"></i>
    </a>
    <h4 class="mb-0 fw-semibold text-primary">Detalle de {{ Helper::termino('consolidacion', minuscula: true) }}: 
      @switch($kpi)
          @case('cosecha_total') Total cosecha @break
          @case('cosecha_efectiva') Cosecha efectiva @break
          @case('deserciones') Deserciones (Cosecha) @break
          @case('total_matriculas') Total matrículas @break
          @case('matriculas_sector') Matrículas de Sector @break
          @case('matriculas_templo') Matrículas de Templo @break
          @case('matriculas_aptos') Matrículas Aptos (Cosecha Vigentes) @break
          @case('matriculas_union_libre') Matrículas Unión Libre @break
          @case('matriculas_efectivos') Matrículas Efectivas @break
          @case('matriculas_deserciones') Deserciones (Matrículas) @break
          @case('total_miembros') Total miembros @break
          @case('miembros_ubicados') Miembros ubicados en grupos @break
          @case('union_libre_matriculados') Unión libre matriculados @break
          @case('miembros_formalizados') Miembros que estaban en unión libre (formalizados) @break
          @case('pendientes_membresia_union_libre') Pendientes por membresía (Unión libre) @break
          @case('bautismos') Bautismos @break
          @case('traslados') Traslados @break
          @default
              @if(str_starts_with($kpi, 'cosecha_vinculacion_')) Cosecha por Vinculación
              @elseif(str_starts_with($kpi, 'traslados_')) Traslados: {{ str_contains($kpi, 'adultos') ? 'Adultos' : 'Warriors' }}
              @elseif(str_starts_with($kpi, 'bautismos_')) Bautismos: {{ str_contains($kpi, 'adultos') ? 'Adultos' : 'Warriors' }}
              @else KPI @endif
      @endswitch
    </h4>
  </div>

 <div class="text-black small mb-3">
    @if(isset($bloqueDetalle))
        <span class="fw-semibold">Bloque:</span> {{ $bloqueDetalle->nombre }}
    @else
        <span class="fw-semibold">Filtros de Bloque:</span> {{ count($bloquesSeleccionados) == 0 ? 'Todos' : count($bloquesSeleccionados) . ' seleccionados' }}
    @endif
    
    <span class="mx-1">|</span>
    
    @if(isset($sedeDetalle))
        <span class="fw-semibold">Sede:</span> {{ $sedeDetalle->nombre }}
    @else
        <span class="fw-semibold">Filtros de Sede:</span> {{ count($sedesSeleccionadas) == 0 ? 'Todas' : count($sedesSeleccionadas) . ' seleccionadas' }}
    @endif

    <span class="mx-1">|</span>
    <span class="fw-semibold">Rango:</span> {{ $rangoFechas }}
 </div>

 <form id="formFiltroKpi" method="GET" action="{{ route('consolidacion.dashboard.detalle-kpi') }}">
    <div class="row mt-10 mb-5">
        <input type="hidden" name="kpi" value="{{ $kpi }}">
        <input type="hidden" name="bloques_seleccionados" value="{{ implode(',', $bloquesSeleccionados) }}">
        <input type="hidden" name="sedes_seleccionadas" value="{{ implode(',', $sedesSeleccionadas) }}">
        <input type="hidden" name="rango_fechas" value="{{ $rangoFechas }}">
        
        @if(isset($bloqueDetalle))
            <input type="hidden" name="bloque_id" value="{{ $bloqueDetalle->id }}">
        @endif
        @if(isset($sedeDetalle))
            <input type="hidden" name="sede_id" value="{{ $sedeDetalle->id }}">
        @endif

        <div class="row w-100">
            <div class="col-9">
                <div class="input-group input-group-merge bg-white">
                    <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                    <input type="text" id="inputBuscar" class="form-control" name="buscar" placeholder="Buscar por nombre..." value="{{ request('buscar') }}" aria-label="Buscar..." aria-describedby="basic-addon-search31">
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end">
                <a href="{{ route('consolidacion.dashboard.detalle-kpi.exportar', request()->all()) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3">
                    <span class="d-none d-md-block fw-semibold">Descargar excel</span><i class="ti ti-file-download ms-1"></i>
                </a> 
            </div>
        </div>
    </div>
</form>

 <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputBuscar = document.getElementById('inputBuscar');
        const form = document.getElementById('formFiltroKpi');
        let timeout = null;

        inputBuscar.addEventListener('input', function() {
            clearTimeout(timeout);
            const val = this.value.trim();
            
            timeout = setTimeout(function() {
                if (val.length >= 3 || val.length === 0) {
                    form.submit();
                }
            }, 800);
        });

        // Mantener el cursor al final del input si hay búsqueda
        if (inputBuscar.value.length > 0) {
            inputBuscar.focus();
            inputBuscar.setSelectionRange(inputBuscar.value.length, inputBuscar.value.length);
        }
    });
 </script>

  <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
    <span class="text-black fw-medium">
      {{ $usuarios->total() > 1 ? $usuarios->total().' personas encontradas' : ($usuarios->total() == 1 ? '1 persona encontrada' : '0 personas') }}
    </span>
  </div>

  <!-- Listado de personas en cards -->
  <div class="row g-4 mt-1">
    @forelse($usuarios as $usuario)
      <div class="col-12 col-md-6">
        <div class="card h-100 rounded-3 shadow-sm border">
          <!-- Card Header -->
          <div class="card-header border-bottom d-flex justify-content-between align-items-center px-4 py-3" style="background-color: #f9f9f9 !important;">
            <div class="d-flex align-items-center overflow-hidden me-2">
              <div class="d-flex flex-column text-truncate">
                <h6 class="fw-semibold text-black mb-0 text-truncate">
                  {{ $usuario->nombre(3) }}
                </h6>
                @if($usuario->trashed())
                  <small class="text-danger fw-bold mt-1"><i class="ti ti-user-x fs-6 me-1"></i>Dado de baja</small>
                @endif
              </div>
            </div>

            @if($usuario->tipoUsuario)
              <span class="badge rounded-pill px-6 fw-light " style="background-color: {{ $usuario->tipoUsuario->color }}">
                <i class="{{ $usuario->tipoUsuario->icono }} fs-6"></i> <span class="text-white"> {{ $usuario->tipoUsuario->nombre }}</span>
              </span>
            @endif
          </div>

          <!-- Card Body -->
          <div class="card-body px-4 py-3">
            @php
              $telefonos = collect([
                $usuario->telefono_movil,
                $usuario->telefono_fijo,
                $usuario->telefono_otro,
              ])->filter();
            @endphp
            <div class="row g-3">
              <!-- Teléfono -->
              <div class="col-6 d-flex flex-column">
                <small class="text-black"><i class="ti ti-phone me-1"></i>Teléfono</small>
                @if($telefonos->isNotEmpty())
                  <a href="tel:{{ $telefonos->first() }}" class="fw-semibold text-black text-truncate" title="{{ $telefonos->implode(', ') }}">
                    {{ $telefonos->implode(', ') }}
                  </a>
                @else
                  <span class="fw-semibold text-black">No indicado</span>
                @endif
              </div>

              <!-- Correo -->
              <div class="col-6 d-flex flex-column">
                <small class="text-black"><i class="ti ti-mail me-1"></i>Correo</small>
                @if($usuario->email)
                  <a href="mailto:{{ $usuario->email }}" class="fw-semibold text-black text-truncate" title="{{ $usuario->email }}">
                    {{ $usuario->email }}
                  </a>
                @else
                  <span class="fw-semibold text-black">No indicado</span>
                @endif
              </div>

              <div class="col-12 my-1">
                <hr class="my-1 border-1 opacity-25">
              </div>

              <!-- Sede -->
              <div class="col-6 d-flex flex-column">
                <small class="text-black"><i class="ti ti-building me-1"></i>Sede</small>
                <span class="fw-semibold text-black text-truncate" title="{{ $usuario->sede->nombre ?? 'No especificada' }}">
                  {{ $usuario->sede->nombre ?? 'No especificada' }}
                </span>
              </div>

              <!-- Edad -->
              <div class="col-6 d-flex flex-column">
                <small class="text-black"><i class="ti ti-cake me-1"></i>Edad</small>
                <span class="fw-semibold text-black">
                  {{ $usuario->fecha_nacimiento ? ($usuario->edad() > 1 ? $usuario->edad().' años' : $usuario->edad().' año') : 'No indicada' }}
                </span>
              </div>

              <!-- ¿Por dónde ingresó? (Vinculación) -->
              <div class="col-6 d-flex flex-column">
                <small class="text-black"><i class="ti ti-link me-1"></i>¿Por dónde ingresó?</small>
                <span class="fw-semibold text-black text-truncate" title="{{ $usuario->tipoVinculacion->nombre ?? 'No especificado' }}">
                  {{ $usuario->tipoVinculacion->nombre ?? 'No especificado' }}
                </span>
              </div>

              <!-- Fecha Registro -->
              <div class="col-6 d-flex flex-column">
                <small class="text-black"><i class="ti ti-calendar me-1"></i>Fecha creación</small>
                <span class="fw-semibold text-black">
                  {{ $usuario->created_at ? $usuario->created_at->format('d-m-Y') : 'No indicada' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12 text-center py-5">
        <div class="card shadow-sm border p-5">
          <i class="ti ti-search fs-1 text-black opacity-25 mb-2"></i>
          <h5 class="mt-2 text-black">No se encontraron registros</h5>
          <p class="text-black mb-0">Intenta cambiar los filtros o el término de búsqueda.</p>
        </div>
      </div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $usuarios->appends(request()->query())->links() }}
  </div>
@endsection
