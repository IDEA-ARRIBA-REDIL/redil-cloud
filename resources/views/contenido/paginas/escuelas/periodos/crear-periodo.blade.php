@php
    use Carbon\Carbon;
@endphp
@extends('layouts/blankLayout')


@section('title', 'Crear Periodo')

@section('vendor-style')
    {{-- Asegúrate de que los @vite estén correctos para tu versión y configuración --}}
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', // Si usas bootstrap-select además de select2
    ])
    <style>
        /* Estilo para ocultar pasos */
        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        /* Estilo para filas de corte */
        .corte-row {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
            /* Usa variable BS para tema oscuro/claro */
        }

        .corte-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Ajuste para labels en pantallas pequeñas */
        @media (max-width: 767.98px) {
            .corte-row .form-label {
                margin-bottom: 0.25rem;
                /* Menos espacio debajo del label */
            }

            .corte-row>div[class*="col-"] {
                margin-bottom: 0.75rem;
                /* Espacio entre inputs en móvil */
            }

            .corte-row>div[class*="col-"]:last-child {
                margin-bottom: 0;
            }
        }
    </style>
@endsection

@section('vendor-script')
    @vite([
        'resources/js/app.js', // Generalmente se carga en layoutMaster
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/flatpickr/flatpickr.js',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', // Si usas bootstrap-select además de select2
    ])
@endsection

