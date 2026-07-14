@extends('layouts/layoutMaster')

@section('title', 'Nuevo Hito')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <style>
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            border-color: #dbdade !important;
            background-color: #f8f7fa;
        }

        .ql-container.ql-snow {
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
            border-color: #dbdade !important;
        }

        .ql-editor {
            min-height: 150px;
            max-height: 300px;
            overflow-y: auto;
            font-family: inherit;
            font-size: 0.9375rem;
        }

        /* Tarjeta del tipo de origen (selección inicial) */
        .origen-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .origen-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .origen-card.selected {
            border-color: #7c5cfc;
            background-color: rgba(124, 92, 252, 0.04);
        }

        .origen-card .origen-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Sub-secciones del trigger */
        .trigger-section {
            display: none;
            padding: 16px;
            background-color: #fafafc;
            border-radius: 8px;
            border-left: 3px solid #7c5cfc;
        }

        .trigger-section.active {
            display: block;
        }

        .nav-pills-hitos .nav-link {
            color: #5a5a6e;
            font-weight: 500;
            border-radius: 8px;
            padding: 8px 18px;
        }

        .nav-pills-hitos .nav-link.active {
            background-color: rgba(124, 92, 252, 0.12);
            color: #7c5cfc;
        }

        .preview-foto {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dbdade;
        }

        .placeholder-foto {
            width: 100%;
            aspect-ratio: 16 / 9;
            border-radius: 8px;
            border: 2px dashed #dbdade;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f7fa;
            color: #8b8b9e;
        }

        .preview-video {
            width: 100%;
            aspect-ratio: 16 / 9;
            background-color: #000;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            position: relative;
        }

        .preview-video i {
            font-size: 3rem;
            opacity: 0.7;
        }

        .fotos-grid-preview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .foto-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
        }

        .foto-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .foto-preview-item .remove-foto {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(0,0,0,0.6);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-banner {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            background-color: rgba(59, 130, 246, 0.08);
            border-left: 3px solid #3b82f6;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .info-banner i {
            color: #3b82f6;
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .info-banner p {
            margin: 0;
            font-size: 0.85rem;
            color: #4a4a5a;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/quill/quill.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
            'use strict';

            // Datos de ejemplo hardcoded para mostrar al cliente
            const datosEjemplo = {
                titulo: 'Bautismo en el Río',
                descripcion: 'Momento especial donde los nuevos creyentes deciden dar el paso de fe más importante. Un día lleno de emoción, familia y renovación espiritual.',
                mensaje_usuario: '¡Felicidades por tu bautismo! 🌊 Hoy diste el paso más importante de tu vida. Que este nuevo comienzo esté lleno de fe, esperanza y propósito. ¡Dios tiene grandes planes para ti!',
                video_url: 'https://www.youtube.com/watch?v=VN7luRDyJwQ',
                requiere_asistencia: true,
                asignar_al_crear: false,
                permite_fotos_usuario: true,
                max_fotos_usuario: 3,
                max_peso_kb: 2048,
                // Trigger automático: escuelas → materia
                origen_seleccionado: 'automatico',
                trigger_modulo: 'escuelas',
                trigger_escuela_id: 1,
                trigger_nivel_id: 2,
                trigger_materia_id: 5,
                // Restricciones
                sedes: [1, 2],
                tipos_usuario: [1, 2],
            };

            // Pre-rellenar campos con datos de ejemplo
            $('#titulo').val(datosEjemplo.titulo);
            $('#video_url').val(datosEjemplo.video_url);
            $('#mensaje_usuario').val(datosEjemplo.mensaje_usuario);
            $('#max_fotos_usuario').val(datosEjemplo.max_fotos_usuario);
            $('#max_peso_kb').val(datosEjemplo.max_peso_kb);

            // Inicializar Select2
            $(".select2").select2({
                placeholder: 'Seleccione...',
                allowClear: true,
                width: '100%'
            });

            // Pre-seleccionar restricciones
            $('#sedes').val(datosEjemplo.sedes).trigger('change');
            $('#tiposUsuario').val(datosEjemplo.tipos_usuario).trigger('change');

            // Pre-seleccionar tipo de origen
            $(`.origen-card[data-origen="${datosEjemplo.origen_seleccionado}"]`).addClass('selected');
            $('#origen').val(datosEjemplo.origen_seleccionado);
            mostrarSeccionOrigen(datosEjemplo.origen_seleccionado);

            // Pre-seleccionar módulo de trigger
            setTimeout(() => {
                $('#trigger_modulo').val(datosEjemplo.trigger_modulo).trigger('change');
                mostrarSeccionTrigger(datosEjemplo.trigger_modulo);

                setTimeout(() => {
                    if (datosEjemplo.trigger_escuela_id) {
                        $('#trigger_escuela_id').val(datosEjemplo.trigger_escuela_id).trigger('change');
                    }
                    if (datosEjemplo.trigger_materia_id) {
                        $('#trigger_materia_id').val(datosEjemplo.trigger_materia_id);
                    }
                }, 100);
            }, 100);

            // Toggle: tipo de origen
            $('.origen-card').on('click', function() {
                $('.origen-card').removeClass('selected');
                $(this).addClass('selected');
                const origen = $(this).data('origen');
                $('#origen').val(origen);
                mostrarSeccionOrigen(origen);
            });

            function mostrarSeccionOrigen(origen) {
                $('.trigger-section').removeClass('active');
                $('#seccion-' + origen).addClass('active');
            }

            // Toggle: módulo de trigger
            $('#trigger_modulo').on('change', function() {
                const modulo = $(this).val();
                mostrarSeccionTrigger(modulo);
            });

            function mostrarSeccionTrigger(modulo) {
                $('.trigger-sub-section').removeClass('active');
                if (modulo) {
                    $('#trigger-' + modulo).addClass('active');
                }
            }

            // Toggle: requiere asistencia / asignar al crear
            $('#requiere_asistencia').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#asignar_al_crear_container').fadeOut();
                    $('#asignar_al_crear').prop('checked', false);
                } else {
                    $('#asignar_al_crear_container').fadeIn();
                }
            });

            // Toggle: permite fotos usuario
            $('#permite_fotos_usuario').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#fotos_usuario_config').fadeIn();
                } else {
                    $('#fotos_usuario_config').fadeOut();
                }
            });

            // Toggle: secciones de tabs
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Reinicializar Select2 si está oculto
                $(e.target.getAttribute('href')).find('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({ width: '100%' });
                });
            });

            // Demo: click en guardar
            $('#formulario').on('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Vista de demostración',
                    text: 'Esta es una vista de ejemplo. No se guarda información real.',
                    icon: 'info',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill'
                    },
                    buttonsStyling: false
                });
            });
        });
    </script>
