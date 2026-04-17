<div wire:init="loadCharts">
    <div class="row">
        <!-- Filtros de Dashboard -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" wire:model.live="fechaInicio">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" wire:model.live="fechaFin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Carrera (Carrera)</label>
                            <select class="form-select" wire:model.live="carreraId">
                                <option value="">Todas las Carreras</option>
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary" wire:click="$refresh">
                                <i class="fas fa-sync-alt me-1"></i> Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de KPIs -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md bg-label-primary me-3">
                        <span class="avatar-initial rounded"><i class="fas fa-users"></i></span>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $totalInscritos }}</h4>
                        <small class="text-muted">Nuevos Inscritos</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md bg-label-success me-3">
                        <span class="avatar-initial rounded"><i class="fas fa-chart-line"></i></span>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $promedioAvance }}%</h4>
                        <small class="text-muted">Progreso Promedio</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico por Sexo -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Distribución por Sexo</h5>
                </div>
                <div class="card-body">
                    <div id="chartSexo"></div>
                </div>
            </div>
        </div>

        <!-- Gráfico por Roles -->
        <div class="col-md-6 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Usuarios por Roles</h5>
                </div>
                <div class="card-body">
                    <div id="chartRoles"></div>
                </div>
            </div>
        </div>

        <!-- Gráfico por Entidades (Global) -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Usuarios por organización</h5>
                </div>
                <div class="card-body">
                    <div id="chartEntidades"></div>
                </div>
            </div>
        </div>

        <!-- Gráfico por Entidades (Inscritos) -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Inscripciones por organización</h5>
                </div>
                <div class="card-body">
                    <div id="chartInscritosEntidad"></div>
                </div>
            </div>
        </div>

        <!-- Gráfico por Cursos -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inscripciones por Curso</h5>
                </div>
                <div class="card-body pb-0">
                    <div id="chartCursos"></div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:navigated', () => {
                initCharts();
            });

            document.addEventListener('livewire:load', () => {
                initCharts();
            });

            // Escuchar actualizaciones de Livewire para refrescar los gráficos
            window.addEventListener('contentChanged', event => {
                initCharts();
            });

            function initCharts() {
                // Datos de Sexo (0=Masculino, 1=Femenino)
                const generoData = @json($datosGenero);
                const generoSeries = generoData.map(item => item.total);
                const generoLabels = generoData.map(item => item.genero == 0 ? 'Masculino' : (item.genero == 1 ? 'Femenino' :
                    'Otro'));

                const optionsSexo = {
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    series: generoSeries,
                    labels: generoLabels,
                    colors: ['#2196F3', '#E91E63', '#FFC107'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true
                    }
                };

                const chartSexo = new ApexCharts(document.querySelector("#chartSexo"), optionsSexo);
                chartSexo.render();

                // Datos de Roles
                const rolesData = @json($datosRoles);
                const rolesLabels = rolesData.map(item => item.rol);
                const rolesSeries = [{
                    data: rolesData.map(item => item.total)
                }];

                const optionsRoles = {
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    series: rolesSeries,
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true
                        }
                    },
                    xaxis: {
                        categories: rolesLabels
                    },
                    colors: ['#673AB7']
                };

                const chartRoles = new ApexCharts(document.querySelector("#chartRoles"), optionsRoles);
                chartRoles.render();

                // Datos por Curso
                const cursosData = @json($inscritosPorCurso);
                const cursosLabels = cursosData.map(item => item.nombre);
                const cursosSeries = [{
                    name: 'Inscritos',
                    data: cursosData.map(item => item.total)
                }];

                const optionsCursos = {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    series: cursosSeries,
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '45%'
                        }
                    },
                    xaxis: {
                        categories: cursosLabels
                    },
                    colors: ['#FF9800'],
                    title: {
                        text: 'Total inscritos por curso',
                        align: 'center'
                    }
                };

                const chartCursos = new ApexCharts(document.querySelector("#chartCursos"), optionsCursos);
                chartCursos.render();

                // Datos de Entidades (Global)
                const entidadData = @json($datosEntidad);
                const entidadLabels = entidadData.map(item => item.entidad);
                const entidadSeries = entidadData.map(item => item.total);

                const optionsEntidad = {
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    series: entidadSeries,
                    labels: entidadLabels,
                    colors: ['#4CAF50', '#2196F3', '#FFC107', '#9C27B0', '#FF5722'],
                    legend: {
                        position: 'bottom'
                    }
                };

                const chartEntidad = new ApexCharts(document.querySelector("#chartEntidades"), optionsEntidad);
                chartEntidad.render();

                // Datos de Inscritos por Entidad
                const inscritosEntidadData = @json($inscritosPorEntidad);
                const inscritosEntidadLabels = inscritosEntidadData.map(item => item.entidad);
                const inscritosEntidadSeries = [{
                    name: 'Asociados',
                    data: inscritosEntidadData.map(item => item.total)
                }];

                const optionsInscritosEntidad = {
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    series: inscritosEntidadSeries,
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true
                        }
                    },
                    xaxis: {
                        categories: inscritosEntidadLabels
                    },
                    colors: ['#03A9F4']
                };

                const chartInscritosEntidad = new ApexCharts(document.querySelector("#chartInscritosEntidad"), optionsInscritosEntidad);
                chartInscritosEntidad.render();
            }
        </script>
    @endpush
</div>
