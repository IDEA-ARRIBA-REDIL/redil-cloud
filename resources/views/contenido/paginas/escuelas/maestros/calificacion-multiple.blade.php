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
            /* Ancho máximo para el input de nota */
        }

        .accordion-toggle-btn {
            font-size: 1.2rem;
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
        <div class="col-12 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Calificación multiple: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }}</small></p>
                </div>
            </div>
        </div>
    </div>

    @include('contenido.paginas.escuelas.maestros.nav-modulo')

    {{-- Contenido Principal: Acordeón de Alumnos --}}
    <div class="row">
        <div class="col-12">
            {{-- Renderizar el componente Livewire, pasándole el HorarioMateriaPeriodo --}}
            @livewire('Maestros.CalificacionMultipleAlumnos', ['horarioAsignado' => $horarioAsignado])
        </div>
    </div>
@endsection
