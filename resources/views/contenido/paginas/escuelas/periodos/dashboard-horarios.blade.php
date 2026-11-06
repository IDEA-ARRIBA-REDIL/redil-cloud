@section('isEscuelasModule', true)
@extends('layouts/layoutMaster')
@section('title', 'Dashboard de horarios')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    @include('layouts.status-msn')
    @include('contenido.paginas.escuelas.periodos.resumen-dashboard-horarios')
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof ApexCharts === 'undefined') return;

        // 1. Gráfico de Horarios por Materia
        const contenedorMaterias = document.getElementById('grafico-horarios-por-materia');
        if (contenedorMaterias) {
            const labelsMaterias = JSON.parse(contenedorMaterias.dataset.labels || '[]');
            const seriesMaterias = JSON.parse(contenedorMaterias.dataset.series || '[]');

            if (labelsMaterias.length > 0) {
                const alturaCalculada = Math.max(280, labelsMaterias.length * 36);
                new ApexCharts(contenedorMaterias, {
                    chart: {
                        type: 'bar',
                        height: alturaCalculada,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4,
                            barHeight: '65%',
                            distributed: false
                        }
                    },
                    colors: ['#7367f0'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: function (val) {
                            return val + (val === 1 ? ' horario' : ' horarios');
                        }
                    },
                    series: [{
                        name: 'Horarios asignados',
                        data: seriesMaterias
                    }],
                    xaxis: {
                        categories: labelsMaterias,
                        labels: {
                            formatter: function (val) { return parseInt(val); }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { fontSize: '12px' },
                            maxWidth: 240
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val + (val === 1 ? ' horario configurado' : ' horarios configurados');
                            }
                        }
                    }
                }).render();
            }
        }

        // 2. Gráfico de Distribución por Día de la Semana
        const contenedorDias = document.getElementById('grafico-horarios-dias');
        if (contenedorDias) {
            const labelsDias = JSON.parse(contenedorDias.dataset.labels || '[]');
            const seriesDias = JSON.parse(contenedorDias.dataset.series || '[]');

            if (labelsDias.length > 0) {
                new ApexCharts(contenedorDias, {
                    chart: {
                        type: 'bar',
                        height: 250,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '50%',
                            distributed: true
                        }
                    },
                    colors: ['#7367f0', '#00cfe8', '#28c76f', '#ff9f43', '#ea5455', '#4b4b4b', '#82868b'],
                    legend: { show: false },
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px' }
                    },
                    series: [{
                        name: 'Horarios',
                        data: seriesDias
                    }],
                    xaxis: {
                        categories: labelsDias
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        decimalsInFloat: 0
                    }
                }).render();
            }
        }

        // 3. Gráfico de Distribución y Proporción por Sede (A TODO EL ANCHO)
        const contenedorSedes = document.getElementById('grafico-horarios-sedes');
        if (contenedorSedes) {
            const labelsSedes = JSON.parse(contenedorSedes.dataset.labels || '[]');
            const seriesSedes = JSON.parse(contenedorSedes.dataset.series || '[]');
            const porcentajesSedes = JSON.parse(contenedorSedes.dataset.porcentajes || '[]');
            const inscritosSedes = JSON.parse(contenedorSedes.dataset.inscritos || '[]');

            if (labelsSedes.length > 0 && seriesSedes.some(s => s > 0)) {
                const alturaCalculadaSedes = Math.max(340, labelsSedes.length * 36);
                new ApexCharts(contenedorSedes, {
                    chart: {
                        type: 'bar',
                        height: alturaCalculadaSedes,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4,
                            barHeight: '65%',
                            distributed: false
                        }
                    },
                    colors: ['#7367f0'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: function (val, opt) {
                            const pct = porcentajesSedes[opt.dataPointIndex] !== undefined ? porcentajesSedes[opt.dataPointIndex] : 0;
                            return val + (val === 1 ? ' horario' : ' horarios') + ' (' + pct + '%)';
                        }
                    },
                    series: [{
                        name: 'Horarios configurados',
                        data: seriesSedes
                    }],
                    xaxis: {
                        categories: labelsSedes,
                        labels: {
                            formatter: function (val) { return parseInt(val); }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { fontSize: '12px' },
                            maxWidth: 260
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val, opt) {
                                const pct = porcentajesSedes[opt.dataPointIndex] !== undefined ? porcentajesSedes[opt.dataPointIndex] : 0;
                                const insc = inscritosSedes[opt.dataPointIndex] !== undefined ? inscritosSedes[opt.dataPointIndex] : 0;
                                return val + (val === 1 ? ' horario' : ' horarios') + ' (' + pct + '% del periodo) · ' + insc + ' alumnos inscritos';
                            }
                        }
                    }
                }).render();
            }
        }

        // 4. Gráficos Proporcionales dentro de cada Sede (Acordeón)
        const sedesDibujadas = new Set();
        function inicializarGraficosSede(panel) {
            if (!panel) return;

            // A. Días con clases en la sede
            panel.querySelectorAll('[id^="grafico-sede-dias-"]').forEach(function (c) {
                if (sedesDibujadas.has(c.id)) return;
                sedesDibujadas.add(c.id);
                const labels = JSON.parse(c.dataset.labels || '[]');
                const series = JSON.parse(c.dataset.series || '[]');
                if (series.some(s => s > 0)) {
                    new ApexCharts(c, {
                        chart: { type: 'bar', height: 180, toolbar: { show: false } },
                        plotOptions: { bar: { borderRadius: 3, columnWidth: '55%', distributed: true } },
                        colors: ['#7367f0', '#00cfe8', '#28c76f', '#ff9f43', '#ea5455', '#4b4b4b', '#82868b'],
                        legend: { show: false },
                        dataLabels: { enabled: true, style: { fontSize: '10px' } },
                        series: [{ name: 'Horarios', data: series }],
                        xaxis: { categories: labels, labels: { style: { fontSize: '10px' } } },
                        yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0 }
                    }).render();
                } else {
                    c.innerHTML = '<p class="text-muted small text-center my-3">Sin horarios registrados</p>';
                }
            });

            // B. Materias en la sede
            panel.querySelectorAll('[id^="grafico-sede-materias-"]').forEach(function (c) {
                if (sedesDibujadas.has(c.id)) return;
                sedesDibujadas.add(c.id);
                const labels = JSON.parse(c.dataset.labels || '[]');
                const series = JSON.parse(c.dataset.series || '[]');
                if (series.some(s => s > 0)) {
                    if (labels.length === 1) {
                        new ApexCharts(c, {
                            chart: { type: 'bar', height: 180, toolbar: { show: false } },
                            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '50%' } },
                            colors: ['#ff9f43'],
                            dataLabels: { enabled: true, formatter: (v) => v + (v === 1 ? ' horario' : ' horarios') },
                            series: [{ name: 'Horarios', data: series }],
                            xaxis: { categories: labels },
                            yaxis: { labels: { maxWidth: 120, style: { fontSize: '10px' } } }
                        }).render();
                    } else {
                        new ApexCharts(c, {
                            chart: { type: 'donut', height: 180, toolbar: { show: false } },
                            series: series,
                            labels: labels,
                            colors: ['#7367f0', '#00cfe8', '#ff9f43', '#28c76f', '#ea5455'],
                            legend: { position: 'bottom', fontSize: '10px' },
                            plotOptions: { pie: { donut: { size: '60%' } } },
                            dataLabels: { enabled: true }
                        }).render();
                    }
                } else {
                    c.innerHTML = '<p class="text-muted small text-center my-3">Sin materias registradas</p>';
                }
            });

            // C. Ocupación de cupos en la sede
            panel.querySelectorAll('[id^="grafico-sede-ocupacion-"]').forEach(function (c) {
                if (sedesDibujadas.has(c.id)) return;
                sedesDibujadas.add(c.id);
                const inscritos = parseInt(c.dataset.inscritos || 0);
                const disponibles = parseInt(c.dataset.disponibles || 0);
                if (inscritos + disponibles > 0) {
                    new ApexCharts(c, {
                        chart: { type: 'donut', height: 180, toolbar: { show: false } },
                        series: [inscritos, disponibles],
                        labels: ['Inscritos', 'Cupos libres'],
                        colors: ['#7367f0', '#e0e0e0'],
                        legend: { position: 'bottom', fontSize: '10px' },
                        plotOptions: { pie: { donut: { size: '65%' } } },
                        dataLabels: { enabled: true }
                    }).render();
                } else {
                    c.innerHTML = '<p class="text-muted small text-center my-3">Sin cupos definidos</p>';
                }
            });
        }

        // Renderizar en los paneles abiertos y al abrir acordeón
        document.querySelectorAll('.accordion-collapse').forEach(function (panel) {
            if (panel.classList.contains('show')) {
                inicializarGraficosSede(panel);
            }
            panel.addEventListener('shown.bs.collapse', function () {
                inicializarGraficosSede(panel);
            });
        });
    });
</script>
@endsection
