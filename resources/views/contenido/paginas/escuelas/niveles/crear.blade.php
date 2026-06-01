@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Crear Nuevo Grado')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
    <script type="module">
        const editor = new Quill('#editor', {
            bounds: '#editor',
            placeholder: 'Descripcion',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            },
            theme: 'snow'
        });

        editor.root.innerHTML = "{!! old('descripcion', '') !!}";

        editor.on('text-change', (delta, oldDelta, source) => {
            $('#descripcion').val(editor.root.innerHTML);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Seleccionar opciones',
                allowClear: true
            });

            // --- Logica Toggle UI ---
            const switchDiaLimite = $('#diaLimiteHabilitado');
            const selectDia = $('#dia_limite_reporte');
            const inputCantidadReportesSemana = $('#cantidad_reportes_semana');
            const inputDiasPlazoReporte = $('#dias_plazo_reporte');

            function actualizarCamposLimiteReporte() {
                if (switchDiaLimite.is(':checked')) {
                    selectDia.prop('disabled', false).prop('required', true);
                    inputCantidadReportesSemana.prop('disabled', true).prop('required', false).val('');
                    inputDiasPlazoReporte.prop('disabled', true).prop('required', false).val('');
                } else {
                    selectDia.prop('disabled', true).prop('required', false).val('').trigger('change');
                    inputCantidadReportesSemana.prop('disabled', false).prop('required', true);
                    inputDiasPlazoReporte.prop('disabled', false).prop('required', true);
                }
            }

            actualizarCamposLimiteReporte();
            switchDiaLimite.on('change', actualizarCamposLimiteReporte);

            // Toggles visibility
            // Toggles visibility
             $('#togglehabilitarAsistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containerAsistenciasMinimas').removeClass('d-none').show();
                    $('.row-asistencias, .plazos-reporte').removeClass('d-none').show();
                } else {
                    $('#containerAsistenciasMinimas').addClass('d-none').hide();
                    $('.row-asistencias, .plazos-reporte').addClass('d-none').hide();
                }
            });
            if ($('#togglehabilitarAsistencias').is(':checked')) {
                $('#containerAsistenciasMinimas').show();
                $('.row-asistencias, .plazos-reporte').show();
            } else {
                $('#containerAsistenciasMinimas').hide();
                $('.row-asistencias, .plazos-reporte').hide();
            }

            $('#togglehabilitarInasistencias').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containerAsistenciasAlerta').removeClass('d-none').show();
                } else {
                    $('#containerAsistenciasAlerta').addClass('d-none').hide();
                }
            });
             if ($('#togglehabilitarInasistencias').is(':checked')) $('#containerAsistenciasAlerta').show(); else $('#containerAsistenciasAlerta').hide();
        });
    </script>
@endsection

