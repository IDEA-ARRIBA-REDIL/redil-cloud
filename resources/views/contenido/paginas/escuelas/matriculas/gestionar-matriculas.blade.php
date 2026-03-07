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

            // --- CÓDIGO NUEVO AÑADIDO ---

            // Listener para la alerta de éxito con SweetAlert2.
            Livewire.on('swal:success', data => {
                Swal.fire({
                    title: 'Matriculado con exito',
                    text: data.text,
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });

            // Listener para recargar la página.
            Livewire.on('recargarPagina', () => {
                // Retraso de 1.5 segundos para ver la alerta.
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            });

            // --- PASO A: CAPTURAR EL ID DEL ESTUDIANTE SELECCIONADO ---
            // Este listener escucha el evento de tu componente de búsqueda Livewire.
            // Su única misión es tomar el ID del estudiante y ponerlo en nuestro campo oculto.
            Livewire.on('usuarioBuscadoSeleccionado', eventData => {
                const estudianteId = eventData
                    .usuarioId; // Ajusta 'usuarioId' si la propiedad tiene otro nombre.

                if (estudianteId) {
                    // Habilitamos los controles del siguiente paso.
                    $('#selector-escuela').prop('disabled', false).find('option:first').text(
                        '-- Seleccione una escuela --');
                    $('#buscarMaterias').prop('disabled', false);

                    // IMPORTANTE: Ponemos el ID en el campo oculto dedicado.
                    $('#hidden_estudiante_id').val(estudianteId);
                }
            });

            // --- PASO B: VALIDAR Y ENVIAR AL HACER CLIC EN EL BOTÓN ---
            $('#buscarMaterias').on('click', function() {
                // 1. Obtenemos los valores actuales de los campos.
                const estudianteId = $('#buscador-estudiante').val();
                const escuelaId = $('#selector-escuela').val();

                // 2. Validamos que se haya seleccionado un estudiante.
                if (!estudianteId) {
                    Swal.fire({
                        title: '¡Falta el Estudiante!',
                        text: 'Por favor, busque y seleccione un estudiante de la lista.',
                        icon: 'warning',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    return; // Detenemos la ejecución.
                }

                // 3. Validamos que se haya seleccionado una escuela.
                if (!escuelaId) {
                    Swal.fire({
                        title: '¡Falta la Escuela!',
                        text: 'Por favor, seleccione una escuela para continuar.',
                        icon: 'warning',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    return; // Detenemos la ejecución.
                }

                // 4. Si todas las validaciones pasan, enviamos el formulario manualmente.
                $('#formBusquedaMaterias').submit();
            });
        });

        // Función para el modal de matrícula (sin cambios).
        function abrirModalMatricula(materiaId, usuarioId, escuelaId) {
            Livewire.dispatch('abrirModalMatricula', {
                materiaId: materiaId,
                usuarioId: usuarioId,
                escuelaId: escuelaId
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
                    // Si el usuario confirma, redirigimos a la URL de eliminación.
                    window.location.href = url;
                }
            });
        }

        // Listener para los mensajes de sesión (éxito o error)
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: '{{ session('error') }}',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        @endif
    </script>
@endsection

@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Gestión de matrículas</h4>
    <p class="mb-4 text-black">Busca un estudiante y selecciona una escuela para gestionar su matrícula.</p>
    {{-- Usamos un formulario GET para controlar la búsqueda final de materias. --}}
    <form id="formBusquedaMaterias" action="{{ route('matriculas.gestionar', ['user' => $usuarioActivo->id]) }}"
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
                        {{-- La llamada al componente Livewire no necesita cambios. --}}
                        {{-- Su única responsabilidad ahora es encontrar un usuario y guardar su ID internamente. --}}
                        @livewire('usuarios.usuarios-para-busqueda', [
                            'id' => 'buscador-estudiante', // Usamos este ID para el click en nuestro script.
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
                                {{-- El selector se deshabilita si aún no se ha interactuado con el buscador. --}}
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
                                {{-- El botón está deshabilitado hasta que se haya seleccionado un usuario --}}
                                <button id="buscarMaterias" type="button"
                                    class="btn btn-outline-secondary rounded-pill w-100">
                                    <i class="ti ti-search me-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>

    {{-- PASO 3: PLAN DE ESTUDIOS Y MATRÍCULAS --}}
    @if ($usuarioSeleccionado && $escuelaSeleccionada)
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-black fw-semibold mb-3">
                        <i class="ti ti-books ms-n1 me-2"></i>3. Eliga donde desea
                        matricular: {{ $usuarioSeleccionado->nombre(3) }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row equal-height-row g-4">
                        {{-- CAMBIO: El bucle principal ahora recorre el reporte detallado. --}}
                        @forelse ($reporteMaterias as $item)
                            @php
                                $materia = $item->materia;
                            @endphp
                            <div class="col equal-height-col  col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="position-relative">
                                        <img class="card-img-top"
                                            src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/materias/' . $materia->portada) }}"
                                            alt="Portada de {{ $materia->nombre }}"
                                            style="height: 100px; object-fit: cover; {{ $item->estado == 'BLOQUEADA' ? 'filter: grayscale(100%); opacity: 0.6;' : '' }}">

                                        @if ($item->estado == 'BLOQUEADA')
                                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                                <span class="badge bg-danger rounded-pill text-white"><i class="ti ti-lock me-1"></i>Bloqueada</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-1">{{ $materia->nombre }}</h5>

                                        @if ($item->estado == 'APROBADA')
                                            <span class="badge bg-label-success rounded-pill mb-3 w-px-100"> Aprobada</span>
                                        @elseif($item->estado == 'BLOQUEADA')
                                            <div class="alert alert-warning p-2 mt-2 mb-3">
                                                <small class="fw-semibold d-block mb-1"><i class="ti ti-alert-triangle me-1"></i>Requisitos pendientes:</small>
                                                <ul class="ps-3 mb-0">
                                                    @foreach ($item->motivos as $motivo)
                                                        <li><small>{{ $motivo }}</small></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @php
                                            // Buscamos si hay matrículas activas para esta materia específica.
                                            $matriculasExistentes = $matriculasDelAlumno->where(
                                                'horarioMateriaPeriodo.materiaPeriodo.materia_id',
                                                $materia->id,
                                            );
                                        @endphp

                                        @if ($matriculasExistentes->isNotEmpty())
                                            {{-- MOSTRAR DETALLES DE MATRÍCULA ACTIVA --}}
                                            <div class="mt-auto">
                                                @foreach ($matriculasExistentes as $matricula)
                                                    <span class="badge bg-label-success rounded-pill"> Matriculado</span>
                                                    <div class="row justify-content-between mb-2 mt-2">
                                                        <div class="col-12 col-md-6 align-items-center">
                                                            <i class="ti ti-calendar-month me-2"></i>
                                                            <div class="d-flex flex-column text-star">
                                                                <small class="text-muted">Periodo:</small>
                                                                <small class="fw-semibold text-black">
                                                                    {{ $matricula->periodo->nombre ?? 'N/A' }} </small>
                                                            </div>
                                                        </div>
                                                        <div class=" col-12 col-md-6 align-items-center">
                                                            <i class="ti ti-building-plus me-2"></i>
                                                            <div class="d-flex flex-column text-star">
                                                                <small class="text-muted">Sede</small>
                                                                <small class="fw-semibold text-black">
                                                                    {{ $matricula->horarioMateriaPeriodo->horarioBase->aula->sede->nombre ?? 'N/A' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-between mb-2">
                                                        <div class="col-12 col-md-6 align-items-center">
                                                            <i class="ti ti-building-skyscraper me-2"></i>
                                                            <div class="d-flex flex-column text-star">
                                                                <small class="text-muted">Aula:</small>
                                                                <small class="fw-semibold text-black">
                                                                    {{ $matricula->horarioMateriaPeriodo->horarioBase->aula->nombre ?? 'N/A' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6 align-items-center">
                                                            <i class="ti ti-clock me-2"></i>
                                                            <div class="d-flex flex-column text-star">
                                                                <small class="text-muted">Horario</small>
                                                                <small class="fw-semibold text-black">
                                                                    {{ $matricula->horarioMateriaPeriodo->horarioBase->dia_semana ?? '' }},
                                                                    {{ $matricula->horarioMateriaPeriodo->horarioBase->hora_inicio_formato ?? '' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p style="text-align: justify;"
                                                        class="card-text text-black mt-3 flex-grow-1">
                                                        <small class="text-muted ">Descripción:</small><br>
                                                        {!! $matricula->observacion !!}
                                                    </p>
                                                    @if( $rolActivo->hasPermissionTo('escuelas.opcion_eliminar_matricula'))
                                                    <button type="button"
                                                        class="btn btn-outline-danger rounded-pill w-100 mt-auto"
                                                        onclick="confirmarEliminacion('{{ route('matriculas.eliminarMatricula', ['matricula' => $matricula->id, 'user' => $usuarioActivo->id]) }}')">
                                                        <i class="ti ti-trash me-1"></i> Eliminar matrícula
                                                    </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            {{-- MOSTRAR OPCIÓN DE MATRÍCULA SEGÚN ESTADO --}}
                                            @if ($item->estado == 'DISPONIBLE')
                                                <button type="button" class="btn btn-primary rounded-pill w-100 mt-auto"
                                                    onclick="abrirModalMatricula({{ $materia->id }}, {{ $usuarioSeleccionado->id }}, {{ $escuelaSeleccionada->id }})">
                                                    <i class="ti ti-plus me-1"></i> Matricular
                                                </button>
                                            @elseif($item->estado == 'APROBADA')
                                                <button disabled type="button" class="btn btn-outline-success rounded-pill w-100 mt-auto">
                                                    <i class="ti ti-check me-1"></i> Ya aprobada
                                                </button>
                                            @else
                                                <button disabled type="button" class="btn btn-outline-secondary rounded-pill w-100 mt-auto">
                                                    <i class="ti ti-lock me-1"></i> No disponible
                                                </button>
                                            @endif
                                        @endif

                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center" role="alert">
                                    <span>Este alumno no tiene matrículas activas ni materias nuevas disponibles en esta
                                        escuela.</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @livewire('matricula.matricula-modal')

@endsection
