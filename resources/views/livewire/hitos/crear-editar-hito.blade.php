<div>
    {{-- Header con título y botón volver --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="ti ti-trophy me-2 text-primary"></i>{{ $hitoId ? 'Editar Hito: ' . $titulo : 'Nuevo Hito' }}
            </h4>
            <p class="text-muted mb-0">
                @if(!$hitoId)
                    Configura la información general y multimedia para la creación de un nuevo hito.
                @else
                    Configura los detalles, multimedia, disparadores de activación y filtros de audiencia para el hito <strong class="text-dark">"{{ $titulo }}"</strong>.
                @endif
            </p>
        </div>
        <a href="{{ route('hitos.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>

    <form wire:submit.prevent="guardar">
        {{-- ============================================================ --}}
        {{-- PANEL 1: INFORMACIÓN GENERAL --}}
        {{-- ============================================================ --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title fw-bold mb-0 text-primary d-flex align-items-center">
                    <span class="badge bg-label-primary text-white rounded-circle p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">1</span>
                    Información General
                </h5>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    {{-- Tipo de Hito --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Tipo de Hito <span class="text-danger">*</span></label>
                        <div wire:ignore id="container-select-tipo-hito">
                            <select x-data="{
                                init() {
                                    let select = $(this.$refs.select);
                                    select.select2({
                                        placeholder: '-- Seleccionar Tipo de Hito --',
                                        allowClear: false
                                    });
                                    select.on('change', () => {
                                        @this.set('tipo_hito_id', select.val());
                                    });
                                }
                            }" x-ref="select" id="tipo_hito_id" class="select2 form-select @error('tipo_hito_id') is-invalid @enderror">
                                @foreach($tiposHito as $t)
                                    <option value="{{ $t->id }}" {{ (string)$t->id === (string)$tipo_hito_id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('tipo_hito_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @if($tipoSeleccionado)
                            <div class="d-flex align-items-center mt-2">
                                <span class="badge text-white px-2 py-1 me-2" style="background-color: {{ $tipoSeleccionado->color ?? '#7c5cfc' }};">
                                    <i class="{{ $tipoSeleccionado->icono ?? 'ti ti-tag' }} me-1"></i> {{ $tipoSeleccionado->nombre }}
                                </span>
                                <small class="text-muted">{{ $tipoSeleccionado->descripcion }}</small>
                            </div>
                        @endif
                    </div>

                    {{-- Título --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Título del Hito <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror"
                               wire:model="titulo" placeholder="Ej: Bautismo en Agua, Graduación Escuela Bíblica, Retiro...">
                        @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Fecha del Evento --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Fecha del Evento / Conmemoración</label>
                        <input type="date" class="form-control @error('fecha_evento') is-invalid @enderror"
                               wire:model="fecha_evento">
                        <small class="text-muted">Opcional para hitos generales o de fecha fija.</small>
                        @error('fecha_evento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Switch Activo --}}
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="checkActivo" wire:model="activo">
                            <label class="form-check-label fw-semibold text-dark" for="checkActivo">Hito Activo y Publicado</label>
                        </div>
                    </div>

                    {{-- Descripción General --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Descripción del Hito</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                  wire:model="descripcion" rows="3"
                                  placeholder="Explica el contexto o significado de este hito para la congregación..."></textarea>
                        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Mensaje Personalizado para el Usuario --}}
                    <div class="col-12">
                        <div class="border-start border-primary border-3 ps-3">
                            <label class="form-label fw-semibold text-dark d-flex align-items-center">
                                <i class="ti ti-message-2 me-1 text-primary"></i> Mensaje Personalizado para la Línea de Vida
                            </label>
                            <textarea class="form-control @error('mensaje_usuario') is-invalid @enderror"
                                      wire:model="mensaje_usuario" rows="3"
                                      placeholder="¡Felicidades por este gran paso en tu camino espiritual! Que Dios continúe guiando cada uno de tus pasos..."></textarea>
                            <small class="text-muted d-block mt-1">Este mensaje aparecerá enmarcado de forma especial y destacada en el muro personal del usuario.</small>
                            @error('mensaje_usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- PANEL 2: MULTIMEDIA Y FOTOS --}}
        {{-- ============================================================ --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title fw-bold mb-0 text-primary d-flex align-items-center">
                    <span class="badge bg-label-primary text-white rounded-circle p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">2</span>
                    Multimedia y Fotografías
                </h5>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Foto de Portada --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Foto de Portada Principal</label>
                        <input type="file" class="form-control @error('portada') is-invalid @enderror"
                               wire:model="portada" accept="image/*">
                        <small class="text-muted d-block mb-2">Recomendado: Proporción 1:1 o 16:9, JPG, PNG o WEBP (Máx 5MB).</small>
                        @error('portada') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        @if ($portada)
                            <div class="mt-2 p-2 border rounded">
                                <p class="small fw-semibold text-success mb-1"><i class="ti ti-check me-1"></i> Nueva Portada Seleccionada:</p>
                                <img src="{{ $portada->temporaryUrl() }}" class="rounded w-100" style="max-height: 180px; object-fit: cover;">
                            </div>
                        @elseif ($portadaActual)
                            <div class="mt-2 p-2 border rounded">
                                <p class="small fw-semibold text-dark mb-1"><i class="ti ti-photo me-1"></i> Portada Actual:</p>
                                <img src="{{ tenant_asset('img/hitos/portadas/' . $portadaActual) }}" class="rounded w-100" style="max-height: 180px; object-fit: cover;">
                            </div>
                        @endif
                    </div>

                    {{-- URL de Video --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">URL de Video (YouTube o Vimeo)</label>
                        <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                               wire:model="video_url" placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted d-block mt-1">Si ingresas un enlace, se mostrará un reproductor interactivo en el hito.</small>
                        @error('video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Configuración de Fotos de Usuarios --}}
                    <div class="col-12 border-top pt-4">
                        <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                            <i class="ti ti-users-group me-2 text-primary"></i> Participación Comunitaria (Fotos de Usuarios)
                        </h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="checkPermiteFotos" wire:model="permite_fotos_usuario">
                                    <label class="form-check-label fw-semibold text-dark small" for="checkPermiteFotos">Permitir a los usuarios subir fotos personales</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Máximo de Fotos por Usuario</label>
                                <input type="number" class="form-control form-control-sm" wire:model="max_fotos_usuario" min="1" max="10" {{ !$permite_fotos_usuario ? 'disabled' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Peso Máximo por Foto (KB)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="max_peso_kb" step="512" min="512" max="10240" {{ !$permite_fotos_usuario ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Galería de Fotos Oficiales (Admin) --}}
                    <div class="col-12 border-top pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
                                <i class="ti ti-camera me-2 text-primary"></i> Galería Oficial del Evento
                            </h6>
                            <span class="badge bg-label-primary text-white">
                                <i class="ti ti-aspect-ratio me-1"></i> Formato Cuadrado (1:1)
                            </span>
                        </div>

                        {{-- Mensaje Aclaratorio --}}
                        <div class="border-start border-warning border-3 ps-3 mb-3 py-1">
                            <div class="small text-dark">
                                <strong>Recomendación de formato:</strong> Se sugiere subir fotos con proporción <strong>cuadrada 1:1</strong>. Las fotos en otras proporciones serán encuadradas y centradas automáticamente para asegurar una cuadrícula uniforme en el muro.
                            </div>
                        </div>

                        <label class="form-label small fw-semibold text-dark">Subir fotos oficiales para la galería pública</label>
                        <input type="file" class="form-control" wire:model="fotosAdmin" multiple accept="image/*">
                        <small class="text-muted d-block mt-1">Puedes seleccionar múltiples fotos simultáneamente (JPG, PNG o WEBP - Máx 5MB c/u).</small>

                        {{-- Spinner de carga de fotos --}}
                        <div wire:loading wire:target="fotosAdmin" class="mt-2 text-primary small">
                            <i class="ti ti-loader-2 ti-spin me-1"></i> Procesando y cargando fotos a la galería...
                        </div>

                        {{-- Galería de fotos existentes --}}
                        @if(!empty($fotosAdminActuales) && count($fotosAdminActuales) > 0)
                            <div class="row g-2 mt-3">
                                <p class="small fw-semibold text-dark mb-1 col-12">
                                    Fotos actuales en la galería oficial ({{ count($fotosAdminActuales) }}):
                                </p>
                                @foreach($fotosAdminActuales as $fotoA)
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 position-relative" wire:key="foto-admin-{{ $fotoA->id }}">
                                        <div class="border rounded overflow-hidden shadow-sm position-relative" style="aspect-ratio: 1/1;">
                                            <img src="{{ $fotoA->url }}" class="w-100 h-100 object-fit-cover" alt="Foto Oficial">
                                            <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 rounded-circle p-1 shadow"
                                                    wire:click="eliminarFotoAdmin({{ $fotoA->id }})" title="Eliminar foto de la galería">
                                                <i class="ti ti-x text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- PANELES 3 Y 4: EXCLUSIVOS DE LA VISTA DE EDICIÓN --}}
        {{-- ============================================================ --}}
        @if($hitoId)
            {{-- ============================================================ --}}
            {{-- PANEL 3: TIPO DE HITO Y ACTIVACIÓN / TRIGGERS --}}
            {{-- ============================================================ --}}
            <div class="card mb-4 border shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title fw-bold mb-0 text-primary d-flex align-items-center">
                        <span class="badge bg-label-primary text-white rounded-circle p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">3</span>
                        Configuración de Activación y Disparadores
                    </h5>
                </div>

                <div class="card-body p-4">
                    {{-- Caso 1: Tipo Actividad --}}
                    @if($tipoSeleccionado && ($tipoSeleccionado->requiere_actividad || $tipoSeleccionado->slug === 'actividad'))
                        <div class="mb-2">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                                <i class="ti ti-ticket me-2 text-primary"></i> Vinculación con Actividad del Sistema
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold text-dark">Seleccionar Actividad Asociada</label>
                                    <div wire:ignore id="container-select-actividad">
                                        <select x-data="{
                                            init() {
                                                let select = $(this.$refs.select);
                                                select.select2({
                                                    placeholder: '-- Seleccionar Actividad --',
                                                    allowClear: true
                                                });
                                                select.on('change', () => {
                                                    @this.set('actividad_id', select.val() || null);
                                                });
                                            }
                                        }" x-ref="select" id="actividad_id" class="select2 form-select">
                                            <option value="">-- Seleccionar Actividad --</option>
                                            @foreach($actividades as $act)
                                                @php
                                                    $fechaTxt = $act->fecha_inicio ? \Carbon\Carbon::parse($act->fecha_inicio)->format('d/m/Y') : ($act->fecha_finalizacion ? \Carbon\Carbon::parse($act->fecha_finalizacion)->format('d/m/Y') : 'Sin fecha');
                                                @endphp
                                                <option value="{{ $act->id }}" {{ (string)$act->id === (string)$actividad_id ? 'selected' : '' }}>
                                                    {{ $act->nombre }} ({{ $fechaTxt }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" id="checkReqAsistencia" wire:model="requiere_asistencia">
                                        <label class="form-check-label fw-semibold text-dark" for="checkReqAsistencia">
                                            Exigir asistencia confirmada para mostrar este hito
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- Caso 2: Tipo Automático --}}
                    @elseif($tipoSeleccionado && ($tipoSeleccionado->requiere_trigger || $tipoSeleccionado->slug === 'automatico'))
                        <div class="mb-2">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                                <i class="ti ti-cpu me-2 text-primary"></i> Configuración del Disparador Automático
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Módulo que dispara el hito</label>
                                    <div wire:ignore id="container-select-trigger-modulo">
                                        <select x-data="{
                                            init() {
                                                let select = $(this.$refs.select);
                                                select.select2({
                                                    placeholder: '-- Seleccionar Módulo --',
                                                    allowClear: true
                                                });
                                                select.on('change', () => {
                                                    @this.set('trigger_modulo', select.val() || '');
                                                });
                                            }
                                        }" x-ref="select" id="trigger_modulo" class="select2 form-select">
                                            <option value="">-- Seleccionar Módulo --</option>
                                            <option value="pasos_crecimiento" {{ $trigger_modulo === 'pasos_crecimiento' ? 'selected' : '' }}>Pasos de Crecimiento</option>
                                            <option value="tareas_consolidacion" {{ $trigger_modulo === 'tareas_consolidacion' ? 'selected' : '' }}>Tareas de Consolidación</option>
                                            <option value="escuelas" {{ $trigger_modulo === 'escuelas' ? 'selected' : '' }}>Escuelas / Académico</option>
                                            <option value="grupos" {{ $trigger_modulo === 'grupos' ? 'selected' : '' }}>Grupos Celulares</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Sub-opciones de Pasos de Crecimiento --}}
                                @if($trigger_modulo === 'pasos_crecimiento')
                                    <div class="col-md-6" wire:key="wrap-paso-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Paso de Crecimiento Requerido</label>
                                        <div wire:ignore id="container-select-paso-crecimiento" wire:key="cont-paso-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Paso --',
                                                        allowClear: true
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_paso_crecimiento_id', select.val() || null);
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_paso_crecimiento_id" class="select2 form-select">
                                                <option value="">-- Seleccionar Paso --</option>
                                                @foreach($pasosCrecimiento as $paso)
                                                    <option value="{{ $paso->id }}" {{ (string)$paso->id === (string)$trigger_paso_crecimiento_id ? 'selected' : '' }}>
                                                        {{ $paso->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" wire:key="wrap-estado-paso-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Estado de Culminación Requerido</label>
                                        <div wire:ignore id="container-select-estado-paso" wire:key="cont-estado-paso-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Estado --',
                                                        allowClear: true
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_estado_paso_id', select.val() || null);
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_estado_paso_id" class="select2 form-select">
                                                <option value="">-- Seleccionar Estado --</option>
                                                @foreach($estadosPasos as $estPaso)
                                                    <option value="{{ $estPaso->id }}" {{ (string)$estPaso->id === (string)$trigger_estado_paso_id ? 'selected' : '' }}>
                                                        {{ $estPaso->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                {{-- Sub-opciones de Tareas de Consolidación --}}
                                @if($trigger_modulo === 'tareas_consolidacion')
                                    <div class="col-md-6" wire:key="wrap-tarea-cons-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Tarea de Consolidación Requerida</label>
                                        <div wire:ignore id="container-select-tarea-cons" wire:key="cont-tarea-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Tarea --',
                                                        allowClear: true
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_tarea_consolidacion_id', select.val() || null);
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_tarea_consolidacion_id" class="select2 form-select">
                                                <option value="">-- Seleccionar Tarea --</option>
                                                @foreach($tareasConsolidacion as $tarea)
                                                    <option value="{{ $tarea->id }}" {{ (string)$tarea->id === (string)$trigger_tarea_consolidacion_id ? 'selected' : '' }}>
                                                        {{ $tarea->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" wire:key="wrap-estado-tarea-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Estado de la Tarea Requerido</label>
                                        <div wire:ignore id="container-select-estado-tarea" wire:key="cont-estado-tarea-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Estado --',
                                                        allowClear: true
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_estado_tarea_id', select.val() || null);
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_estado_tarea_id" class="select2 form-select">
                                                <option value="">-- Seleccionar Estado --</option>
                                                @foreach($estadosTareas as $estTarea)
                                                    <option value="{{ $estTarea->id }}" {{ (string)$estTarea->id === (string)$trigger_estado_tarea_id ? 'selected' : '' }}>
                                                        {{ $estTarea->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                {{-- Sub-opciones de Escuelas --}}
                                @if($trigger_modulo === 'escuelas')
                                    @php
                                        $escSelected = $trigger_escuela_id ? $escuelas->firstWhere('id', $trigger_escuela_id) : null;
                                        $esNivelesAgrupados = $escSelected && $escSelected->tipo_matricula === 'niveles_agrupados';
                                    @endphp
                                    <div class="{{ $trigger_escuela_id ? 'col-md-6' : 'col-md-12' }}" wire:key="wrap-escuela-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Escuela <span class="text-danger">*</span></label>
                                        <div wire:ignore id="container-select-escuela" wire:key="cont-escuela-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Escuela --',
                                                        allowClear: true
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_escuela_id', select.val() || null);
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_escuela_id" class="select2 form-select">
                                                <option value="">-- Seleccionar Escuela --</option>
                                                @foreach($escuelas as $esc)
                                                    <option value="{{ $esc->id }}" {{ (string)$esc->id === (string)$trigger_escuela_id ? 'selected' : '' }}>
                                                        {{ $esc->nombre }} ({{ $esc->tipo_matricula === 'niveles_agrupados' ? 'Niveles' : 'Materias' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Caso A: Escuela por Niveles Agrupados -> Muestra sólo selector de Nivel (Obligatorio) --}}
                                    @if($trigger_escuela_id && $esNivelesAgrupados)
                                        <div class="col-md-6" wire:key="wrap-nivel-{{ $trigger_escuela_id }}">
                                            <label class="form-label fw-semibold text-dark">Nivel Requerido <span class="text-danger">*</span></label>
                                            <div wire:ignore id="container-select-nivel" wire:key="cont-nivel-{{ $trigger_escuela_id }}">
                                                <select x-data="{
                                                    init() {
                                                        let select = $(this.$refs.select);
                                                        select.select2({
                                                            placeholder: '-- Seleccionar Nivel --',
                                                            allowClear: true
                                                        });
                                                        select.on('change', () => {
                                                            @this.set('trigger_nivel_id', select.val() || null);
                                                        });
                                                    }
                                                }" x-ref="select" id="trigger_nivel_id" class="select2 form-select">
                                                    <option value="">-- Seleccionar Nivel --</option>
                                                    @foreach($niveles as $niv)
                                                        <option value="{{ $niv->id }}" {{ (string)$niv->id === (string)$trigger_nivel_id ? 'selected' : '' }}>
                                                            {{ $niv->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Caso B: Escuela por Materias Independientes -> Muestra sólo selector de Materia (Obligatorio) --}}
                                    @if($trigger_escuela_id && !$esNivelesAgrupados)
                                        <div class="col-md-6" wire:key="wrap-materia-{{ $trigger_escuela_id }}">
                                            <label class="form-label fw-semibold text-dark">Materia Requerida <span class="text-danger">*</span></label>
                                            <div wire:ignore id="container-select-materia" wire:key="cont-materia-{{ $trigger_escuela_id }}">
                                                <select x-data="{
                                                    init() {
                                                        let select = $(this.$refs.select);
                                                        select.select2({
                                                            placeholder: '-- Seleccionar Materia --',
                                                            allowClear: true
                                                        });
                                                        select.on('change', () => {
                                                            @this.set('trigger_materia_id', select.val() || null);
                                                        });
                                                    }
                                                }" x-ref="select" id="trigger_materia_id" class="select2 form-select">
                                                    <option value="">-- Seleccionar Materia --</option>
                                                    @foreach($materias as $mat)
                                                        <option value="{{ $mat->id }}" {{ (string)$mat->id === (string)$trigger_materia_id ? 'selected' : '' }}>
                                                            {{ $mat->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- Sub-opciones de Grupos --}}
                                @if($trigger_modulo === 'grupos')
                                    <div class="col-md-6" wire:key="wrap-tipo-grupo-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Tipo de Grupo</label>
                                        <div wire:ignore id="container-select-tipo-grupo" wire:key="cont-tipo-grupo-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Tipo de Grupo --',
                                                        allowClear: true
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_tipo_grupo_id', select.val() || null);
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_tipo_grupo_id" class="select2 form-select">
                                                <option value="">-- Seleccionar Tipo de Grupo --</option>
                                                @foreach($tiposGrupo as $tg)
                                                    <option value="{{ $tg->id }}" {{ (string)$tg->id === (string)$trigger_tipo_grupo_id ? 'selected' : '' }}>
                                                        {{ $tg->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" wire:key="wrap-rol-grupo-{{ $trigger_modulo }}">
                                        <label class="form-label fw-semibold text-dark">Rol del Creyente en el Grupo</label>
                                        <div wire:ignore id="container-select-rol-grupo" wire:key="cont-rol-grupo-{{ $trigger_modulo }}">
                                            <select x-data="{
                                                init() {
                                                    let select = $(this.$refs.select);
                                                    select.select2({
                                                        placeholder: '-- Seleccionar Rol --',
                                                        allowClear: false
                                                    });
                                                    select.on('change', () => {
                                                        @this.set('trigger_tipo', select.val() || '');
                                                    });
                                                }
                                            }" x-ref="select" id="trigger_tipo" class="select2 form-select">
                                                <option value="asignacion_integrante" {{ $trigger_tipo === 'asignacion_integrante' ? 'selected' : '' }}>Asignado como Integrante</option>
                                                <option value="designacion_lider" {{ $trigger_tipo === 'designacion_lider' ? 'selected' : '' }}>Designado como Líder / Encargado</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    {{-- Caso 3: Reconocimiento Pastoral / Asignación Manual --}}
                    @elseif($tipoSeleccionado && $tipoSeleccionado->slug === 'manual')
                        <div class="mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark d-flex align-items-center">
                                        <i class="ti ti-award me-2 text-warning"></i> Otorgar Reconocimiento a Usuarios
                                    </h6>
                                    <small class="text-muted">Busca y selecciona las personas específicas que recibirán este reconocimiento pastoral.</small>
                                </div>
                                <span class="badge bg-label-primary text-white rounded-pill px-3 py-2">
                                    {{ count($usuariosManuales) }} {{ count($usuariosManuales) === 1 ? 'persona asignada' : 'personas asignadas' }}
                                </span>
                            </div>

                            {{-- Buscador de Usuarios Reutilizable --}}
                            <div class="mb-4 p-3 border rounded">
                                @livewire('usuarios.usuarios-para-busqueda', [
                                    'id' => 'buscador_hitos_manual',
                                    'tipoBuscador' => 'unico',
                                    'queUsuariosCargar' => 'todos',
                                    'label' => 'Buscar usuario por nombre o identificación:',
                                    'placeholder' => 'Escriba el nombre o documento...',
                                ], key('busc-manual-'.$hitoId))
                            </div>

                            {{-- Listado de Usuarios Seleccionados --}}
                            @if(!empty($usuariosManuales))
                                <div class="table-responsive border rounded">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr class="border-bottom">
                                                <th style="width: 35%;" class="fw-bold text-dark">Usuario</th>
                                                <th style="width: 25%;" class="fw-bold text-dark">Fecha de Entrega</th>
                                                <th style="width: 30%;" class="fw-bold text-dark">Dedicatoria / Nota Pastoral (Opcional)</th>
                                                <th style="width: 10%;" class="text-center fw-bold text-dark">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($usuariosManuales as $index => $uManual)
                                                <tr wire:key="user-manual-{{ $uManual['user_id'] }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if(empty($uManual['foto']) || $uManual['foto'] == "default-m.png" || $uManual['foto'] == "default-f.png")
                                                                <div class="avatar avatar-sm me-2">
                                                                    <span class="avatar-initial rounded-circle border border-2 border-white bg-info text-white"> {{ $uManual['iniciales'] ?? strtoupper(substr($uManual['nombre'] ?? 'U', 0, 2)) }} </span>
                                                                </div>
                                                            @else
                                                                <div class="avatar avatar-sm me-2">
                                                                    <img src="{{ $uManual['foto_url'] ?? tenant_asset('img/usuario/fotos/' . $uManual['foto']) }}" alt="{{ $uManual['foto'] }}" class="avatar-initial rounded-circle border border-2 border-white bg-info">
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <span class="fw-bold text-dark d-block">{{ $uManual['nombre'] }}</span>
                                                                <small class="text-muted">ID: {{ $uManual['user_id'] }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="date" class="form-control form-control-sm" wire:model.defer="usuariosManuales.{{ $index }}.fecha">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" placeholder="Ej: Por su fidelidad y servicio..." wire:model.defer="usuariosManuales.{{ $index }}.nota">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" title="Quitar reconocimiento" wire:click="eliminarUsuarioManual({{ $index }})">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 border rounded text-muted">
                                    <i class="ti ti-users-minus fs-1 d-block mb-2 text-secondary"></i>
                                    Aún no has agregado usuarios a este reconocimiento. Utiliza el buscador superior para agregar personas.
                                </div>
                            @endif
                        </div>

                    {{-- Caso 4: General (Congregacional) --}}
                    @else
                        <div class="border-start border-primary border-3 ps-3 py-2">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-info-circle text-primary fs-4 me-2"></i>
                                <div class="text-dark">
                                    Este hito es de tipo <strong>{{ $tipoSeleccionado->nombre ?? 'General' }}</strong>, por lo que se mostrará directamente a toda la congregación (según los filtros demográficos del Panel 4).
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- PANEL 4: RESTRICCIONES Y SEGMENTACIÓN DE AUDIENCIA --}}
            {{-- (Oculto automáticamente para reconocimientos manuales) --}}
            {{-- ============================================================ --}}
            @if($tipoSeleccionado && $tipoSeleccionado->slug !== 'manual')
            <div class="card mb-4 border shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title fw-bold mb-0 text-primary d-flex align-items-center">
                        <span class="badge bg-label-primary text-white rounded-circle p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">4</span>
                        Restricciones y Segmentación de Audiencia
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="border-start border-info border-3 ps-3 mb-4 py-1">
                        <div class="text-dark small">
                            <i class="ti ti-info-circle text-info me-1"></i>
                            Si dejas los campos vacíos en una categoría, el hito será <strong>visible para todos los usuarios</strong> en esa categoría.
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Sedes --}}
                        <div class="col-12">
                            <label class="form-label text-dark fw-semibold" for="sedes"><i class="ti ti-building me-1 text-primary"></i> Sedes permitidas</label>
                            <div id="container-select-sedes" wire:ignore>
                                <select x-data="{
                                    init() {
                                        let select = $(this.$refs.select);
                                        select.select2({
                                            placeholder: 'Todas las sedes (dejar vacío para todas)',
                                            allowClear: true
                                        });
                                        select.on('change', () => {
                                            @this.set('sedesSeleccionadas', select.val() || []);
                                        });
                                    }
                                }" x-ref="select" id="sedes" class="select2 form-select" multiple>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ in_array($sede->id, $sedesSeleccionadas) ? 'selected' : '' }}>
                                            {{ $sede->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tipos de Usuario --}}
                        <div class="col-12">
                            <label class="form-label text-dark fw-semibold" for="tiposUsuario"><i class="ti ti-user me-1 text-primary"></i> Tipos de usuario permitidos</label>
                            <div id="container-select-tiposUsuario" wire:ignore>
                                <select x-data="{
                                    init() {
                                        let select = $(this.$refs.select);
                                        select.select2({
                                            placeholder: 'Todos los tipos de usuario (dejar vacío para todos)',
                                            allowClear: true
                                        });
                                        select.on('change', () => {
                                            @this.set('tiposUsuariosSeleccionados', select.val() || []);
                                        });
                                    }
                                }" x-ref="select" id="tiposUsuario" class="select2 form-select" multiple>
                                    @foreach($tiposUsuario as $tu)
                                        <option value="{{ $tu->id }}" {{ in_array($tu->id, $tiposUsuariosSeleccionados) ? 'selected' : '' }}>
                                            {{ $tu->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Estados Civiles --}}
                        <div class="col-12">
                            <label class="form-label text-dark fw-semibold" for="estadosCiviles"><i class="ti ti-heart me-1 text-primary"></i> Estados civiles permitidos</label>
                            <div id="container-select-estadosCiviles" wire:ignore>
                                <select x-data="{
                                    init() {
                                        let select = $(this.$refs.select);
                                        select.select2({
                                            placeholder: 'Todos los estados civiles (dejar vacío para todos)',
                                            allowClear: true
                                        });
                                        select.on('change', () => {
                                            @this.set('estadosCivilesSeleccionados', select.val() || []);
                                        });
                                    }
                                }" x-ref="select" id="estadosCiviles" class="select2 form-select" multiple>
                                    @foreach($estadosCiviles as $ec)
                                        <option value="{{ $ec->id }}" {{ in_array($ec->id, $estadosCivilesSeleccionados) ? 'selected' : '' }}>
                                            {{ $ec->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Rangos de Edad --}}
                        <div class="col-12">
                            <label class="form-label text-dark fw-semibold" for="rangosEdad"><i class="ti ti-users me-1 text-primary"></i> Rangos de edad permitidos</label>
                            <div id="container-select-rangosEdad" wire:ignore>
                                <select x-data="{
                                    init() {
                                        let select = $(this.$refs.select);
                                        select.select2({
                                            placeholder: 'Todos los rangos de edad (dejar vacío para todos)',
                                            allowClear: true
                                        });
                                        select.on('change', () => {
                                            @this.set('rangosEdadSeleccionados', select.val() || []);
                                        });
                                    }
                                }" x-ref="select" id="rangosEdad" class="select2 form-select" multiple>
                                    @foreach($rangosEdad as $re)
                                        <option value="{{ $re->id }}" {{ in_array($re->id, $rangosEdadSeleccionados) ? 'selected' : '' }}>
                                            {{ $re->nombre }} ({{ $re->edad_minima }} - {{ $re->edad_maxima }} años)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endif

        {{-- Barra de Acciones Final --}}
        <div class="d-flex justify-content-between align-items-center py-3">
            <a href="{{ route('hitos.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Cancelar y Volver
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2">
                @if(!$hitoId)
                    <i class="ti ti-arrow-right me-1"></i> Guardar y Continuar a Triggers / Filtros
                @else
                    <i class="ti ti-device-floppy me-1"></i> Guardar Todos los Cambios
                @endif
            </button>
        </div>
    </form>

    @script
    <script>
        $wire.on('msn', (data) => {
            let info = Array.isArray(data) ? data[0] : data;
            let icono = info?.msnIcono || info?.tipo || 'info';
            let titulo = info?.msnTitulo || (icono === 'success' ? '¡Éxito!' : 'Notificación');
            let texto = info?.msnTexto || info?.mensaje || '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icono,
                    title: titulo,
                    html: texto,
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }
        });
    </script>
    @endscript
</div>
