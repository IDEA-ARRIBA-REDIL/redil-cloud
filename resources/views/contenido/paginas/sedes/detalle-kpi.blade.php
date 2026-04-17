@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Detalle KPI Consolidación - Sede')

@section('content')

  <div class="d-flex align-items-center mb-1">
    <a href="{{ route('sede.dashboardConsolidacion', [$sede, 'rango_fechas' => $rangoFechas]) }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver al Dashboard">
        <i class="ti ti-arrow-left text-primary fs-4"></i>
    </a>
    <h4 class="mb-0 fw-semibold text-primary">Detalle de Consolidación ({{ $sede->nombre }}): 
      @switch($kpi)
          @case('cosecha_total') Total cosecha @break
          @case('cosecha_efectiva') Cosecha efectiva @break
          @case('deserciones') Deserciones (Cosecha) @break
          @case('total_matriculas') Total matrículas @break
          @case('matriculas_sector') Matrículas de sector @break
          @case('matriculas_templo') Matrículas de templo @break
          @case('matriculas_aptos') Matrículas aptos (Cosecha Vigentes) @break
          @case('matriculas_union_libre') Matrículas unión libre @break
          @case('matriculas_efectivos') Matrículas efectivas @break
          @case('matriculas_deserciones') Deserciones (Matrículas) @break
          @case('total_miembros') Total miembros @break
          @case('miembros_ubicados') Miembros ubicados en grupos @break
          @case('union_libre_matriculados') Unión libre matriculados @break
          @case('miembros_formalizados') Miembros que estaban en unión libre (formalizados) @break
          @case('pendientes_membresia_union_libre') Pendientes por membresía (Unión libre) @break
          @case('bautismos') Bautismos @break
          @case('traslados') Traslados @break
          @default
              @if(str_starts_with($kpi, 'cosecha_vinculacion_')) Cosecha por vinculación
              @elseif(str_starts_with($kpi, 'traslados_')) Traslados: {{ str_contains($kpi, 'adultos') ? 'Adultos' : 'Warriors' }}
              @elseif(str_starts_with($kpi, 'bautismos_')) Bautismos: {{ str_contains($kpi, 'adultos') ? 'Adultos' : 'Warriors' }}
              @else KPI @endif
      @endswitch
    </h4>
  </div>

 <div class="text-black small mb-3">
    <span class="fw-semibold">Sede:</span> {{ $sede->nombre }}
    <span class="mx-1">|</span>
    <span class="fw-semibold">Rango:</span> {{ $rangoFechas }}
 </div>

 <form id="formFiltroKpi" method="GET" action="{{ route('sede.dashboardConsolidacion.detalleKpi', $sede) }}">
    <div class="row mt-10 mb-5">
        <input type="hidden" name="kpi" value="{{ $kpi }}">
        <input type="hidden" name="rango_fechas" value="{{ $rangoFechas }}">
        
        <div class="row w-100">
            <div class="col-9">
                <div class="input-group input-group-merge bg-white">
                    <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                    <input type="text" id="inputBuscar" class="form-control" name="buscar" placeholder="Buscar por nombre..." value="{{ request('buscar') }}" aria-label="Buscar..." aria-describedby="basic-addon-search31">
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end">
                <a href="{{ route('sede.dashboardConsolidacion.detalleKpi.exportar', [$sede, 'kpi' => $kpi, 'rango_fechas' => $rangoFechas, 'buscar' => request('buscar')]) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3">
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

        if (inputBuscar.value.length > 0) {
            inputBuscar.focus();
            inputBuscar.setSelectionRange(inputBuscar.value.length, inputBuscar.value.length);
        }
    });
 </script>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="fw-bold text-black">Nombre</th>
                    <th class="fw-bold text-black">Teléfono</th>
                    <th class="fw-bold text-black">Email</th>
                    <th class="fw-bold text-black">Fecha creación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr>
                    <td class="text-black">
                        <span class="fw-semibold">{{ $usuario->nombre(3) }}</span>
                        @if($usuario->trashed())
                            <br><small class="text-danger fw-bold"><i class="ti ti-user-x fs-6"></i> Dado de baja</small>
                        @endif
                    </td>
                    <td class="text-black">
                        @php
                            $telefonos = collect([$usuario->telefono_fijo, $usuario->telefono_movil, $usuario->telefono_otro])->filter();
                        @endphp
                        {{ $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'N/A' }}
                    </td>
                    <td class="text-black">
                        {{ $usuario->email ?? 'N/A' }}
                    </td>
                    <td class="text-black">
                        {{ $usuario->created_at->format('Y-m-d') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-black">
                        <h4>No se encontraron registros</h4>
                        <p class="text-muted">Intenta cambiar el término de búsqueda.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="card-footer">
        {{ $usuarios->appends(request()->query())->links() }}
    </div>
</div>
@endsection