@section('page-script')
    {{-- Script para inicializar Flatpickr y Select2 --}}
    <script type="module">
        $(document).ready(function() {

            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno'
            });

            $(".fecha-picker").flatpickr({
                dateFormat: "Y-m-d",
                disableMobile: true
            });
        });
    </script>

    {{-- ** INICIO: Script para la navegación por pasos y carga dinámica de cortes ** --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const steps = document.querySelectorAll('.step');
            const progressBar = document.getElementById('progress-bar');
            const stepCounter = document.getElementById('step-counter');
            const stepTitle = document.getElementById('step-title');
            const nextButton = document.querySelector('.next-step');
            const prevButton = document.querySelector('.prev-step');
            const form = document.getElementById('formularioCrearPeriodo');
            const escuelaSelect = document.getElementById('escuelaId');
            const cortesContainer = document.getElementById('cortes-container');

            // Almacenar los datos de cortes pasados desde el controlador
            // IMPORTANTE: $cortesPorEscuela debe ser pasado desde PeriodoController@crear
            const cortesData = @json($cortesPorEscuela ?? []);

            let currentStep = 1;
            const totalSteps = steps.length;

            function updateStepView() {
                steps.forEach((step, index) => {
                    step.classList.toggle('active', (index + 1) === currentStep);
                });

                const currentStepElement = document.getElementById(`step-${currentStep}`);
                if (!currentStepElement) {
                    return;
                }

                const progress = totalSteps > 0 ? (currentStep / totalSteps) * 100 : 0;
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                }

                if (stepCounter && stepTitle) {
                    stepCounter.textContent = `Paso ${currentStep} de ${totalSteps}`;
                    stepTitle.textContent = currentStepElement.dataset.title || `Paso ${currentStep}`;
                }

                // --- Lógica de visibilidad de botones (con jQuery y clases) ---
                // Asegúrate de que jQuery esté cargado antes de este script
                if (typeof $ !== 'undefined') {
                    // Botón Volver (#btnVolver)
                    $('#btnVolver').toggleClass('d-none', currentStep === 1);

                    // Botón Continuar (#btnContinuar)
                    $('#btnContinuar').toggleClass('d-none', currentStep === totalSteps);

                    // Botón Guardar (#btnGuardar)
                    $('#btnGuardar').toggleClass('d-none', currentStep !== totalSteps);
                } else {
                    // Fallback a la lógica original si jQuery no está disponible (opcional)
                    console.warn("jQuery no está definido, usando lógica de visibilidad con style.display.");
                    if (btnVolver) {
                        btnVolver.classList.toggle('d-none', currentStep === 1);
                    }
                    if (btnContinuar) {
                        btnContinuar.style.display = (currentStep < totalSteps) ? 'inline-block' : 'none';
                    }
                    if (btnGuardar) {
                        btnGuardar.style.display = (currentStep === totalSteps) ? 'inline-block' : 'none';
                    }
                }
                // --- Fin Lógica de visibilidad ---
            }

            // Función para generar el HTML de los cortes en el Paso 2
            function cargarCortesParaEscuela(escuelaId) {
                if (!cortesContainer) {
                    console.error("Contenedor de cortes no encontrado.");
                    return false; // Indicar fallo
                }
                cortesContainer.innerHTML = ''; // Limpiar contenedor

                const cortes = cortesData[escuelaId] || [];

                if (cortes.length === 0) {
                    cortesContainer.innerHTML =
                        '<div class="alert alert-warning">La escuela seleccionada no tiene cortes definidos. Por favor, configúralos primero en la gestión de escuelas o selecciona otra escuela.</div>';
                    return false; // Indicar que no se cargaron cortes válidos
                }

                // Generar HTML para cada corte
                cortes.forEach((corte, index) => {
                    // Usar el porcentaje de CorteEscuela como valor por defecto
                    const porcentajeDefault = corte.porcentaje || '';
                    const corteHtml = `
                        <div class="row corte-row align-items-center">
                            <input type="hidden" name="cortes[${index}][corte_escuela_id]" value="${corte.id}">
                            <div class="col-12 col-md-3 mb-2 mb-md-0">
                                <label class="form-label small text-muted d-block">Corte:</label>
                                <span class="fw-semibold">${corte.nombre || 'N/A'}</span>
                                <small class="d-block text-muted">(Orden: ${corte.orden})</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="cortes_${index}_fecha_inicio" class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="text" name="cortes[${index}][fecha_inicio]" id="cortes_${index}_fecha_inicio"
                                       class="form-control fecha-picker-corte" placeholder="YYYY-MM-DD" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="cortes_${index}_fecha_fin" class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                                <input type="text" name="cortes[${index}][fecha_fin]" id="cortes_${index}_fecha_fin"
                                       class="form-control fecha-picker-corte" placeholder="YYYY-MM-DD" required>
                            </div>
                            <div class="col-12 col-md-3 mt-2 mt-md-0">
                                <label for="cortes_${index}_porcentaje" class="form-label">Porcentaje (%) <span class="text-danger">*</span></label>
                                <input type="number" name="cortes[${index}][porcentaje]" id="cortes_${index}_porcentaje"
                                       class="form-control" min="0" max="100" step="0.01" value="${porcentajeDefault}" required>
                            </div>
                        </div>
                    `;
                    cortesContainer.insertAdjacentHTML('beforeend', corteHtml);
                });

                // Re-inicializar Flatpickr para los nuevos campos de fecha
                // Usamos un selector específico para no re-inicializar los del paso 1
                $(".fecha-picker-corte").flatpickr({
                    dateFormat: "Y-m-d",
                    disableMobile: true
                });
                return true; // Indicar éxito
            }

            // Evento para el botón "Continuar"
            if (nextButton) {
                nextButton.addEventListener('click', function() {
                    if (nextButton.type === 'button' && currentStep < totalSteps) {

                        // --- Validación del Paso 1 (Ejemplo Básico) ---
                        // --- INICIO: Validación del Paso Actual (Paso 1 en este caso) ---
                        let paso1Valido = true; // Asumir que el paso es válido inicialmente
                        // Seleccionar solo el div del paso actual que está activo
                        const currentStepElement = document.querySelector('.step.active');

                        if (currentStepElement && currentStep === 1) { // Validar específicamente el paso 1
                            console.log("Validando Paso 1..."); // Log para depuración
                            // Seleccionar TODOS los inputs y selects requeridos DENTRO del paso actual
                            const inputsPasoActual = currentStepElement.querySelectorAll(
                                'input[required], select[required]');

                            inputsPasoActual.forEach(input => {
                                // Limpiar errores previos
                                input.classList.remove('is-invalid'); // QUITA LA CLASE SI ESTABA
                                // Encontrar el contenedor padre más cercano para buscar el feedback
                                const formGroup = input.closest(
                                    '.mb-3, .col-12'); // Ajustar selectores si es necesario
                                const feedback = formGroup ? formGroup.querySelector(
                                    '.invalid-feedback') : null;
                                // Ocultar el feedback de Blade (si se mostró por error previo del backend)
                                if (feedback) feedback.style.display = 'none';

                                let valorInvalido = false;
                                // Validación específica para select múltiple (Select2)
                                if (input.multiple) {
                                    if ($(input).val() === null || $(input).val().length === 0) {
                                        valorInvalido = true;
                                    }
                                }
                                // Validación para otros inputs y selects
                                else if (!input.value.trim()) {
                                    valorInvalido = true;
                                }

                                // Si el valor es inválido
                                if (valorInvalido) {
                                    pasoValido = false; // Marcar el paso como inválido
                                    // AÑADE LA CLASE PARA EL BORDE ROJO
                                    // NO intentamos mostrar el feedback de Blade aquí, solo añadimos la clase.
                                    console.warn(
                                        `Campo requerido vacío o inválido: ${input.name}`
                                    ); // Log para depuración
                                }
                            });
                        }
                        // --- FIN: Validación del Paso Actual ---

                        // Si el paso NO es válido, mostrar mensaje y detener
                        if (!pasoValido) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Campos incompletos',
                                text: 'Por favor, completa todos los campos obligatorios (*) resaltados en rojo.', // Mensaje actualizado
                                customClass: {
                                    confirmButton: 'btn btn-warning'
                                }
                            });
                            return; // Detiene la ejecución, no avanza al siguiente paso
                        }
                        // --- Fin Validación Paso 1 ---

                        // Cargar cortes si estamos pasando al paso 2
                        if (currentStep === 1) {
                            const selectedEscuelaId = escuelaSelect ? escuelaSelect.value : null;
                            if (selectedEscuelaId) {
                                const cortesCargados = cargarCortesParaEscuela(selectedEscuelaId);
                                if (!cortesCargados) {
                                    // Si no se cargaron cortes (ej: escuela sin cortes definidos), no avanzar
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'La escuela seleccionada no tiene cortes definidos. Configúralos primero.'
                                    });
                                    return;
                                }
                            } else {
                                // Si no se seleccionó escuela, no avanzar
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Acción requerida',
                                    text: 'Por favor, selecciona una escuela en el Paso 1.'
                                });
                                return;
                            }
                        }

                        // Avanzar al siguiente paso
                        currentStep++;
                        updateStepView();
                    }
                    // Si es el último paso (type="submit"), el formulario se enviará
                });
            }

            // Evento para el botón "Volver"
            if (prevButton) {
                prevButton.addEventListener('click', function() {
                    if (currentStep > 1) {
                        currentStep--;
                        updateStepView();
                    }
                });
            }

            // Inicializar la vista
            if (steps.length > 0) {
                updateStepView();
            } else {
                console.warn("No se encontraron elementos 'step'.");
            }
        });
    </script>
@endsection

@section('content')



    {{-- Mensaje de error general --}}
    @if ($errors->has('error_general'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first('error_general') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
        <div class="col-3 text-start">
            <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
                <span class="ti-xs ti ti-arrow-left me-2"></span>
                <span class="d-none d-md-block fw-normal">Volver</span>
            </button>
        </div>
        <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Nuevo Periodo
            </h5>
        </div>
        <div class="col-3 text-end">
            <a href="{{ route('periodo.gestionar') }}" type="button"
                class="btn rounded-pill waves-effect waves-light text-white">
                <span class="d-none d-md-block fw-normal">Salir</span>
                <span class="ti-xs ti ti-x mx-2"></span>
            </a>
        </div>
    </nav>
    <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">
        <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
            @livewire('Escuelas.NuevoPeriodo')
        </div>
    </div>
@endsection
