@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Crear Periodos')

@section('vendor-style')
    {{-- Asegúrate de que los @vite estén correctos para tu versión y configuración --}}
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/js/app.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection

@section('page-script')
    {{-- Script para inicializar Flatpickr y Select2 --}}
    <script type="module">
        // Inicializar Flatpickr en los campos de fecha
        $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d", // Formato consistente con la validación
            disableMobile: true
        });

        // Inicializar Select2 en el campo de sedes múltiples
        $(document).ready(function() {
            // Nota: Select2 por defecto puede no mostrar el estilo 'is-invalid' directamente.
            // El mensaje de error debajo del campo es la forma más fiable de indicar el error.
            $('.select2').select2({
                placeholder: 'Selecciona una o más sedes',
                allowClear: true,
                width: '100%' // Asegurar que ocupe el ancho completo
            });
        });
    </script>
@endsection

@section('content')

    <h4 class="mb-1 text-primary">Crear periodo</h4>
    <p>Aquí podrás crear la configuración de tu nuevo periodo.</p>

    {{-- Mensaje de error general (si se envió desde el controlador con withErrors) --}}
    @if ($errors->has('error_general'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first('error_general') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Formulario --}}
    <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('periodo.guardar') }}"
        enctype="multipart/form-data">
        @csrf {{-- Token CSRF obligatorio --}}

        <div class="row mb-5 mt-5">
            <div class="card col-12">
                <div class="card-header">
                    <h5 class="mb-4">Configuración inicial</h5>
                </div>
                <div class="card-body row">

                    {{-- Campo Nombre --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label for="nombre" class="form-label">Nombre </label>
                        <input value="{{ old('nombre') }}" {{-- Mantener valor anterior --}} type="text"
                            class="form-control @error('nombre') is-invalid @enderror" {{-- Clase si hay error --}} id="nombre"
                            name="nombre" placeholder="Ej: Semestre 2025-1">
                        @error('nombre')
                            {{-- Mostrar error específico --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo Escuela asociada --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label for="escuelaId" class="form-label">Escuela asociada </label>
                        <select id="escuelaId" name="escuelaId"
                            class="form-select @error('escuelaId') is-invalid @enderror"> {{-- Clase si hay error --}}
                            <option value="">Selecciona una escuela</option>
                            @foreach ($escuelas as $escuela)
                                <option value="{{ $escuela->id }}"
                                    {{ old('escuelaId') == $escuela->id ? 'selected' : '' }}> {{-- Mantener selección anterior --}}
                                    {{ $escuela->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('escuelaId')
                            {{-- Mostrar error específico --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo Sistema de calificaciones --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label for="sistema_calificacion_id" class="form-label">Sistema de calificaciones </label>
                        <select id="sistema_calificacion_id" name="sistema_calificacion_id" {{-- Nombre corregido --}}
                            class="form-select @error('sistema_calificacion_id') is-invalid @enderror">
                            {{-- Clase si hay error --}}
                            <option value="">Selecciona un sistema</option>
                            @foreach ($sistemasCalifiacion as $sistema)
                                <option value="{{ $sistema->id }}"
                                    {{ old('sistema_calificacion_id') == $sistema->id ? 'selected' : '' }}>
                                    {{-- Mantener selección anterior --}}
                                    {{ $sistema->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('sistema_calificacion_id')
                            {{-- Mostrar error específico --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo Fecha inicio periodo --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label class="form-label" for="fecha_inicio">Fecha inicio periodo </label>
                        <input id="fecha_inicio" value="{{ old('fecha_inicio') }}" {{-- Mantener valor anterior --}}
                            placeholder="YYYY-MM-DD" name="fecha_inicio"
                            class="form-control fecha-picker @error('fecha_inicio') is-invalid @enderror"
                            {{-- Clase si hay error --}} type="text" />
                        @error('fecha_inicio')
                            {{-- Mostrar error específico --}}
                            <div class="invalid-feedback d-block">{{ $message }}</div> {{-- Usar d-block si flatpickr interfiere --}}
                        @enderror
                    </div>

                    {{-- Campo Fecha finalización periodo --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label class="form-label" for="fecha_fin">Fecha finalización periodo </label>
                        <input id="fecha_fin" value="{{ old('fecha_fin') }}" {{-- Mantener valor anterior --}}
                            placeholder="YYYY-MM-DD" name="fecha_fin"
                            class="form-control fecha-picker @error('fecha_fin') is-invalid @enderror"
                            {{-- Clase si hay error --}} type="text" />
                        @error('fecha_fin')
                            {{-- Mostrar error específico --}}
                            <div class="invalid-feedback d-block">{{ $message }}</div> {{-- Usar d-block si flatpickr interfiere --}}
                        @enderror
                    </div>

                    {{-- Campo Fecha limite calificaciones maestro --}}
                    <div class="mb-3 col-12 col-md-4">
                        <label class="form-label" for="fecha_limite_maestro">Fecha límite calificaciones maestro </label>
                        <input id="fecha_limite_maestro" value="{{ old('fecha_limite_maestro') }}" {{-- Mantener valor anterior --}}
                            placeholder="YYYY-MM-DD" name="fecha_limite_maestro"
                            class="form-control fecha-picker @error('fecha_limite_maestro') is-invalid @enderror"
                            {{-- Clase si hay error --}} type="text" />
                        @error('fecha_limite_maestro')
                            {{-- Mostrar error específico --}}
                            <div class="invalid-feedback d-block">{{ $message }}</div> {{-- Usar d-block si flatpickr interfiere --}}
                        @enderror
                    </div>

                    {{-- Campo Sedes habilitadas (Select2 Múltiple) --}}
                    <div class="col-12 mb-3">
                        <label for="sedes" class="form-label">Sedes habilitadas</label>
                        <select id="sedes" name="sedes[]" {{-- Nombre como array --}}
                            class="select2 form-select @error('sedes') is-invalid @enderror @error('sedes.*') is-invalid @enderror"
                            {{-- Clase si hay error en el array o sus elementos --}} multiple>
                            {{-- No necesita option vacía con select2 y placeholder --}}
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}"
                                    {{ is_array(old('sedes')) && in_array($sede->id, old('sedes')) ? 'selected' : '' }}>
                                    {{-- Mantener selección múltiple anterior --}}
                                    {{ $sede->nombre }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Mostrar error para el campo 'sedes' (ej: si es requerido y no se envía) --}}
                        @error('sedes')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        {{-- Mostrar error para elementos individuales de 'sedes' (ej: si un id no existe) --}}
                        @error('sedes.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div> {{-- Fin card-body --}}
            </div> {{-- Fin card --}}

            {{-- Botonera --}}
            <div class="d-flex mb-1 mt-5">
                <div class="me-auto">
                    {{-- Botón Guardar --}}
                    <button type="submit" class="btn rounded-pill btn-primary me-1 px-4"> {{-- Ajusté padding px-10 a px-4 --}}
                        <i class="mdi mdi-content-save me-2"></i>Guardar Periodo
                    </button>
                    {{-- Botón Cancelar (Opcional) --}}
                    <a href="{{ route('periodo.gestionar') }}" class="btn rounded-pill btn-secondary px-4">
                        <i class="mdi mdi-cancel me-2"></i>Atrás
                    </a>
                </div>
            </div>
            {{-- /Botonera --}}

        </div> {{-- Fin row mb-5 mt-5 --}}
    </form> {{-- Fin Formulario --}}

@endsection
