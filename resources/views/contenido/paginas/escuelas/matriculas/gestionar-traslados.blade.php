@section('isEscuelasModule', true)

@php
    $configData = Helper::appClasses();
@endphp


@extends('layouts/layoutMaster')

@section('title', 'Gestionar Traslados')


@section('vendor-style')
    <style>
        .color-picker-container {
            width: 100px;
            /* Ajusta este valor al tamaño que necesites */
        }

        .pickr .pcr-button {
            height: 38px !important;
            width: 40px !important;
            border: solid 1px #3e3e3e;
        }

        @media screen and (max-width:550px) {
            #container-left {
                display: none
            }

            ;
        }
    </style>


    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection


@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/js/app.js'])
@endsection


@section('page-script')


    <script>
        $(function() {
            // Scripts de inicialización y validación que ya tenías
            $('#selector-escuela').select2({
                placeholder: 'Selecciona una escuela'
            });

            Livewire.on('usuarioBuscadoSeleccionado', eventData => {
                const estudianteId = eventData.usuarioId;
                if (estudianteId) {
                    $('#hidden_estudiante_id').val(estudianteId);
                    $('#selector-escuela').prop('disabled', false).find('option:first').text(
                        '-- Seleccione una escuela --');
                    $('#buscar-btn').prop('disabled', false);
                }
            });

            $('#buscar-btn').on('click', function() {
                // ... (tu lógica de validación del formulario)
                $('#formBusqueda').submit();
            });
        });

        // Función para abrir el modal (se queda aquí)
        function abrirModalTraslado(matriculaId) {
            Livewire.dispatch('abrirModalTraslado', {
                matriculaId: matriculaId
            });
        }

        // --- INICIO: LISTENERS DE EVENTOS GLOBALES ---
        // Al estar en la vista principal, estos listeners siempre estarán activos.

        // Listener para la alerta de éxito
        Livewire.on('swal:success', data => {
            Swal.fire({
                title: data[0].title,
                text: data[0].text,
                icon: 'success',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        });

        // Listener para la alerta de error
        Livewire.on('swal:error', data => {
            Swal.fire({
                title: data[0].title,
                text: data[0].text,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        });

        // Listener para recargar la página
        Livewire.on('recargarPagina', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });
    </script>
@endsection

@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Gestión de traslados</h4>
    <p class="mb-4 text-black">Busque un estudiante y seleccione una escuela para ver sus matrículas activas y gestionar
        traslados de horario.</p>

    {{-- El formulario GET controla toda la búsqueda de matrículas --}}
    <form id="formBusqueda" action="{{ route('matriculas.gestionarTraslados', ['user' => $usuarioActivo->id]) }}"
        method="GET">


        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-black fw-semibold">
                            <i class="ti ti-user-search ms-n1 me-2"></i>1. Seleccione el estudiante
                        </h5>
                    </div>
                    <div class="card-body">
                        @livewire('usuarios.usuarios-para-busqueda', [
                            'id' => 'buscador-estudiante',
                            'tipoBuscador' => 'unico',
                            'conDadosDeBaja' => 'no',
                            'class' => 'col-12',
                            'placeholder' => 'Buscar por nombre o identificación...',
                            'queUsuariosCargar' => 'todos',
                            'usuarioSeleccionadoId' => $userId,
                            'obligatorio' => true,
                        ])
                    </div>
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-black fw-semibold">
                            <i class="ti ti-school ms-n1 me-2"></i>2. Seleccione la escuela
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end g-3">
                            <div class="col-md-9">
                                <label for="selector-escuela" class="form-label">Escuelas disponibles</label>
                                <select required id="selector-escuela" name="escuela_id" class="form-select">
                                    <option value="">--
                                        {{ $usuarioSeleccionado ? 'Seleccione una escuela' : 'Primero busque un estudiante' }}
                                        --</option>
                                    @foreach ($escuelas as $escuela)
                                        <option value="{{ $escuela->id }}" @selected($escuelaSeleccionada && $escuelaSeleccionada->id == $escuela->id)>
                                            {{ $escuela->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button id="buscar-btn" type="button" class="btn btn-outline-secondary rounded-pill w-100">
                                    <i class="ti ti-search me-1"></i> Buscar matrículas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- PASO 3: MOSTRAR MATRÍCULAS ACTIVAS ENCONTRADAS --}}
    <div class="col-12">
        <div class="card">

            @if ($usuarioSeleccionado && $escuelaSeleccionada)
                <div class="card-header">
                    <h5 class="text-black fw-semibold mb-3">
                        <i class="ti ti-check ms-n1 me-2"></i>3. Matrículas activas de {{ $usuarioSeleccionado->nombre(3) }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @forelse ($matriculasActivas as $matricula)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <img class="card-img-top"
                                        src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/materias/' . $matricula->horarioMateriaPeriodo->materiaPeriodo->materia->portada) }}"
                                        alt="Portada de la Materia" style="height: 100px; object-fit: cover;">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-semibold">
                                            {{ $matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre }}</h5>

                                        {{-- Detalles de la matrícula actual --}}
                                        <div class="row justify-content-between mb-2 mt-2">
                                            <div class="col-12 col-md-6 align-items-center">
                                                <i class="ti ti-calendar-month me-2"></i>
                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted">Periodo:</small>
                                                    <small
                                                        class="fw-semibold text-black">{{ $matricula->periodo->nombre ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 align-items-center">
                                                <i class="ti ti-building-community me-2"></i>
                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted">Sede</small>
                                                    <small
                                                        class="fw-semibold text-black">{{ $matricula->horarioMateriaPeriodo->horarioBase->aula->sede->nombre ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row justify-content-between mb-2">
                                            <div class="col-12 col-md-6 align-items-center">
                                                <i class="ti ti-door-enter me-2"></i>
                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted">Aula:</small>
                                                    <small
                                                        class="fw-semibold text-black">{{ $matricula->horarioMateriaPeriodo->horarioBase->aula->nombre ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 align-items-center">
                                                <i class="ti ti-clock me-2"></i>
                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted">Horario</small>
                                                    <small
                                                        class="fw-semibold text-black">{{ $matricula->horarioMateriaPeriodo->horarioBase->dia_semana ?? '' }},
                                                        {{ $matricula->horarioMateriaPeriodo->horarioBase->hora_inicio_formato ?? '' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($matricula->trasladosLog->isNotEmpty())
                                            <div class="mt-3">
                                                <small class="text-black fw-semibold">HISTORIAL DE TRASLADOS:</small>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach ($matricula->trasladosLog as $log)
                                                        <li class="my-3">
                                                            <small><i class="ti ti-arrow-right-circle ti-xs"></i>
                                                                Trasladado por: {{ $log->user->nombre(3) ?? 'N/A' }}
                                                                el {{ $log->created_at->format('d/m/Y') }}
                                                            </small>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- Botón de trasladar, que ahora va al final --}}
                                        <div class="mt-auto pt-2">
                                            <button type="button" class="btn btn-primary rounded-pill w-100"
                                                onclick="abrirModalTraslado({{ $matricula->id }})">
                                                <i class="ti ti-transfer-in me-1"></i> Trasladar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center" role="alert">
                                    <span>Este alumno no tiene matrículas activas en la escuela y periodo
                                        seleccionados.</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Incluimos el nuevo componente de modal para los traslados --}}
    @livewire('matricula.traslado-modal')

@endsection
