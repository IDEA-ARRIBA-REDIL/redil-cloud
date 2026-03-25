@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Detalle KPI Consolidación')

@section('content')

  <h4 class="mb-1 fw-semibold text-primary">Detalle de Consolidación: 
    @switch($kpi)
        @case('cosecha_total')
            Total cosecha
            @break
        @case('cosecha_efectiva')
            Cosecha efectiva
            @break
        @case('sin_gestion')
            Sin gestión de tareas
            @break
        @case('matriculas')
            Matrículas
            @break
        @default
            @if(isset($paso))
                {{ $paso->nombre }}
            @else
                KPI
            @endif
    @endswitch
 </h4>

 <div class="text-black small mb-3">
    <span class="fw-semibold">Zona:</span> {{ $zona->nombre }}
    @if(isset($sede))
        <span class="mx-1">|</span>
        <span class="fw-semibold">Sede:</span> {{ $sede->nombre }}
    @endif
    <span class="mx-1">|</span>
    <span class="fw-semibold">Rango:</span> {{ $rangoFechas }}
 </div>

 <form id="formFiltroKpi" method="GET" action="{{ route('consolidacion.detalle-kpi') }}">
    <div class="row mt-10 mb-5">
        <input type="hidden" name="kpi" value="{{ $kpi }}">
        <input type="hidden" name="zona_id" value="{{ $zona->id }}">
        @if(isset($sede))
            <input type="hidden" name="sede_id" value="{{ $sede->id }}">
        @endif
        <input type="hidden" name="rango_fechas" value="{{ $rangoFechas }}">

        <div class="row w-100">
            <div class="col-10">
                <div class="input-group input-group-merge bg-white">
                    <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                    <input type="text" id="inputBuscar" class="form-control" name="buscar" placeholder="Buscar por nombre..." value="{{ request('buscar') }}" aria-label="Buscar..." aria-describedby="basic-addon-search31">
                </div>
            </div>
            <div class="col-2 d-flex justify-content-end">
                <a href="{{ route('consolidacion.detalle-kpi.exportar', request()->all()) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3">
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

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="fw-bold text-black">Nombre</th>
                    <th class="fw-bold text-black">Teléfono</th>
                    <th class="fw-bold text-black">Email</th>
                    <th class="fw-bold text-black">Sede</th>
                    <th class="fw-bold text-black">Fecha Creación</th>
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
                        {{ $usuario->sede->nombre ?? 'N/A' }}
                    </td>
                    <td class="text-black">
                        {{ $usuario->created_at->format('Y-m-d') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-black">
                        <h4>No se encontraron registros</h4>
                        <p class="text-muted">Intenta cambiar los filtros o el término de búsqueda.</p>
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