@section('content')
    <form id="formNuevoNivel" action="{{ route('escuelas.niveles.store', $escuela) }}" method="POST">
        @csrf

        <!-- Encabezado -->
        <div class="col-md-12 mb-4">


                    <h5 class="mb-1 fw-semibold text-primary">Crear Nuevo Grado</h5>
                    <p class="text-black mb-0">Define la configuración completa para el nuevo grado académico.</p>


        </div>

        @include('layouts.status-msn')

        <div class="row equal-height-row">

            <!-- Columna Izquierda: Configuración Principal -->
            <div class="col-12 col-md-12 mb-3">
                <div class="card h-100 p-4">
                    <h5 class="mb-4 text-primary fw-semibold">Configuración General</h5>

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label for="nombre" class="form-label">Nombre del Grado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required autofocus>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="orden" class="form-label">Orden Jerárquico <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('orden') is-invalid @enderror" id="orden" name="orden" value="{{ old('orden', 1) }}" required>
                            @error('orden') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="tipo_usuario_objetivo_id" class="form-label">Tipo Usuario Objetivo</label>
                            <select class="form-select select2" id="tipo_usuario_objetivo_id" name="tipo_usuario_objetivo_id">
                                <option value="">Ninguno</option>
                                @foreach($tiposUsuario as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_usuario_objetivo_id') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Calificaciones -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Calificaciones</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="togglehabilitarCalificaciones" name="habilitar_calificaciones" value="1" @checked(old('habilitar_calificaciones', true))>
                                <label class="form-check-label" for="togglehabilitarCalificaciones">Habilitar</label>
                            </div>
                        </div>

                        <!-- Asistencias -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Asistencias</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="togglehabilitarAsistencias" name="habilitar_asistencias" value="1" @checked(old('habilitar_asistencias', true))>
                                <label class="form-check-label" for="togglehabilitarAsistencias">Habilitar</label>
                            </div>
                        </div>

                         <!-- Inasistencias -->
                         <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Inasistencias</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="togglehabilitarInasistencias" name="habilitar_inasistencias" value="1" @checked(old('habilitar_inasistencias', true))>
                                <label class="form-check-label" for="togglehabilitarInasistencias">Habilitar</label>
                            </div>
                        </div>

                        <!-- Obligatorio -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Carácter Obligatorio</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" name="caracter_obligatorio" value="1" @checked(old('caracter_obligatorio', true))>
                                <label class="form-check-label">Sí</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <div id="editor" style="height: 150px;"></div>
                        <input type="hidden" id="descripcion" name="descripcion" value="{{ old('descripcion') }}">
                        @error('descripcion') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3 text-primary">Reglas de Asistencia y Calificaciones</h5>



                    <div class="row mt-3 row-asistencias">

                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label">Máx. Reportes Permitidos</label>
                            <input type="number" class="form-control" name="limite_reportes" value="{{ old('limite_reportes') }}">
                        </div>

                        <!-- Campos condicionales de asistencia -->
                        <div id="containerAsistenciasMinimas" class="col-md-4 col-12 mb-3" style="display:none;">
                            <label class="form-label">Asistencias Mínimas para Aprobar</label>
                            <input type="number" class="form-control" name="asistencias_minimas" value="{{ old('asistencias_minimas') }}">
                        </div>

                        <div id="containerAsistenciasAlerta" class="col-md-4 col-12 mb-3" style="display:none;">
                            <label class="form-label">Alerta tras X Inasistencias</label>
                            <input type="number" class="form-control" name="cantidad_inasistencias_alerta" value="{{ old('cantidad_inasistencias_alerta') }}">
                        </div>
                    </div>



                    <hr class="my-4">


                    <div class="row align-items-center plazos-reporte">
                        <h5 class="mb-3 text-primary">Plazos de Reporte</h5>
                         <div class="col-md-6 col-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="diaLimiteHabilitado" name="dia_limite_habilitado" value="1" @checked(old('dia_limite_habilitado'))>
                                <label class="form-check-label" for="diaLimiteHabilitado">Usar Día Límite Fijo</label>
                            </div>
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                             <label class="form-label">Día Límite (Semanal)</label>
                             <select class="form-select" id="dia_limite_reporte" name="dia_limite_reporte" disabled>
                                <option value="">Seleccione...</option>
                                <option value="1">Lunes</option>
                                <option value="2">Martes</option>
                                <option value="3">Miércoles</option>
                                <option value="4">Jueves</option>
                                <option value="5">Viernes</option>
                                <option value="6">Sábado</option>
                                <option value="0">Domingo</option>
                             </select>
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                             <label class="form-label">Reportes por Semana</label>
                             <input type="number" class="form-control" id="cantidad_reportes_semana" name="cantidad_reportes_semana" value="{{ old('cantidad_reportes_semana') }}">
                        </div>
                         <div class="col-md-6 col-12 mb-3">
                             <label class="form-label">Días Plazo tras Reporte</label>
                             <input type="number" class="form-control" id="dias_plazo_reporte" name="dias_plazo_reporte" value="{{ old('dias_plazo_reporte') }}">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Columna Derecha: Componentes Livewire y Relaciones -->
            <div class="col-12 col-md-12 mt-4">
                <div class="card p-4">
                    <h5 class="mb-4 text-primary fw-semibold">Configuración de requisitos </h5>

                    <div class="p-3 border rounded ">
                        <h6 class="text-black  small fw-semibold mb-3">1. Al Iniciar el Grado</h6>
                        @livewire('escuelas.niveles.gestionar-pasos-iniciar-nivel', ['nivel' => new \App\Models\NivelAgrupacion])
                    </div>

                    <div class="p-3 border rounded  mt-3">
                        <h6 class="text-black  small fw-semibold mb-3">2. Prerrequisitos de Pasos</h6>
                        @livewire('escuelas.niveles.gestionar-pasos-requisito-nivel', ['nivel' => new \App\Models\NivelAgrupacion])
                    </div>

                    <div class="p-3 border rounded  mt-3">
                        <h6 class="text-black  small fw-semibold mb-3">3. Al Culminar el Grado</h6>
                        @livewire('escuelas.niveles.gestionar-pasos-culminados-nivel', ['nivel' => new \App\Models\NivelAgrupacion])
                    </div>

                    <div class="p-3 border rounded  mt-3">
                        <h6 class="text-black  small fw-semibold mb-3">4. Tareas de Consolidación Requeridas</h6>
                        @livewire('escuelas.niveles.gestionar-tareas-requisito-nivel', ['nivel' => new \App\Models\NivelAgrupacion])
                    </div>

                     <div class="p-3 border rounded  mt-3">
                        <h6 class="text-black  small fw-semibold mb-3">5. Tareas a Culminar al Cerrar</h6>
                        @livewire('escuelas.niveles.gestionar-tareas-culminadas-nivel', ['nivel' => new \App\Models\NivelAgrupacion])
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4 mb-5">
            <a href="{{ route('escuelas.niveles.index', $escuela) }}" class="btn btn-outline-secondary rounded-pill me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary rounded-pill">Guardar Grado</button>
        </div>

    </form>
@endsection
