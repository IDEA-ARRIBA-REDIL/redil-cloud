@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Detalle KPI peticiones')

@section('content')


<div class="d-flex align-items-center mb-1">
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver al Dashboard">
        <i class="ti ti-arrow-left text-primary fs-4"></i>
    </a>
    <h4 class="mb-0 fw-semibold text-primary">Detalle de peticiones: 
    @if(isset($paisDetalle))
        País: {{ $paisDetalle->nombre }}
    @elseif(isset($tipoDetalle))
        Tipo: {{ $tipoDetalle->nombre }}
    @else
        @switch($kpi)
            @case('total')
                Todas
                @break
            @case('pendientes')
                Pendientes
                @break
            @case('en_proceso')
                En proceso
                @break
            @case('cerradas')
                Cerradas
                @break
            @case('sin_asignar')
                Sin asignar
                @break
            @default
                Métricas
        @endswitch
    @endif
    </h4>
  </div>

  <div class="text-black small mb-3">
    @if(isset($paisDetalle))
        <span class="fw-semibold">País:</span> {{ $paisDetalle->nombre }}
        <span class="mx-1">|</span>
    @endif
    @if(isset($tipoDetalle))
        <span class="fw-semibold">Tipo de Petición:</span> {{ $tipoDetalle->nombre }}
        <span class="mx-1">|</span>
    @endif
    @if($kpi && !isset($paisDetalle) && !isset($tipoDetalle))
        <span class="fw-semibold">Filtro KPI:</span> 
        <span class="text-capitalize">{{ str_replace('_', ' ', $kpi) }}</span>
        <span class="mx-1">|</span>
    @endif
    <span class="fw-semibold">Rango:</span> {{ $rangoFechas }}
  </div>

  <form id="formFiltroKpi" method="GET" action="{{ route('peticion.dashboard.detalle-kpi') }}">
    <div class="row mt-10 mb-5">
        <input type="hidden" name="kpi" value="{{ $kpi }}">
        @if(isset($paisId))
            <input type="hidden" name="pais_id" value="{{ $paisId }}">
        @endif
        @if(isset($tipoPeticionId))
            <input type="hidden" name="tipo_peticion_id" value="{{ $tipoPeticionId }}">
        @endif
        <input type="hidden" name="rango_fechas" value="{{ $rangoFechas }}">


        <div class="row w-100">
            <div class="col-9">
                <div class="input-group input-group-merge bg-white">
                    <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                    <input type="text" id="inputBuscar" class="form-control" name="buscar" placeholder="Buscar por nombre, correo, teléfono o descripción..." value="{{ request('buscar') }}" aria-label="Buscar..." aria-describedby="basic-addon-search31">
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end">
                <a href="{{ route('peticion.dashboard.detalle-kpi.exportar', request()->all()) }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3">
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

  <div class="card shadow-sm border-0 rounded-3">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="fw-bold text-black">Nombre</th>
                    <th class="fw-bold text-black">Teléfono</th>
                    <th class="fw-bold text-black">Email</th>
                    <th class="fw-bold text-black" style="width: 35%;">Petición</th>
                    <th class="fw-bold text-black">Tipo petición</th>
                    <th class="fw-bold text-black">Asignada a</th>
                    <th class="fw-bold text-black text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peticiones as $peticion)
                @php
                    // Resolver Nombre
                    if ($peticion->user_id) {
                        $nombre = trim($peticion->primer_nombre . ' ' . $peticion->segundo_nombre . ' ' . $peticion->primer_apellido);
                    } else {
                        $nombre = $peticion->nombre_externo ? $peticion->nombre_externo . ' (Externo)' : 'N/A';
                    }

                    // Resolver Teléfono
                    if ($peticion->user_id) {
                        $telefonos = collect([$peticion->telefono_fijo, $peticion->telefono_movil, $peticion->telefono_otro])->filter();
                        $telefono = $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'N/A';
                    } else {
                        $telefono = $peticion->telefono_externo ?? 'N/A';
                    }

                    // Resolver Email
                    if ($peticion->user_id) {
                        $email = $peticion->email ?? 'N/A';
                    } else {
                        $email = $peticion->email_externo ?? 'N/A';
                    }
                @endphp
                <tr>
                    <td class="text-black fw-semibold">
                        {{ $nombre }}
                    </td>
                    <td class="text-black">
                        {{ $telefono }}
                    </td>
                    <td class="text-black">
                        {{ $email }}
                    </td>
                    <td class="text-black text-wrap">
                        @if(strlen($peticion->descripcion) > 100)
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $peticion->descripcion }}">
                                {{ substr($peticion->descripcion, 0, 100) }}...
                            </span>
                        @else
                            {{ $peticion->descripcion }}
                        @endif
                    </td>
                    <td class="text-black">
                        {{ $peticion->tipoPeticion->nombre ?? 'N/A' }}
                    </td>
                    <td class="text-black">
                        @if($peticion->asignado)
                            <span class="fw-medium text-heading">{{ $peticion->asignado->nombre(3) }}</span>
                        @else
                            <span class="text-muted">Sin asignar</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @switch($peticion->estado)
                            @case(1)
                                <span class="badge bg-label-warning fw-semibold">Pendiente</span>
                                @break
                            @case(3)
                                <span class="badge bg-label-info fw-semibold">En proceso</span>
                                @break
                            @case(2)
                                <span class="badge bg-label-success fw-semibold">Cerrada</span>
                                @break
                            @default
                                <span class="badge bg-label-secondary fw-semibold">Desconocido</span>
                        @endswitch
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-black">
                        <h4>No se encontraron peticiones</h4>
                        <p class="text-muted">Intenta cambiar los filtros o el término de búsqueda.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="card-footer bg-transparent border-top">
        {{ $peticiones->appends(request()->query())->links() }}
    </div>
  </div>
@endsection