@endsection

@section('content')

    <div class="d-flex align-items-center mb-4">
        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-label-secondary me-2" onclick="history.back();">
            <i class="ti ti-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-semibold text-primary">Nuevo Hito</h4>
            <small class="text-muted">Configura un evento especial para el seguimiento espiritual</small>
        </div>
    </div>

    @include('layouts.status-msn')

    <form action="javascript:void(0);" method="POST" enctype="multipart/form-data" id="formulario">
        @csrf

        {{-- ============================================ --}}
        {{-- SELECTOR DE TIPO DE ORIGEN                  --}}
        {{-- ============================================ --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-1 fw-semibold">
                    <i class="ti ti-flag-3 me-2 text-primary"></i>¿Cómo quieres crear este hito?
                </h5>
                <p class="text-muted mb-4 small">Selecciona el origen. Esto define de dónde viene el contenido y cuándo se asigna a los usuarios.</p>

                <input type="hidden" name="origen" id="origen" value="general">

                <div class="row g-3">
                    {{-- ORIGEN: GENERAL --}}
                    <div class="col-md-4">
                        <div class="origen-card card h-100 p-3" data-origen="general">
                            <div class="d-flex align-items-start gap-3">
                                <div class="origen-icon" style="background-color: rgba(124, 92, 252, 0.12); color: #7c5cfc;">
                                    <i class="ti ti-edit"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Manual</h6>
                                    <p class="text-muted small mb-0">Creas el hito desde cero. Tú controlas todo el contenido y la asignación.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ORIGEN: AUTOMATICO --}}
                    <div class="col-md-4">
                        <div class="origen-card card h-100 p-3" data-origen="automatico">
                            <div class="d-flex align-items-start gap-3">
                                <div class="origen-icon" style="background-color: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                                    <i class="ti ti-bolt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Automático</h6>
                                    <p class="text-muted small mb-0">Se asigna al usuario cuando cumple una condición en otro módulo (escuelas, grupos, etc.).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ORIGEN: ACTIVIDAD --}}
                    <div class="col-md-4">
                        <div class="origen-card card h-100 p-3" data-origen="actividad">
                            <div class="d-flex align-items-start gap-3">
                                <div class="origen-icon" style="background-color: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                                    <i class="ti ti-calendar-event"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">De una Actividad</h6>
                                    <p class="text-muted small mb-0">Asociado a una actividad existente. Con o sin control de asistencia.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- ============================================ --}}
            {{-- COLUMNA IZQUIERDA: PORTADA + MULTIMEDIA   --}}
            {{-- ============================================ --}}
            <div class="col-md-4 col-12 mb-4">
                {{-- Portada --}}
                <div class="card mb-4 shadow-sm">
                    <h5 class="card-header text-black fw-semibold">
                        <i class="ti ti-photo me-1"></i> Portada
                    </h5>
                    <div class="card-body">
                        <div class="position-relative">
                            <img src="{{ asset('assets/img/illustrations/page-pricing-enterprise.png') }}"
                                 alt="Preview portada" class="preview-foto">
                            <button type="button"
                                    class="btn btn-sm btn-icon btn-primary rounded-circle position-absolute bottom-0 end-0 mb-2 me-2 shadow"
                                    data-bs-toggle="modal" data-bs-target="#modalPortada">
                                <i class="ti ti-camera"></i>
                            </button>
                        </div>
                        <small class="d-block mt-2 text-muted text-center">Relación de aspecto recomendada 16:9</small>
                    </div>
                </div>

                {{-- Video --}}
                <div class="card mb-4 shadow-sm">
                    <h5 class="card-header text-black fw-semibold">
                        <i class="ti ti-video me-1"></i> Video (opcional)
                    </h5>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="video_url" class="form-label">URL YouTube o Vimeo</label>
                            <input type="url" class="form-control" id="video_url" name="video_url"
                                   placeholder="https://www.youtube.com/watch?v=...">
                            <small class="text-muted">Pega el enlace completo del video</small>
                        </div>
                        <div class="preview-video">
                            <i class="ti ti-player-play"></i>
                        </div>
                    </div>
                </div>

                {{-- Fotos administrativas --}}
                <div class="card shadow-sm">
                    <h5 class="card-header text-black fw-semibold d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-camera me-1"></i> Fotos (admin)</span>
                        <span class="badge bg-label-primary rounded-pill">Hasta 20</span>
                    </h5>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="file" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Sube las fotos oficiales del evento</small>
                        </div>

                        <div class="fotos-grid-preview">
                            <div class="foto-preview-item">
                                <img src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?w=200&q=80" alt="Foto">
                                <button type="button" class="remove-foto"><i class="ti ti-x"></i></button>
                            </div>
                            <div class="foto-preview-item">
                                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200&q=80" alt="Foto">
                                <button type="button" class="remove-foto"><i class="ti ti-x"></i></button>
                            </div>
                            <div class="foto-preview-item">
                                <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=200&q=80" alt="Foto">
                                <button type="button" class="remove-foto"><i class="ti ti-x"></i></button>
                            </div>
                            <div class="foto-preview-item">
                                <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=200&q=80" alt="Foto">
                                <button type="button" class="remove-foto"><i class="ti ti-x"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- COLUMNA DERECHA: CONTENIDO + CONFIG         --}}
            {{-- ============================================ --}}
            <div class="col-md-8 col-12 mb-4">
                {{-- Datos básicos --}}
                <div class="card mb-4 shadow-sm">
                    <h5 class="card-header text-black fw-semibold">
                        <i class="ti ti-info-circle me-1"></i> Información básica
                    </h5>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="titulo" class="form-label fw-semibold">Título del hito *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required
                                       placeholder="Ej: Bautismo en el Río">
                            </div>

                            <div class="col-12">
                                <label for="editor-descripcion" class="form-label fw-semibold">Descripción</label>
                                <div id="editor-descripcion"></div>
                                <input type="hidden" name="descripcion" id="descripcion">
                            </div>

                            <div class="col-12">
                                <label for="mensaje_usuario" class="form-label fw-semibold">
                                    <i class="ti ti-message-circle me-1 text-primary"></i>
                                    Mensaje personalizado para el usuario
                                </label>
                                <textarea class="form-control" id="mensaje_usuario" name="mensaje_usuario" rows="3"
                                          placeholder="Ej: ¡Felicidades por tu bautismo! Que este sea el comienzo de una vida llena de fe y propósito."></textarea>
                                <small class="text-muted">
                                    Este mensaje se mostrará destacado en el muro del usuario cuando reciba este hito. Si lo dejas vacío, se usará un mensaje por defecto.
                                </small>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                    <label class="form-check-label" for="activo">
                                        <strong>Hito activo</strong>
                                        <small class="d-block text-muted">Si se desactiva, no se mostrará en el muro de los usuarios.</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- SECCIÓN CONDICIONAL: ORIGEN               --}}
                {{-- ============================================ --}}

                {{-- Sección: Actividad --}}
                <div class="trigger-section" id="seccion-actividad">
                    <div class="info-banner">
                        <i class="ti ti-info-circle"></i>
                        <p><strong>Hito basado en una actividad existente.</strong> Selecciona la actividad de la lista. Puedes requerir asistencia o asignarlo a todos al guardar.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="actividad_id" class="form-label fw-semibold">Actividad *</label>
                            <select id="actividad_id" name="actividad_id" class="select2 form-select">
                                <option value="">Seleccionar actividad...</option>
                                <option value="1">Concierto Alex Campos</option>
                                <option value="2">Bautismo - Servicio Dominical</option>
                                <option value="3">Retiro de Jóvenes 2024</option>
                                <option value="4">Cena de Navidad</option>
                                <option value="5">Campamento Familiar</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requiere_asistencia" name="requiere_asistencia">
                                <label class="form-check-label" for="requiere_asistencia">
                                    <strong>Requiere confirmación de asistencia</strong>
                                    <small class="d-block text-muted">El hito solo se mostrará al usuario si el admin confirma que asistió a la actividad.</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-12" id="asignar_al_crear_container">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="asignar_al_crear" name="asignar_al_crear">
                                <label class="form-check-label" for="asignar_al_crear">
                                    <strong>Asignar a todos los usuarios al guardar</strong>
                                    <small class="d-block text-muted">Para actividades donde no se requiere asistencia (ej: conciertos). El hito aparece en el muro de todos.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección: Automático --}}
                <div class="trigger-section" id="seccion-automatico">
                    <div class="info-banner">
                        <i class="ti ti-info-circle"></i>
                        <p><strong>Hito automático.</strong> Se asignará automáticamente a cada usuario cuando cumpla la condición configurada abajo. Puedes aplicar el hito a usuarios existentes que ya cumplen la condición.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="trigger_modulo" class="form-label fw-semibold">Módulo disparador *</label>
                            <select id="trigger_modulo" name="trigger_modulo" class="form-select">
                                <option value="">Seleccionar módulo...</option>
                                <option value="pasos_crecimiento">Pasos de Crecimiento</option>
                                <option value="tareas_consolidacion">Tareas de Consolidación</option>
                                <option value="escuelas">Escuelas</option>
                                <option value="grupos">Grupos</option>
                            </select>
                        </div>

                        {{-- Sub-trigger: pasos_crecimiento --}}
                        <div class="trigger-sub-section col-12" id="trigger-pasos_crecimiento">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="trigger_paso_crecimiento_id" class="form-label fw-semibold">Paso de Crecimiento *</label>
                                    <select id="trigger_paso_crecimiento_id" class="select2 form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Bienvenida</option>
                                        <option value="2">Discipulado</option>
                                        <option value="3">Consolidación</option>
                                        <option value="4">Liderazgo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="trigger_estado_paso_id" class="form-label fw-semibold">Estado requerido *</label>
                                    <select id="trigger_estado_paso_id" class="select2 form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">En proceso</option>
                                        <option value="2">En revisión</option>
                                        <option value="3">Finalizado</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Ej: Cuando el paso "Bienvenida" cambie al estado "Finalizado".</small>
                        </div>

                        {{-- Sub-trigger: tareas_consolidacion --}}
                        <div class="trigger-sub-section col-12" id="trigger-tareas_consolidacion">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="trigger_tarea_consolidacion_id" class="form-label fw-semibold">Tarea *</label>
                                    <select id="trigger_tarea_consolidacion_id" class="select2 form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Primer Contacto</option>
                                        <option value="2">Visita al Hogar</option>
                                        <option value="3">Consejería Inicial</option>
                                        <option value="4">Presentación en Grupo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="trigger_estado_tarea_id" class="form-label fw-semibold">Estado requerido *</label>
                                    <select id="trigger_estado_tarea_id" class="select2 form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Asignada</option>
                                        <option value="2">En proceso</option>
                                        <option value="3">Completada</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">El asesor confirma la tarea y se asigna el hito al usuario.</small>
                        </div>

                        {{-- Sub-trigger: escuelas --}}
                        <div class="trigger-sub-section col-12" id="trigger-escuelas">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="trigger_escuela_id" class="form-label fw-semibold">Escuela *</label>
                                    <select id="trigger_escuela_id" class="select2 form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Escuela Bíblica</option>
                                        <option value="2">Escuela de Liderazgo</option>
                                        <option value="3">Escuela de Matrimonios</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="trigger_materia_id" class="form-label fw-semibold">Materia específica</label>
                                    <select id="trigger_materia_id" class="select2 form-select" disabled>
                                        <option value="">Todas las materias</option>
                                        <option value="1">Nuevo Testamento</option>
                                        <option value="2">Antiguo Testamento</option>
                                        <option value="3">Hermenéutica</option>
                                        <option value="4">Teología Sistemática</option>
                                        <option value="5">Bautismo (Especial)</option>
                                    </select>
                                    <small class="text-muted">Si se deja vacío, se dispara con cualquier materia de la escuela.</small>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Se asigna cuando el usuario aprueba la materia seleccionada.</small>
                        </div>

                        {{-- Sub-trigger: grupos --}}
                        <div class="trigger-sub-section col-12" id="trigger-grupos">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="trigger_tipo_grupo_id" class="form-label fw-semibold">Tipo de Grupo *</label>
                                    <select id="trigger_tipo_grupo_id" class="select2 form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Grupos de Crecimiento</option>
                                        <option value="2">Grupos de Matrimonios</option>
                                        <option value="3">Grupos Juveniles</option>
                                        <option value="4">Ministerio Infantil</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="trigger_tipo" class="form-label fw-semibold">Rol del usuario *</label>
                                    <select id="trigger_tipo" class="form-select">
                                        <option value="asignacion_integrante">Integrante (recién agregado)</option>
                                        <option value="designacion_lider">Líder / Encargado (recién nombrado)</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Crea dos hitos diferentes si quieres que integrante y líder tengan fotos distintas.</small>
                        </div>

                        {{-- Botón: aplicar retroactivo --}}
                        <div class="col-12 mt-3">
                            <div class="alert alert-warning d-flex align-items-start gap-2 mb-0">
                                <i class="ti ti-alert-triangle mt-1"></i>
                                <div>
                                    <strong>¿Ya tienes usuarios que cumplen esta condición?</strong>
                                    <p class="mb-2 small">Después de guardar, podrás aplicar este hito a usuarios existentes que ya cumplieron el disparador.</p>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill" disabled>
                                        <i class="ti ti-history me-1"></i> Aplicar a usuarios existentes (después de guardar)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección: General (oculta por defecto, se muestra si origen=general) --}}
                <div class="trigger-section" id="seccion-general">
                    <div class="info-banner">
                        <i class="ti ti-info-circle"></i>
                        <p><strong>Hito manual.</strong> Tienes control total. Después de guardar, podrás asignarlo a usuarios específicos desde la lista de hitos.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- TARJETA: PERMISOS DE FOTOS DE USUARIO       --}}
        {{-- ============================================ --}}
        <div class="card mb-4 shadow-sm">
            <h5 class="card-header text-black fw-semibold">
                <i class="ti ti-camera-plus me-1"></i> Fotos de los usuarios
            </h5>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="permite_fotos_usuario" name="permite_fotos_usuario" checked>
                    <label class="form-check-label" for="permite_fotos_usuario">
                        <strong>Permitir que los usuarios suban fotos</strong>
                        <small class="d-block text-muted">Los usuarios verán una zona de carga en este hito desde su muro.</small>
                    </label>
                </div>

                <div id="fotos_usuario_config" class="row g-3">
                    <div class="col-md-6">
                        <label for="max_fotos_usuario" class="form-label fw-semibold">Máximo de fotos por usuario</label>
                        <input type="number" class="form-control" id="max_fotos_usuario" name="max_fotos_usuario" min="1" max="10" value="3">
                    </div>
                    <div class="col-md-6">
                        <label for="max_peso_kb" class="form-label fw-semibold">Peso máximo por foto (KB)</label>
                        <input type="number" class="form-control" id="max_peso_kb" name="max_peso_kb" min="100" max="10240" value="2048">
                        <small class="text-muted">Por defecto: 2MB (2048 KB)</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- TARJETA: RESTRICCIONES DE VISIBILIDAD       --}}
        {{-- ============================================ --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header border-bottom py-2 cursor-pointer d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#collapseRestricciones" aria-expanded="true"
                 aria-controls="collapseRestricciones">
                <h5 class="card-header text-black fw-semibold p-0 border-0">
                    <i class="ti ti-filter me-1"></i> Restricciones de visibilidad
                </h5>
                <i class="ti ti-chevron-down"></i>
            </div>
            <div id="collapseRestricciones" class="collapse show">
                <div class="card-body pt-3">
                    <div class="row g-3">
                        {{-- Visible para todos --}}
                        <div class="col-12 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="visible_todos" name="visible_todos">
                                <label class="form-check-label w-100 cursor-pointer" for="visible_todos">
                                    <strong>Visible para todos los usuarios</strong>
                                    <small class="d-block text-muted">Si se desactiva, solo los usuarios que cumplan los requisitos podrán verlo.</small>
                                </label>
                            </div>
                        </div>

                        <div id="seccionRestricciones">
                            <hr class="my-3">

                            <div class="row g-3">
                                {{-- Sedes --}}
                                <div class="col-12">
                                    <label class="form-label fw-bold">Sedes permitidas</label>
                                    <select id="sedes" name="sedes[]" class="select2 form-select" multiple>
                                        <option value="1">Sede Principal - Centro</option>
                                        <option value="2">Sede Norte</option>
                                        <option value="3">Sede Sur</option>
                                        <option value="4">Sede Occidental</option>
                                    </select>
                                </div>

                                {{-- Tipos de Usuario --}}
                                <div class="col-12">
                                    <label class="form-label fw-bold">Tipos de usuario</label>
                                    <select id="tiposUsuario" name="tiposUsuario[]" class="select2 form-select" multiple>
                                        <option value="1">Miembro Activo</option>
                                        <option value="2">Asistente</option>
                                        <option value="3">Líder</option>
                                        <option value="4">Pastor</option>
                                        <option value="5">Visitante</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botonera --}}
        <div class="d-flex mb-5 mt-4 gap-2">
            <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2">
                <i class="ti ti-device-floppy me-2"></i> Guardar Hito
            </button>
            <button type="button" class="btn btn-label-secondary rounded-pill px-6 py-2" onclick="history.back();">
                Cancelar
            </button>
        </div>
    </form>

    {{-- Modal Portada --}}
    <div class="modal fade" id="modalPortada" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-simple">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4 p-4">
                        <h3 class="mb-2"><i class="ti ti-camera ti-lg"></i> Subir portada</h3>
                        <p class="text-muted">Selecciona y recorta la imagen de portada del hito</p>
                    </div>

                    <div class="row px-4">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Paso #1 Selecciona la imagen</label>
                                <input class="form-control" type="file" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-outline-secondary px-5 rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-5" data-bs-dismiss="modal">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
