@section('isEscuelasModule', true)
@extends('layouts/layoutMaster')
@section('title', 'Dashboard del periodo')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    @include('layouts.status-msn')
    @include('contenido.paginas.escuelas.periodos.resumen-dashboard-periodo')
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dibujadas = new Set();

        function inicializarGrafico(contenedor) {
            if (!contenedor || dibujadas.has(contenedor.id) || typeof ApexCharts === 'undefined') return;
            dibujadas.add(contenedor.id);

            if (contenedor.id.startsWith('grafico-resultados-')) {
                const aprobados = parseInt(contenedor.dataset.aprobados || 0);
                const riesgo = parseInt(contenedor.dataset.riesgo || 0);
                const pendientes = parseInt(contenedor.dataset.pendientes || 0);
                const cerrada = contenedor.dataset.cerrada === '1';

                if (aprobados + riesgo + pendientes > 0) {
                    new ApexCharts(contenedor, {
                        chart: { type: 'donut', height: 230, toolbar: { show: false } },
                        series: [aprobados, riesgo, pendientes],
                        labels: [cerrada ? 'Aprobados' : 'Cumplirían hoy', cerrada ? 'Reprobados' : 'Van a perder', 'Sin evaluar'],
                        colors: ['#28c76f', '#ea5455', '#ff9f43'],
                        legend: { position: 'bottom' },
                        plotOptions: { pie: { donut: { size: '60%' } } },
                        dataLabels: { enabled: true }
                    }).render();
                }
            } else if (contenedor.id.startsWith('grafico-bloqueo-')) {
                const noBloqueadas = parseInt(contenedor.dataset.nobloqueadas || 0);
                const bloqueadas = parseInt(contenedor.dataset.bloqueadas || 0);

                if (noBloqueadas + bloqueadas > 0) {
                    new ApexCharts(contenedor, {
                        chart: { type: 'donut', height: 230, toolbar: { show: false } },
                        series: [noBloqueadas, bloqueadas],
                        labels: ['No bloqueadas', 'Bloqueadas'],
                        colors: ['#28c76f', '#ea5455'],
                        legend: { position: 'bottom' },
                        plotOptions: { pie: { donut: { size: '60%' } } },
                        dataLabels: { enabled: true }
                    }).render();
                }
            } else if (contenedor.id.startsWith('grafico-generos-')) {
                const fem = parseInt(contenedor.dataset.femenino || 0);
                const masc = parseInt(contenedor.dataset.masculino || 0);
                const sin = parseInt(contenedor.dataset.sin || 0);

                if (fem + masc + sin > 0) {
                    new ApexCharts(contenedor, {
                        chart: { type: 'bar', height: 230, toolbar: { show: false } },
                        series: [{ name: 'Estudiantes', data: [fem, masc, sin] }],
                        xaxis: { categories: ['Femenino', 'Masculino', 'Sin registrar'] },
                        yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0 },
                        colors: ['#7367f0', '#00cfe8', '#82868b'],
                        plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '45%' } },
                        legend: { show: false }, dataLabels: { enabled: true }
                    }).render();
                }
            } else if (contenedor.id.startsWith('grafico-asistencias-')) {
                const presentes = parseInt(contenedor.dataset.presentes || 0);
                const ausentes = parseInt(contenedor.dataset.ausentes || 0);

                if (presentes + ausentes > 0) {
                    new ApexCharts(contenedor, {
                        chart: { type: 'bar', height: 230, toolbar: { show: false } },
                        series: [{ name: 'Registros', data: [presentes, ausentes] }],
                        xaxis: { categories: ['Asistencias', 'Inasistencias'] },
                        yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0 },
                        colors: ['#28c76f', '#ea5455'],
                        plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '40%' } },
                        legend: { show: false }, dataLabels: { enabled: true }
                    }).render();
                }
            }
        }

        // Renderizar gráficos cuando se abre un collapse
        document.querySelectorAll('.collapse').forEach(function (panel) {
            panel.addEventListener('shown.bs.collapse', function () {
                panel.querySelectorAll('.apex-chart-container').forEach(inicializarGrafico);
            });
            if (panel.classList.contains('show')) {
                panel.querySelectorAll('.apex-chart-container').forEach(inicializarGrafico);
            }
        });

        // Confirmaciones SweetAlert para cierre/reapertura de materias
        document.querySelectorAll('.accion-cierre-materia').forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                evento.preventDefault();
                const reabrir = formulario.dataset.reabrir === '1';
                Swal.fire({
                    title: (reabrir ? '¿Reabrir ' : '¿Cerrar ') + formulario.dataset.materia + '?',
                    text: reabrir
                        ? 'Se eliminarán los resultados finales para permitir un nuevo cálculo. Las notas y asistencias originales se conservan. Los pasos de crecimiento, tareas y promociones ya otorgados no se revierten automáticamente.'
                        : 'Se calcularán las aprobaciones con las notas y asistencias registradas. Las notas pendientes aportan cero. El proceso continuará en segundo plano.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: reabrir ? 'Sí, reabrir materia' : 'Sí, cerrar y calcular',
                    cancelButtonText: 'Cancelar', buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary me-3 rounded-pill', cancelButton: 'btn btn-outline-secondary rounded-pill' }
                }).then(function (resultado) {
                    if (resultado.isConfirmed) {
                        formulario.querySelector('button[type="submit"]').disabled = true;
                        formulario.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
