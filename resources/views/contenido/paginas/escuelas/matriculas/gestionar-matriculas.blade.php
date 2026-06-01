@php
    $configData = Helper::appClasses();
@endphp

@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Matrícula')

@section('vendor-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/js/app.js'])
@endsection


@section('page-script')
    <script>
        $(function() {
            // Inicializa el selector de escuelas.
            $('#selector-escuela').select2({
                placeholder: 'Selecciona una escuela',
            });

            // --- NOTIFICACIONES Y EVENTOS ---

            // Alerta de éxito global
            Livewire.on('swal:success', data => {
                Swal.fire({
                    title: data.title || 'Operación Exitosa',
                    text: data.text,
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });

            // Alerta de advertencia/validación
            Livewire.on('swal:warning', data => {
                Swal.fire({
                    title: data.title,
                    text: data.text,
                    icon: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });

            // Listener para recargar la página tras operaciones exitosas
            Livewire.on('recargarPagina', () => {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            });

            // Captura del ID del estudiante desde el buscador Livewire
            Livewire.on('usuarioBuscadoSeleccionado', eventData => {
                const estudianteId = eventData.usuarioId;
                if (estudianteId) {
                    $('#selector-escuela').prop('disabled', false).find('option:first').text(
                        '-- Seleccione una escuela --');
                    $('#buscarMaterias').prop('disabled', false);
                    $('#hidden_estudiante_id').val(estudianteId);
                }
            });

            // Envío manual del formulario de búsqueda
            $('#buscarMaterias').on('click', function() {
                const estudianteId = $('#buscador-estudiante').val();
                const escuelaId = $('#selector-escuela').val();

                if (!estudianteId) {
                    Swal.fire({
                        title: '¡Falta el Estudiante!',
                        text: 'Por favor, seleccione un estudiante.',
                        icon: 'warning',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    return;
                }
                if (!escuelaId) {
                    Swal.fire({
                        title: '¡Falta la Escuela!',
                        text: 'Por favor, seleccione una escuela.',
                        icon: 'warning',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    return;
                }
                $('#formBusquedaMaterias').submit();
            });
        });

        // --- FUNCIONES DE APERTURA DE MODALES ---

        /**
         * BIFURCACIÓN HACIA MATERIAS: Abre el modal estándar para inscripciones individuales.
         */
        function abrirModalMatricula(materiaId, usuarioId, escuelaId) {
            Livewire.dispatch('abrirModalMatricula', {
                materiaId: materiaId,
                usuarioId: usuarioId,
                escuelaId: escuelaId
            });
        }

        /**
         * BIFURCACIÓN HACIA NIVELES: Abre el nuevo modal para inscripciones grupales por nivel.
         */
        function abrirModalMatriculaNivel(nivelId, estudianteId) {
            Livewire.dispatch('abrirModalMatriculaNivel', {
                nivelId: nivelId,
                estudianteId: estudianteId
            });
        }

        function confirmarEliminacion(url) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
@endsection

@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Gestión de matrículas</h4>
    <p class="mb-4 text-black">Busca un estudiante y selecciona una escuela para gestionar su matrícula.</p>

    <form id="formBusquedaMaterias" action="{{ route('matriculas.gestionar', ['user' => $usuarioActivo->id]) }}"
        method="GET">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <h5 class="text-black fw-semibold mb-0">
                            <i class="ti ti-user-search ms-n1 me-2 text-primary"></i>1. Seleccione el estudiante
                        </h5>
                    </div>
                    <div class="card-body pt-4">
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
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <h5 class="text-black fw-semibold mb-0">
                            <i class="ti ti-school ms-n1 me-2 text-primary"></i>2. Seleccione la escuela
                        </h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row align-items-end g-3">
                            <div class="col-md-9">
                                <label for="selector-escuela" class="form-label fw-bold">Escuelas disponibles</label>
                                <select required id="selector-escuela" name="escuela_id" class="form-select">
                                    <option value="">-- Selecciona una escuela --</option>
                                    @foreach ($escuelas as $escuela)
                                        <option value="{{ $escuela->id }}" @selected($escuelaSeleccionada && $escuelaSeleccionada->id == $escuela->id)>
                                            {{ $escuela->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button id="buscarMaterias" type="button" class="btn btn-primary rounded-pill w-100">
                                    <i class="ti ti-search me-1"></i> Consultar Disponibilidad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- PASO 3: LISTADO DE DISPONIBILIDAD (MATERIAS O NIVELES) --}}
    @if ($usuarioSeleccionado && $escuelaSeleccionada)
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-label-primary border-bottom py-3">
                    <h5 class="text-white fw-bold mb-0">
                        <i class="ti ti-books me-2"></i>
                        @if ($escuelaSeleccionada->tipo_matricula === 'niveles_agrupados')
                            Grados Disponibles: {{ $usuarioSeleccionado->nombre(2) }}
                        @else
                            Materias Disponibles: {{ $usuarioSeleccionado->nombre(2) }}
                        @endif
                    </h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-4">
                        {{-- ITERACIÓN DINÁMICA: El controlador inyecta el reporte de Niveles o Materias --}}
                        @forelse ($reporteItems as $row)
                            @php
                                $item = $row->item;
                                $esNivel = $row->tipo === 'NIVEL';
                                $portadaUrl = $item->portada_url ?? asset('storage/global/img/escuelas/default.png');
                            @endphp

                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border transition-all hover-shadow-md">
                                    <div class="position-relative">
                                        {{-- Imagen de portada (Nivel o Materia) --}}
                                        <img class="card-img-top"
                                            src="{{ $portadaUrl }}"
                                            alt="Portada"
                                            style="height: 120px; object-fit: cover; {{ $row->estado == 'BLOQUEADA' ? 'filter: grayscale(1); opacity: 0.5;' : '' }}">

                                        @if ($row->estado == 'BLOQUEADA')
                                            <div
                                                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                                <span class="badge bg-danger rounded-pill text-black"><i
                                                        class="ti ti-lock me-1"></i>
                                                    Requisitos</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title fw-bold text-gray-800 mb-0">{{ $item->nombre }}</h5>
                                            <span class="badge bg-label-secondary text-uppercase"
                                                style="font-size: 0.65rem">
                                                {{ $esNivel ? 'Grado' : 'Materia' }}
                                            </span>
                                        </div>

                                        {{-- Visualización de Estados --}}
                                        @if (in_array($row->estado, ['APROBADA', 'APROBADO']))
                                            <span class="badge bg-label-success rounded-pill w-px-150 mb-3"><i
                                                    class="ti ti-check me-1"></i> Aprobado</span>
                                        @elseif($row->estado == 'BLOQUEADA')
                                            <div
                                                class="bg-label-warning p-2 rounded mb-3 border border-warning border-opacity-25">
                                                <small class="fw-bold d-block mb-1 text-warning-700">Falta
                                                    completar:</small>
                                                <ul class="ps-3 mb-0 list-unstyled">
                                                    @foreach ($row->motivos as $motivo)
                                                        <li class="mb-1"><i
                                                                class="ti ti-circle-x-filled me-1 text-danger"></i> <small
                                                                class="text-black">{{ $motivo }}</small></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- BIFURCACIÓN DE ACCIONES DE MATRÍCULA --}}
                                        <div class="mt-auto">
                                            @php
                                                // Lógica para detectar si ya está matriculado en este item
                                                if ($esNivel) {
                                                    $estaMatriculado = $item
                                                        ->matriculas()
                                                        ->where('usuario_id', $usuarioSeleccionado->id)
                                                        ->where(
                                                            'periodo_id',
                                                            $reporteItems
                                                                ->first()
                                                                ->item->periodos()
                                                                ->where('estado', true)
                                                                ->first()->id ?? 0,
                                                        )
                                                        ->exists();
                                                } else {
                                                    $estaMatriculado = $matriculasDelAlumno
                                                        ->where(
                                                            'horarioMateriaPeriodo.materiaPeriodo.materia_id',
                                                            $item->id,
                                                        )
                                                        ->isNotEmpty();
                                                }
                                            @endphp

                                            @if ($estaMatriculado)
                                                <div class="text-center py-2 bg-label-info rounded">
                                                    <span class="fw-bold"><i class="ti ti-circle-check me-1"></i>
                                                        Matriculado</span>
                                                </div>
                                            @elseif ($row->estado == 'DISPONIBLE')
                                                @if ($esNivel)
                                                    {{-- BOTÓN ACCIÓN NIVELES --}}
                                                    <button type="button" class="btn btn-primary w-100"
                                                        onclick="abrirModalMatriculaNivel({{ $item->id }}, {{ $usuarioSeleccionado->id }})">
                                                        <i class="ti ti-layout-grid-add me-1"></i> Inscribir Nivel
                                                    </button>
                                                @else
                                                    {{-- BOTÓN ACCIÓN MATERIAS --}}
                                                    <button type="button" class="btn btn-primary w-100"
                                                        onclick="abrirModalMatricula({{ $item->id }}, {{ $usuarioSeleccionado->id }}, {{ $escuelaSeleccionada->id }})">
                                                        <i class="ti ti-plus me-1"></i> Inscribir Materia
                                                    </button>
                                                @endif
                                            @else
                                                <button disabled class="btn btn-outline-secondary w-100">
                                                    <i class="ti ti-lock me-1"></i> No Disponible
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <img src="{{ asset('assets/img/illustrations/boy-working-light.png') }}" alt="No data"
                                    width="150" class="mb-3">
                                <p class="text-muted">No se encontraron ítems disponibles para matricular en esta selección.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODALES DE MATRÍCULA --}}
    @livewire('matricula.matricula-modal')
    @livewire('matricula.matricula-nivel-modal')

@endsection
