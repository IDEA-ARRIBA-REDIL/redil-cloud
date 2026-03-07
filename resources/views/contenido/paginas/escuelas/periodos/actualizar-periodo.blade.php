@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Actualizar Periodo - ' . $periodo->nombre)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss','resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/js/app.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js','resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection

@section('page-script')
    {{-- Script para inicializar Flatpickr y Select2 --}}
    <script type="module">
        $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Selecciona una o más sedes',
                allowClear: true,
                width: '100%'
            });

            // INICIO: Lógica para los nuevos botones
            // Botón para SELECCIONAR todas las sedes
            $('#seleccionar-todas-sedes').on('click', function() {
                // Selecciona todas las etiquetas <option> dentro del select con id "sedes"
                $("#sedes > option").prop("selected", "selected");
                // Notifica a Select2 que el valor ha cambiado para que actualice la vista
                $("#sedes").trigger("change");
            });

            // Botón para QUITAR la selección de todas las sedes
            $('#quitar-todas-sedes').on('click', function() {
                // Establece el valor del select a null (vacío)
                $("#sedes").val(null);
                // Notifica a Select2 que el valor ha cambiado
                $("#sedes").trigger("change");
            });
            // FIN: Lógica para los nuevos botones
        });
    </script>
    
   {{-- Script para inicializar los gráficos de ApexCharts con el nuevo diseño --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const estadisticas = @json($estadisticas);
            const cardColor = 'rgba(var(--bs-body-color-rgb), .68)';
            const headingColor = 'rgba(var(--bs-body-color-rgb), .86)';
            
            const createRadialChartConfig = (selector, seriesValue, color, totalValue) => {
                const element = document.querySelector(selector);
                if (!element) return;

                const percentage = totalValue > 0 ? (seriesValue / totalValue) * 100 : 0;

                const config = {
                    chart: { height: 200, width: 200, type: 'radialBar' },
                    plotOptions: {
                        radialBar: {
                            hollow: { size: '55%' },
                            dataLabels: {
                                name: { show: false },
                                value: {
                                    show: true,
                                    offsetY: 8,
                                    fontSize: '1rem',
                                    fontWeight: '600',
                                    color: headingColor,
                                    formatter: (val) => `${Math.round(val)}%`
                                }
                            },
                            track: { background: 'rgba(var(--bs-secondary-rgb), 0.2)' }
                        }
                    },
                    colors: [color],
                    series: [percentage],
                    stroke: { lineCap: 'round' },
                };
                new ApexCharts(element, config).render();
            };

            createRadialChartConfig('#graficoAprobadas', estadisticas.totalAprobadas, config.colors.success, estadisticas.totalMatriculas);
            createRadialChartConfig('#graficoNoAprobadas', estadisticas.totalNoAprobadas, config.colors.danger, estadisticas.totalMatriculas);
            createRadialChartConfig('#graficoBloqueadas', estadisticas.totalBloqueadas, config.colors.warning, estadisticas.totalMatriculas);
            createRadialChartConfig('#graficoTraslados', estadisticas.totalTraslados, config.colors.info, estadisticas.totalMatriculas);
        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Cabecera y Navegación de Pestañas --}}
    <div class="row">
        <h4 class="mb-1 text-primary">Actualizar periodo: {{ $periodo->nombre }}</h4>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 p-1">
                <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2">
                    <li class="nav-item flex-fill text-center"><a href="{{ route('periodo.actualizar', $periodo) }}" class="nav-link p-3 waves-effect active"><i class="ti-xs ti me-2 ti-info-hexagon"></i> Datos principales</a></li>
                    <li class="nav-item flex-fill text-center"><a href="{{ route('periodo.cortes', $periodo) }}" class="nav-link p-3 waves-effect"><i class="ti-xs ti me-2 ti-clock"></i> Cortes </a></li>
                    <li class="nav-item flex-fill text-center"><a href="{{ route('periodo.materias', $periodo) }}" class="nav-link p-3 waves-effect"><i class="ti-xs ti me-2 ti-template"></i> Materias</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Formulario de Datos Principales --}}
    <form id="formulario" method="POST" action="{{ route('periodo.procesarActualizacion', $periodo) }}">
        @csrf
        <div class="row">
            <div class="card col-12 mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Información principal</h5>
                </div>
                <div class="card-body row">
                    {{-- Campo Nombre --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input value="{{ old('nombre', $periodo->nombre) }}" type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" placeholder="Ej: Semestre 2025-1">
                        @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    {{-- Campo Escuela asociada --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label for="escuelaId" class="form-label">Escuela asociada</label>
                        <select disabled id="escuelaId" name="escuelaId" class="form-select @error('escuelaId') is-invalid @enderror">
                            @foreach ($escuelas as $escuela)
                                <option value="{{ $escuela->id }}" {{ $periodo->escuela_id == $escuela->id ? 'selected' : '' }}>{{ $escuela->nombre }}</option>
                            @endforeach
                        </select>
                        @error('escuelaId')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    {{-- Campo Sistema de calificaciones --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label for="sistema_calificacion_id" class="form-label">Sistema de calificaciones</label>
                        <select id="sistema_calificacion_id" name="sistema_calificacion_id" class="form-select @error('sistema_calificacion_id') is-invalid @enderror">
                            @foreach ($sistemasCalifiacion as $sistema)
                                <option value="{{ $sistema->id }}" {{ $periodo->sistema_calificaciones_id == $sistema->id ? 'selected' : '' }}>{{ $sistema->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sistema_calificacion_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    {{-- Campo Fecha inicio periodo --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label class="form-label" for="fecha_inicio">Fecha inicio periodo</label>
                        <input id="fecha_inicio" value="{{ old('fecha_inicio', $periodo->fecha_inicio?->format('Y-m-d')) }}" placeholder="YYYY-MM-DD" name="fecha_inicio" class="form-control fecha-picker @error('fecha_inicio') is-invalid @enderror" type="text" />
                        @error('fecha_inicio')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    {{-- Campo Fecha finalización periodo --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label class="form-label" for="fecha_fin">Fecha finalización periodo</label>
                        <input id="fecha_fin" value="{{ old('fecha_fin', $periodo->fecha_fin?->format('Y-m-d')) }}" placeholder="YYYY-MM-DD" name="fecha_fin" class="form-control fecha-picker @error('fecha_fin') is-invalid @enderror" type="text" />
                        @error('fecha_fin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    {{-- Campo Fecha limite calificaciones maestro --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label class="form-label" for="fecha_limite_maestro">Fecha límite calificaciones maestro</label>
                        <input id="fecha_limite_maestro" value="{{ old('fecha_limite_maestro', $periodo->fecha_maxima_entrega_notas?->format('Y-m-d')) }}" placeholder="YYYY-MM-DD" name="fecha_limite_maestro" class="form-control fecha-picker @error('fecha_limite_maestro') is-invalid @enderror" type="text" />
                        @error('fecha_limite_maestro')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    {{-- Campo Sedes habilitadas (Select2 Múltiple) --}}
                    <div class="col-12 mb-3">
                        <label for="sedes" class="form-label">Sedes habilitadas </label>
                        {{-- INICIO: Botones para seleccionar/quitar todas --}}
                     
                            <a href="javascript:;" id="seleccionar-todas-sedes" ><span class="fw-medium"> Seleccionar todas </span> </a> | 
                            <a href="javascript:;" id="quitar-todas-sedes" > <span class="fw-medium"> Quitar todas </span> </a>
                       
                        {{-- FIN: Botones --}}
                        <select id="sedes" name="sedes[]" class="select2 form-select @error('sedes') is-invalid @enderror" multiple>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}" {{ in_array($sede->id, old('sedes', $sedesPeriodo)) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sedes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            
            {{-- Botonera --}}
            <div class="col-12 d-flex justify-content-start mb-4">
                <a href="{{ route('periodo.gestionar') }}" class="btn rounded-pill btn-outline-secondary px-4 me-2">Atrás</a>
                <button type="submit" class="btn rounded-pill btn-primary px-4">Actualizar</button>
            </div>
        </div>
    </form>
    
    {{-- Sección de Estadísticas --}}
    <div class="row g-4">
        
        <div class="col-12 col-md-6">
            <div class="border rounded p-3 d-flex align-items-center h-100">
                <div id="graficoAprobadas" class="card"></div>
                <div class="ms-3">
                    <h6 class="mb-0">Aprobadas</h6>
                    <p class="text-muted mb-0 small">Personas que aprobaron</p>
                    <p class="fw-semibold mb-0">{{ $estadisticas['totalAprobadas'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="border rounded p-3 d-flex align-items-center h-100">
                <div id="graficoNoAprobadas" class="card"></div>
                <div class="ms-3">
                    <h6 class="mb-0">No aprobadas</h6>
                    <p class="text-muted mb-0 small">Personas que perdieron</p>
                    <p class="fw-semibold mb-0">{{ $estadisticas['totalNoAprobadas'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="border rounded p-3 d-flex align-items-center h-100">
                <div id="graficoBloqueadas" class="card"></div>
                <div class="ms-3">
                    <h6 class="mb-0">Bloqueadas</h6>
                    <p class="text-muted mb-0 small">Personas bloqueadas</p>
                    <p class="fw-semibold mb-0">{{ $estadisticas['totalBloqueadas'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="border rounded p-3 d-flex align-items-center h-100">
                <div id="graficoTraslados" class="card"></div>
                <div class="ms-3">
                    <h6 class="mb-0">Traslados</h6>
                    <p class="text-muted mb-0 small">Traslados realizados</p>
                    <p class="fw-semibold mb-0">{{ $estadisticas['totalTraslados'] }}</p>
                </div>
            </div>
        </div>

    </div>
@endsection