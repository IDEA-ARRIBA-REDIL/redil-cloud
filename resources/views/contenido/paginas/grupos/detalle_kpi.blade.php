@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard Grupos - ' . ucfirst($kpi))

@section('content')

  <h4 class="mb-1 fw-semibold text-primary">Detalle de grupos: 
    @switch($kpi)
        @case('total')
            Total Grupos (Activos en periodo)
            @break
        @case('nuevos')
            Nuevos
            @break
        @case('bajas')
            Bajas
            @break
        @case('inactivos')
            Inactivos
            @break
        @default
            Grupos
    @endswitch
 </h4>

 <div class="text-black small mb-3">
    <span class="fw-semibold">Rango:</span> {{ $rangoFechas }}
    
    <span class="mx-1">|</span>
    
    <span class="fw-semibold">Sedes:</span> 
    @if(isset($sedesSeleccionadas) && count($sedesSeleccionadas) > 0)
        @if(count($sedesSeleccionadas) == count($mapaSedes))
            Todas
        @else
            @foreach($sedesSeleccionadas as $sedeId)
                {{ isset($mapaSedes[$sedeId]) ? $mapaSedes[$sedeId]->nombre : $sedeId }}@if(!$loop->last), @endif
            @endforeach
        @endif
    @else
        Todas
    @endif

    <span class="mx-1">|</span>

    <span class="fw-semibold">Tipos:</span>
    @if(isset($tiposSeleccionados) && count($tiposSeleccionados) > 0)
         @if(count($tiposSeleccionados) == count($mapaTipos))
            Todos
        @else
            @foreach($tiposSeleccionados as $tipoId)
                {{ isset($mapaTipos[$tipoId]) ? $mapaTipos[$tipoId]->nombre : $tipoId }}@if(!$loop->last), @endif
            @endforeach
        @endif
    @else
        Ninguno
    @endif
 </div>



 <form id="formFiltroKpi" method="GET" action="{{ route('grupos.detalle-kpi') }}">
    <div class="row mt-10 mb-5">
        <!-- Maintain existing filters -->
        <input type="hidden" name="kpi" value="{{ $kpi }}">
        <input type="hidden" name="rango_fechas" value="{{ request('rango_fechas') }}">
        <!-- Arrays need special handling if not automatically handled by form submission of hidden inputs, but here we just pass them if they exist -->
        @if(request('filtro_tipo_grupo'))
            @foreach(request('filtro_tipo_grupo') as $val)
                <input type="hidden" name="filtro_tipo_grupo[]" value="{{ $val }}">
            @endforeach
        @endif
        <input type="hidden" name="filtro_bloques" value="{{ request('filtro_bloques') }}">
        <input type="hidden" name="filtro_sedes" value="{{ request('filtro_sedes') }}">

        <div class="row w-100">
            <div class="col-10 col-md-10">
                <div class="input-group input-group-merge bg-white">
                    <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                    <input type="text" id="inputBuscar" class="form-control" name="buscar" placeholder="Buscar por nombre de grupo..." value="{{ request('buscar') }}" aria-label="Buscar..." aria-describedby="basic-addon-search31">
                </div>
            </div>
            <div class="col-2 col-md-2 d-flex justify-content-end">
                <a href="{{ route('grupos.detalle-kpi.exportar', request()->all()) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3">
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
            }, 800); // 800ms delay
        });

        // Focus al volver de la recarga si hay búsqueda (opcional, pero mejora UX)
        // Como es una recarga completa, el focus se pierde. 
        // Si se quiere mantener el focus, se requiere JS adicional post-carga.
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('buscar')) {
             const searchVal = urlParams.get('buscar');
             if(searchVal && searchVal.length > 0) {
                 // inputBuscar.focus(); // A veces molesto si scroll cambia
                 // inputBuscar.setSelectionRange(searchVal.length, searchVal.length);
             }
        }
    });
 </script>




<div class="card">
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="fw-bold text-black">ID</th>
                    <th class="fw-bold text-black">Nombre</th>
                    <th class="fw-bold text-black">Sede</th>
                    <th class="fw-bold text-black">Tipo</th>
                    <th class="fw-bold text-black">Fecha Apertura</th>
                    @if($kpi == 'bajas')
                        <th class="fw-bold text-black">Fecha Baja</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($grupos as $grupo)
                <tr>
                    <td class="text-black">{{ $grupo->id }}</td>
                    <td class="text-black">
                        <span class="fw-semibold">{{ $grupo->nombre }}</span>
                        @if($grupo->encargados->count() > 0)
                            <br><small class="text-muted">{{ $grupo->encargados->first()->nombre(2) }}</small>
                        @endif
                    </td>
                    <td class="text-black">
                        @if($grupo->sede_historica_id && isset($mapaSedes[$grupo->sede_historica_id]))
                            {{ $mapaSedes[$grupo->sede_historica_id]->nombre }} 
                        @else
                            {{ $grupo->sede->nombre ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="text-black">
                        @if($grupo->tipo_historico_id && isset($mapaTipos[$grupo->tipo_historico_id]))
                            {{ $mapaTipos[$grupo->tipo_historico_id]->nombre }} 
                        @else
                            {{ $grupo->tipoGrupo->nombre ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="text-black">{{ $grupo->fecha_apertura }}</td>
                     @if($kpi == 'bajas')
                        <td>
                            @php
                                // Attempt to find baja date from reportes (logic might need refinement based on controller query)
                                $reporteBaja = $grupo->reportesBajaAlta()
                                                     ->where('dado_baja', true)
                                                     ->orderBy('fecha', 'desc') // or created_at?
                                                     ->first();
                            @endphp
                            {{ $reporteBaja ? $reporteBaja->fecha : 'N/A' }}
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $kpi == 'bajas' ? 7 : 6 }}" class="text-center py-5 text-black">
                        <h4>No se encontraron grupos</h4>
                        <p class="text-muted">Intenta cambiar los filtros o el término de búsqueda.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="card-footer">
        {{ $grupos->appends(request()->query())->links() }}
    </div>
</div>
@endsection
