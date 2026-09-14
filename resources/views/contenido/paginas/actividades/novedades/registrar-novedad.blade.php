@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Registrar Novedad - ' . $actividad->nombre)

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/select2/select2.scss'
    ])
@endsection

@section('page-style')
    @vite([
        'resources/assets/vendor/scss/pages/page-profile.scss'
    ])
    <style>
        body {
            background-color: #f8f9fa !important;
        }
        .banner-actividad {
            min-height: 160px;
            background-size: cover;
            background-position: center;
            border-radius: 12px 12px 0 0;
        }
        .card-novedad {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js'
    ])
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-12">

            <!-- Logo y Encabezado Superior -->
            <div class="text-center mb-4">
                @include('_partials.logo_iglesia', ['logo_negro' => true, 'width' => '180px'])
                <h3 class="fw-bold text-primary mt-3 mb-1">Registro de Novedad o Inconsistencia</h3>
                <p class="text-muted">Si presentas un inconveniente con los requisitos o la inscripción, infórmanos para darte soporte.</p>
            </div>

            <!-- Ficha Resumen de la Actividad -->
            <div class="card card-novedad mb-4">
                @if ($actividad->portada_url)
                    <div class="banner-actividad" style="background-image: url('{{ $actividad->portada_url }}');"></div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <span class="badge bg-label-primary mb-2">
                                <i class="ti ti-calendar-event me-1"></i> {{ $actividad->tipo->nombre ?? 'Actividad' }}
                            </span>
                            <h4 class="mb-1 fw-bold text-dark">{{ $actividad->nombre }}</h4>
                            @if ($actividad->descripcion_corta)
                                <p class="text-muted mb-0 small">{{ $actividad->descripcion_corta }}</p>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block"><i class="ti ti-calendar me-1"></i> Fecha inicio: <b>{{ $actividad->fecha_inicio }}</b></small>
                            @if ($actividad->fecha_finalizacion)
                                <small class="text-muted d-block"><i class="ti ti-calendar-check me-1"></i> Fecha fin: <b>{{ $actividad->fecha_finalizacion }}</b></small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Novedad -->
            <div class="card card-novedad">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="ti ti-file-text me-2 text-primary"></i> Datos del Reporte
                    </h5>
                    <small class="text-muted">Diligencia todos los campos con la información exacta para validar tu caso.</small>
                </div>

                <div class="card-body pt-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading mb-1"><i class="ti ti-alert-triangle me-1"></i> Por favor corrige los siguientes errores:</h6>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @php
                        $nombreDefault = '';
                        $identificacionDefault = '';
                        $telefonoDefault = '';
                        $emailDefault = '';

                        if ($usuario) {
                            $partesNombre = array_filter([
                                $usuario->primer_nombre,
                                $usuario->segundo_nombre,
                                $usuario->primer_apellido,
                                $usuario->segundo_apellido,
                            ]);
                            $nombreDefault = implode(' ', $partesNombre);
                            $identificacionDefault = $usuario->identificacion ?? '';
                            $telefonoDefault = $usuario->telefono_movil ?? $usuario->telefono_fijo ?? $usuario->telefono_otro ?? '';
                            $emailDefault = $usuario->email ?? '';
                        }
                    @endphp

                    <form action="{{ route('actividades.novedades.store', $actividad) }}" method="POST" id="formNovedad" x-data="{ charCount: 0 }">
                        @csrf

                        <!-- Datos Personales -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold" for="nombre">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required
                                       placeholder="Ingresa tu nombre y apellido"
                                       value="{{ old('nombre', $nombreDefault) }}">
                            </div>

                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold" for="identificacion">Número de Identificación (Cédula) <span class="text-danger">*</span></label>
                                <input type="text" id="identificacion" name="identificacion" class="form-control" required
                                       placeholder="Ej: 1020304050"
                                       value="{{ old('identificacion', $identificacionDefault) }}">
                            </div>

                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold" for="telefono">Teléfono de contacto (Móvil / WhatsApp) <span class="text-danger">*</span></label>
                                <input type="text" id="telefono" name="telefono" class="form-control" required
                                       placeholder="Ej: 3001234567"
                                       value="{{ old('telefono', $telefonoDefault) }}">
                            </div>

                            <div class="col-md-6 col-12">
                                <label class="form-label fw-semibold" for="email">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" required
                                       placeholder="nombre@ejemplo.com"
                                       value="{{ old('email', $emailDefault) }}">
                                <div class="form-text">A este correo te enviaremos la respuesta formal de tu novedad.</div>
                            </div>
                        </div>

                        <!-- Campo Condicional: Materia o Escuela (Solo si es Actividad de Escuelas) -->
                        @if ($actividad->tipo && $actividad->tipo->tipo_escuelas)
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label class="form-label fw-bold text-dark" for="materia_id">
                                    <i class="ti ti-school me-1 text-primary"></i> Materia o Escuela a la que deseabas matricularte <span class="text-danger">*</span>
                                </label>
                                @if ($materiasEscuela->isNotEmpty())
                                    <select id="materia_id" name="materia_id" class="form-select select2" required>
                                        <option value="">-- Selecciona la materia a la que deseas ingresar --</option>
                                        @foreach ($materiasEscuela as $mat)
                                            <option value="{{ $mat->id }}" {{ old('materia_id') == $mat->id ? 'selected' : '' }}>
                                                {{ $mat->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Indica a cuál de las materias de la escuela te querías matricular.</div>
                                @else
                                    <input type="text" id="materia_nombre" name="materia_nombre" class="form-control" required
                                           placeholder="Ej: Mentor Espiritual, Caminos a la Libertad, etc."
                                           value="{{ old('materia_nombre') }}">
                                @endif
                            </div>
                        @endif

                        <!-- Tipo de Novedad -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="tipo_novedad_id">Tipo de Novedad <span class="text-danger">*</span></label>
                            <select id="tipo_novedad_id" name="tipo_novedad_id" class="form-select select2" required>
                                <option value="">-- Selecciona la opción que mejor describe tu situación --</option>
                                @foreach ($tiposNovedad as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_novedad_id') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Asunto -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="asunto">Asunto del mensaje <span class="text-danger">*</span></label>
                            <input type="text" id="asunto" name="asunto" class="form-control" required
                                   placeholder="Ej: Ya aprobé Caminos a la Libertad pero no me permite matricularme"
                                   value="{{ old('asunto') }}">
                        </div>

                        <!-- Detalle / Descripción con contador -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" for="descripcion">Explicación detallada del inconveniente <span class="text-danger">*</span></label>
                                <small class="text-muted"><span x-text="charCount">0</span> / 500 caracteres</small>
                            </div>
                            <textarea id="descripcion" name="descripcion" rows="4" maxlength="500" class="form-control" required
                                      placeholder="Describe brevemente lo ocurrido (ej. en qué año o período cursaste la materia previa, si tienes el certificado o si tu número de cédula cambió)..."
                                      x-on:input="charCount = $el.value.length">{{ old('descripcion') }}</textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('actividades.perfil', $actividad) }}" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="ti ti-arrow-left me-1"></i> Volver a la actividad
                            </a>
                            <button type="submit" id="btnEnviarNovedad" class="btn btn-primary rounded-pill px-5 shadow">
                                <i class="ti ti-send me-1"></i> Enviar novedad
                            </button>
                        </div>
                    </form>
                </div>
            </div>



        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                placeholder: "Selecciona una opción",
                width: '100%'
            });
        }

        const formNovedad = document.getElementById('formNovedad');
        if (formNovedad) {
            formNovedad.addEventListener('submit', function() {
                const btn = document.getElementById('btnEnviarNovedad');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando novedad...';
                }
            });
        }
    });

    @if (session('novedad_registrada'))
        function mostrarAlertaNovedad() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Novedad Registrada!',
                    html: 'Tu novedad ha sido registrada con éxito.<br><br>Nuestro equipo revisará tu novedad y será atendida en las <b>próximas 24 horas</b>.',
                    icon: 'success',
                    confirmButtonText: 'Volver a la actividad',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill px-4'
                    },
                    buttonsStyling: false
                }).then(() => {
                    window.location.href = "{{ route('actividades.perfil', $actividad) }}";
                });
            } else {
                setTimeout(mostrarAlertaNovedad, 100);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mostrarAlertaNovedad);
        } else {
            mostrarAlertaNovedad();
        }
    @endif
</script>
@endsection
