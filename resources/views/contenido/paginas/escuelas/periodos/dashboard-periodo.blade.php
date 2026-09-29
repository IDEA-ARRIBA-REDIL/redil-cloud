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
        const materias = {{ Illuminate\Support\Js::from($dashboard['materias']) }};
        const dibujadas = new Set();
        const dibujar = function (materia) {
            if (dibujadas.has(materia.id) || typeof ApexCharts === 'undefined') return;
            dibujadas.add(materia.id);
            const resumen = materia.resumen;
            if (resumen.estudiantes > 0) {
                new ApexCharts(document.getElementById('grafico-resultados-' + materia.id), {
                    chart: { type: 'donut', height: 270, toolbar: { show: false } },
                    series: [resumen.aprobados, resumen.riesgo, resumen.pendientes],
                    labels: [materia.cerrada ? 'Aprobados' : 'Cumplirían hoy', materia.cerrada ? 'Reprobados' : 'En riesgo hoy', 'Sin evaluar'],
                    colors: ['#28c76f', '#ea5455', '#ff9f43'],
                    legend: { position: 'bottom' },
                    plotOptions: { pie: { donut: { size: '62%' } } },
                    dataLabels: { enabled: true }
                }).render();
                new ApexCharts(document.getElementById('grafico-generos-' + materia.id), {
                    chart: { type: 'bar', height: 270, toolbar: { show: false } },
                    series: [{ name: 'Estudiantes', data: Object.values(resumen.generos) }],
                    xaxis: { categories: Object.keys(resumen.generos) },
                    yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0 },
                    colors: ['#7367f0', '#00cfe8', '#82868b'],
                    plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '50%' } },
                    legend: { show: false }, dataLabels: { enabled: true }
                }).render();
            }
            if (resumen.registrosAsistencia > 0) {
                new ApexCharts(document.getElementById('grafico-asistencias-' + materia.id), {
                    chart: { type: 'bar', height: 270, toolbar: { show: false } },
                    series: [{ name: 'Registros', data: [resumen.presentes, resumen.ausentes] }],
                    xaxis: { categories: ['Asistencias', 'Inasistencias'] },
                    yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0 },
                    colors: ['#28c76f', '#ea5455'],
                    plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '45%' } },
                    legend: { show: false }, dataLabels: { enabled: true }
                }).render();
            }
        };
        materias.forEach(function (materia) {
            const panel = document.getElementById('detalle-materia-' + materia.id);
            if (!panel) return;
            panel.addEventListener('shown.bs.collapse', function () { dibujar(materia); });
            if (panel.classList.contains('show')) dibujar(materia);
        });
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
