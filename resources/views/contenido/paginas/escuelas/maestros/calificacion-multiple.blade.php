@php
    use App\Models\Sede;
    use App\Models\User;
@endphp

@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Calificaciones Multiples')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
    <style>
        .accordion-button:not(.collapsed) {
            color: var(--bs-primary);
            background-color: var(--bs-primary-light);
            /* Un color más suave para el acordeón activo */
        }

        .accordion-button:not(.collapsed)::after {
            background-image: var(--bs-accordion-btn-active-icon);
            transform: var(--bs-accordion-btn-icon-transform);
        }

        .card-item-calificacion .card-body {
            padding: 0.8rem;
        }

        .card-item-calificacion .form-control-sm {
            text-align: center;
            max-width: 80px;
            /* Ancho para input de nota */
            margin: 0 auto 0.5rem auto;
            /* Centrar input y margen inferior */
        }



        .item-calificacion-actions a,
        .item-calificacion-actions button {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
@endsection

@section('page-script')
    <script>
        // Inicializar tooltips de Bootstrap si los usas en el componente Livewire o aquí
        document.addEventListener('livewire:navigated', () => { // Para Livewire 3 con navegación SPA
            // O 'DOMContentLoaded' si no usas navegación SPA de Livewire intensamente
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener para los botones "Calificar" individuales
            document.querySelectorAll('.btn-calificar-item').forEach(button => {
                button.addEventListener('click', function(event) {
                    event
                        .preventDefault(); // Prevenir envío de formulario si es parte de uno más grande
                    const itemId = this.dataset.itemId;
                    const alumnoId = this.dataset
                        .alumnoId; // Necesitarás añadir data-alumno-id al botón o al form
                    const notaInputId = `nota_alumno_${alumnoId}_item_${itemId}`;
                    const notaValor = document.getElementById(notaInputId) ? document
                        .getElementById(notaInputId).value : null;

                    if (notaValor === null || notaValor.trim() === '') {
                        Swal.fire('Atención', 'Por favor, ingresa una nota.', 'warning');
                        return;
                    }

                    console.log(
                        `Calificar Alumno ID: ${alumnoId}, Item ID: ${itemId}, Nota: ${notaValor}`
                    );
                    // Aquí iría la lógica AJAX para guardar la nota
                    // Ejemplo:
                    // fetch('/ruta/para/guardar/nota/item', {
                    //     method: 'POST',
                    //     headers: {
                    //         'Content-Type': 'application/json',
                    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    //     },
                    //     body: JSON.stringify({
                    //         alumno_id: alumnoId,
                    //         item_id: itemId,
                    //         nota: notaValor,
                    //         horario_asignado_id: '{{ $horarioAsignado->id }}' // Pasar el ID del horario
                    //     })
                    // })
                    // .then(response => response.json())
                    // .then(data => {
                    //     if(data.success) {
                    //         Swal.fire('¡Guardado!', data.message || 'La nota ha sido guardada.', 'success');
                    //     } else {
                    //         Swal.fire('Error', data.message || 'No se pudo guardar la nota.', 'error');
                    //     }
                    // })
                    // .catch(error => {
                    //     console.error('Error:', error);
                    //     Swal.fire('Error', 'Ocurrió un problema de conexión.', 'error');
                    // });
                    Swal.fire({
                        icon: 'success',
                        title: 'Nota',
                        text: `Nota ${notaValor} para Item ID ${itemId} del Alumno ID ${alumnoId} sería guardada.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });

            // Validar inputs de nota
            document.querySelectorAll('.input-nota-item').forEach(input => {
                input.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                    let val = parseFloat(this.value);
                    if (val > 5.0) this.value = "5.0"; // Asumiendo nota máxima 5.0
                    if (val < 0.0) this.value = "0.0"; // Asumiendo nota mínima 0.0
                });
            });
        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado de la clasificacion detallada --}}
    <div class="row mb-3">
        <div class="col-12 mb-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Calificación multiple: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }}</small></p>
                </div>

            </div>
        </div>


        {{-- Barra de Navegación del Módulo --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card mb-0 p-0 border-0 shadow-sm">
                    <ul class="nav nav-pills nav-fill justify-content-start flex-column flex-md-row gap-1 px-2 py-1">
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_dashboard_general'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.dashboardClase', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light  "> {{-- Marcado como activo --}}
                                <i class="mdi mdi-view-dashboard-outline me-1"></i> Dashboard general
                            </a>
                        </li>
                        @endif

                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.calificacionMultiple', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light active">
                                <i class="mdi mdi-table-edit me-1"></i> Calificación detallada
                            </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.calificacionGrilla', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light">
                                <i class="mdi mdi-grid me-1"></i> Calificación Grilla
                            </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                                class="nav-link module-nav-link p-3 waves-effect waves-light">
                                <i class="mdi mdi-calendar-check-outline me-1"></i> Reportes de asistencia
                            </a>
                        </li>
                        @endif
                        @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_recursos_alumnos'))
                        <li class="nav-item">
                            <a href="{{ route('maestros.recursosAlumnos', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}" class="nav-link module-nav-link p-3 waves-effect waves-light ">
                                <i class="mdi mdi-folder-multiple-outline me-1"></i> Recursos alumnos
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Contenido Principal: Acordeón de Alumnos --}}
        <div class="row">
            <div class="col-12">


                {{-- Renderizar el componente Livewire, pasándole el HorarioMateriaPeriodo --}}
                @livewire('Maestros.CalificacionMultipleAlumnos', ['horarioAsignado' => $horarioAsignado])


            </div>
        </div>
    @endsection
