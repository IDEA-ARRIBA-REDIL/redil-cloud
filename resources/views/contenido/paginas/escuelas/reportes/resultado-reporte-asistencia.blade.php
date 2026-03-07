@extends('layouts.layoutMaster')

@section('title', 'Resultado del Reporte de Asistencia')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Reporte de Asistencia</h4>
        <p class="mb-0"><strong>Periodo:</strong> {{ $periodo->nombre }}</p>
        <p class="mb-0"><strong>Materia:</strong> {{ $materia->nombre }}</p>
        <p class="mb-0"><strong>Semana:</strong> {{ $infoSemana }}</p>
    </div>
    <div class="card-body">
        @forelse($datosReporte as $reporteSede)
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            {{-- El encabezado de la sede ahora abarca 7 columnas --}}
                            <th colspan="7">SEDE: {{ $reporteSede['nombre'] }}</th>
                        </tr>
                        <tr>
                            <th>HORARIO</th>
                            <th>AULA</th>
                            {{-- Se añade la nueva columna de Total en el encabezado --}}
                            <th class="text-center">TOTAL</th>
                            <th class="text-center">ASISTIÓ</th>
                            <th class="text-center">NO ASISTIÓ</th>
                            <th class="text-center">NO REPORTADO</th>
                            <th class="text-center">DESERCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reporteSede['horarios'] as $itemHorario)
                            @php
                                list($diaHora, $aulaInfo) = explode(' - Aula: ', $itemHorario['info']);
                                // Se calcula el total de matrículas para esta fila
                                $totalMatriculasHorario = array_sum($itemHorario['contadores']);
                            @endphp
                            <tr>
                                <td>{{ $diaHora }}</td>
                                <td>{{ $aulaInfo }}</td>
                                {{-- Se añade la celda con el total del horario --}}
                                <td class="text-center fw-bold">{{ $totalMatriculasHorario }}</td>
                                <td class="text-center">{{ $itemHorario['contadores']['asistio'] }}</td>
                                <td class="text-center">{{ $itemHorario['contadores']['ausente'] }}</td>
                                <td class="text-center">{{ $itemHorario['contadores']['no_registrado'] }}</td>
                                <td class="text-center">{{ $itemHorario['contadores']['desercion'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            {{-- AJUSTE CLAVE: LA FILA DE TOTALES --}}
                            <th colspan="2" class="text-end">TOTALES DE LA SEDE</th>
                            @php
                                // Se calcula el total general de la sede
                                $totalGeneralSede = array_sum($reporteSede['totales']);
                            @endphp
                            {{-- Se añade la celda para el total general de la sede --}}
                            <th class="text-center fs-5">{{ $totalGeneralSede }}</th>
                            {{-- Se mantienen las celdas con el desglose --}}
                            <th class="text-center">{{ $reporteSede['totales']['asistio'] }}</th>
                            <th class="text-center">{{ $reporteSede['totales']['ausente'] }}</th>
                            <th class="text-center">{{ $reporteSede['totales']['no_registrado'] }}</th>
                            <th class="text-center">{{ $reporteSede['totales']['desercion'] }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @empty
            <div class="alert alert-warning" role="alert">
                No se encontraron datos para los filtros seleccionados.
            </div>
        @endforelse

        {{-- MEJORA: Tabla de Gran Total para todo el reporte --}}
        @if(!empty($datosReporte))
            <hr>
            <h5 class="mt-4 mb-3">GRAN TOTAL (Todas las sedes seleccionadas)</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                         <tr>
                            <th class="text-center">TOTAL MATRÍCULAS</th>
                            <th class="text-center">TOTAL ASISTIÓ</th>
                            <th class="text-center">TOTAL NO ASISTIÓ</th>
                            <th class="text-center">TOTAL NO REPORTADO</th>
                            <th class="text-center">TOTAL DESERCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $granTotalGeneral = array_sum($granTotalReporte);
                        @endphp
                        <tr>
                            <td class="text-center fw-bold fs-4">{{ $granTotalGeneral }}</td>
                            <td class="text-center">{{ $granTotalReporte['asistio'] }}</td>
                            <td class="text-center">{{ $granTotalReporte['ausente'] }}</td>
                            <td class="text-center">{{ $granTotalReporte['no_registrado'] }}</td>
                            <td class="text-center">{{ $granTotalReporte['desercion'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endsection