<div>
    @php
        use App\Models\Sede;
        use App\Models\User;
    @endphp
    {{-- Encabezado y Búsqueda --}}

    <div class="row mb-3 ">
        <div class="col-lg-1 col-sm-6">
            <label for="busquedaAlumnoInputLivewire" class="  form-label mb-0">Buscar
                alumno:</label>
        </div>
        <div class="col-lg-10 col-sm-6">
            <input type="text" class="col-lg-5 col-sm-6 form-control" id="busquedaAlumnoInputLivewire"
                wire:model.live.debounce.300ms="busquedaAlumno" placeholder="Nombre, identificación o email...">
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            @if ($alumnosConEstado->isNotEmpty())
                <div class="accordion" id="accordionAlumnosCalificacionesComponente">
                    @foreach ($alumnosConEstado as $index => $estadoAcademico)
                        @if ($estadoAcademico->user)
                            {{-- Importante: Asegurarse que el usuario existe --}}
                            <div class="accordion-item" wire:key="alumno-acc-{{ $estadoAcademico->user->id }}">
                                <h2 class="accordion-header"
                                    id="headingAlumnoComponente_{{ $estadoAcademico->user->id }}">
                                    <button class="accordion-button collapsed" type="button"
                                        wire:click="cargarEstructuraCalificacion({{ $estadoAcademico->user->id }})"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapseAlumnoComponente_{{ $estadoAcademico->user->id }}"
                                        aria-expanded="false"
                                        aria-controls="collapseAlumnoComponente_{{ $estadoAcademico->user->id }}">
                                        {{-- Inicio del Contenido del Botón del Acordeón --}}
                                        <div class="container-fluid px-0">
                                            <div class="row align-items-center w-100 g-2"> {{-- g-2 para un poco de espacio entre columnas --}}

                                                {{-- Columna para Avatar y Nombre Principal --}}
                                                <div class="col-12 col-sm-6 col-md-5 d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 flex-shrink-0">
                                                        @if ($estadoAcademico->user->foto_perfil)
                                                            <img src="{{ asset('storage/' . $estadoAcademico->user->foto_perfil) }}"
                                                                alt="Avatar" class="rounded-circle">
                                                        @else
                                                            @php
                                                                $nombreCompleto = trim(
                                                                    ($estadoAcademico->user->primer_nombre ?? '') .
                                                                        ' ' .
                                                                        ($estadoAcademico->user->primer_apellido ?? ''),
                                                                );
                                                                $nombresPartes = explode(' ', $nombreCompleto);
                                                                $iniciales = !empty($nombresPartes[0])
                                                                    ? strtoupper(substr($nombresPartes[0], 0, 1))
                                                                    : 'X';
                                                                if (
                                                                    count($nombresPartes) > 1 &&
                                                                    !empty($nombresPartes[1])
                                                                ) {
                                                                    $iniciales .= strtoupper(
                                                                        substr($nombresPartes[1], 0, 1),
                                                                    );
                                                                } elseif (
                                                                    count($nombresPartes) == 1 &&
                                                                    strlen($nombresPartes[0]) > 1
                                                                ) {
                                                                    $iniciales = strtoupper(
                                                                        substr($nombresPartes[0], 0, 2),
                                                                    );
                                                                }
                                                            @endphp
                                                            <span
                                                                class="avatar-initial rounded-circle bg-label-secondary">
                                                                {{ $iniciales }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-start"> {{-- Para alinear texto a la izquierda --}}
                                                        <span class="fw-medium d-block text-truncate"
                                                            style="max-width: 200px;"
                                                            title="{{ $estadoAcademico->user->primer_nombre ?? 'Alumno' }} {{ $estadoAcademico->user->primer_apellido ?? '' }}">
                                                            {{ $estadoAcademico->user->primer_nombre ?? 'Alumno' }}
                                                            {{ $estadoAcademico->user->primer_apellido ?? '' }}
                                                        </span>
                                                        <small class="text-muted d-block">ID:
                                                            {{ $estadoAcademico->user->identificacion ?? 'N/A' }}</small>
                                                    </div>
                                                </div>

                                                {{-- Columna para Datos Adicionales (Teléfono, etc.) - Oculta en extra pequeño, visible desde sm --}}
                                                <div
                                                    class="col-12 col-sm-6 col-md-4 mt-2 mt-sm-0 text-sm-start text-md-center">
                                                    {{-- Aquí puedes añadir los nuevos datos. Por ahora, placeholders. --}}
                                                    <div>
                                                        <small class="text-muted">Teléfono: <span
                                                                class="text-body">{{ $estadoAcademico->user->telefono_movil ? $estadoAcademico->user->telefono_movil : '' }}</span></small>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Sede:
                                                            @php
                                                                $sede = Sede::find($estadoAcademico->user->sede_id);
                                                            @endphp
                                                            <span
                                                                class="text-body">{{ $sede->id ? $sede->nombre : 'N/A' }}</span></small>
                                                    </div>
                                                </div>

                                                {{-- Columna para Promedio General - Siempre al final en pantallas grandes, se apila en móviles --}}
                                                <div class="col-12 col-md-3 mt-2 mt-md-0 text-md-end">
                                                    <small class="text-muted d-block d-md-inline">Promedio
                                                        general:</small>
                                                    <strong class="text-dark"
                                                        wire:key="pg-{{ $estadoAcademico->user->id }}">
                                                        {{ isset($promedioGeneralMateria[$estadoAcademico->user->id]) ? number_format($promedioGeneralMateria[$estadoAcademico->user->id], 2) : '--.--' }}
                                                    </strong>
                                                </div>

                                            </div>
                                        </div>
                                        {{-- Fin del Contenido del Botón del Acordeón --}}
                                    </button>
                                </h2>
                                <div id="collapseAlumnoComponente_{{ $estadoAcademico->user->id }}"
                                    class="accordion-collapse collapse"
                                    aria-labelledby="headingAlumnoComponente_{{ $estadoAcademico->user->id }}"
                                    data-bs-parent="#accordionAlumnosCalificacionesComponente" wire:ignore.self>
                                    {{-- wire:ignore.self es importante aquí --}}
                                    <div class="accordion-body">
                                        {{-- Indicador de carga mientras se obtienen los ítems --}}
                                        <div wire:loading
                                            wire:target="cargarEstructuraCalificacion({{ $estadoAcademico->user->id }})">
                                            <div class="d-flex justify-content-center my-3">
                                                <div class="spinner-border text-primary spinner-border-sm"
                                                    role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <span class="ms-2 text-muted">Cargando ítems de calificación...</span>
                                            </div>
                                        </div>

                                        {{-- Contenido cargado (solo si es el alumno activo y los datos están listos) --}}
                                        <div class="w-100" wire:loading.remove
                                            wire:target="cargarEstructuraCalificacion({{ $estadoAcademico->user->id }})">
                                            @if (
                                                $alumnoActivoIdParaCalificaciones === $estadoAcademico->user->id &&
                                                    isset($estructuraItemsPorAlumno[$estadoAcademico->user->id]) &&
                                                    !empty($estructuraItemsPorAlumno[$estadoAcademico->user->id]))
                                                @php
                                                    $estructuraDelAlumnoActual =
                                                        $estructuraItemsPorAlumno[$estadoAcademico->user->id];
                                                    $userIdActual = $estadoAcademico->user->id;
                                                @endphp

                                                {{-- Pestañas (Tabs) para cada Corte --}}
                                                <ul class="nav nav-pills nav-fill "
                                                    id="cortesTabsAlumno{{ $userIdActual }}" role="tablist">
                                                    @forelse ($estructuraDelAlumnoActual as $cortePeriodoId => $dataCorte)
                                                        <li class="col-12 col-md-3 mb-2 nav-item border rounded mx-2"
                                                            role="presentation"
                                                            wire:key="nav-item-corte-{{ $userIdActual }}-{{ $cortePeriodoId }}">
                                                            <button
                                                                class="nav-link @if ($this->isTabActive($userIdActual, $cortePeriodoId, $loop->first)) active @endif"
                                                                id="tab-corte-{{ $userIdActual }}-{{ $cortePeriodoId }}"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#content-corte-{{ $userIdActual }}-{{ $cortePeriodoId }}"
                                                                type="button" role="tab"
                                                                aria-controls="content-corte-{{ $userIdActual }}-{{ $cortePeriodoId }}"
                                                                aria-selected="{{ $this->isTabActive($userIdActual, $cortePeriodoId, $loop->first) ? 'true' : 'false' }}"
                                                                wire:click="setActiveTab({{ $userIdActual }}, {{ $cortePeriodoId }})">
                                                               {{ $dataCorte['corte_nombre_original'] }} ({{ number_format($notasCalculadasPorCorte[$userIdActual][$cortePeriodoId] ?? 0, 2) }})
                                                                 {{-- Muestra Fecha Fin del Corte --}}
                                                                 @php $infoCorteActual = $cortesPeriodoInfo[$cortePeriodoId] ?? null; @endphp
                                                                 @if($infoCorteActual && $infoCorteActual['fecha_fin'])
                                                                     <br><small class="fw-normal text-white" style="font-size: 0.7rem;">(Fin: {{ $infoCorteActual['fecha_fin']->format('d/m/y') }})</small>
                                                                 @endif
                                                            </button>
                                                        </li>
                                                    @empty
                                                        <li class="nav-item text-muted p-3">No hay cortes definidos para
                                                            este periodo.</li>
                                                    @endforelse
                                                </ul>

                                                {{-- Contenido de las Pestañas --}}
                                                <div style="box-shadow:none" class="tab-content p-3 mt-4"
                                                    id="cortesTabContentAlumno{{ $userIdActual }}">
                                                    @forelse ($estructuraDelAlumnoActual as $cortePeriodoId => $dataCorte)
                                                        <div class="tab-pane fade @if ($this->isTabActive($userIdActual, $cortePeriodoId, $loop->first)) show active @endif"
                                                            id="content-corte-{{ $userIdActual }}-{{ $cortePeriodoId }}"
                                                            role="tabpanel"
                                                            aria-labelledby="tab-corte-{{ $userIdActual }}-{{ $cortePeriodoId }}"
                                                            wire:key="tab-pane-{{ $userIdActual }}-{{ $cortePeriodoId }}">

                                                            
                                                                
                                                                <div class="row g-3">
                                                                   @if (!empty($dataCorte['itemInstancias']))
    
                                                                            {{-- Muestra la nota calculada para el corte --}}
                                                                            <div>
                                                                            {{-- =============================================== --}}
                                                                            {{-- === INICIO: CÓDIGO NUEVO DEL RELOJ CONTADOR === --}}
                                                                            {{-- =============================================== --}}
                                                                            @php
                                                                                // Obtenemos la información del corte actual que ya cargamos en el componente
                                                                                $infoCorte = $cortesPeriodoInfo[$cortePeriodoId] ?? null;
                                                                                $fechaFin = ($infoCorte && $infoCorte['fecha_fin']) ? $infoCorte['fecha_fin'] : null;
                                                                                
                                                                                // Obtenemos la fecha y hora actual
                                                                                $ahora = \Carbon\Carbon::now();
                                                                            @endphp

                                                                            {{-- 
                                                                                Condiciones para mostrar la alerta:
                                                                                1. Debe existir una fecha de fin ($fechaFin).
                                                                                2. La fecha actual ($ahora) debe ser ANTERIOR a la fecha de fin (el plazo no debe estar vencido).
                                                                                3. La diferencia en días entre ahora y la fecha fin debe ser menor a 15 (0-14 días).
                                                                            --}}
                                                                            @if ($fechaFin && $ahora->lt($fechaFin) && $ahora->diffInDays($fechaFin) < 15)
                                                                                @php
                                                                                    // Calculamos la diferencia exacta
                                                                                    $diff = $ahora->diff($fechaFin);
                                                                                @endphp
                                                                                
                                                                                {{-- Esta es la alerta que verá el maestro --}}
                                                                                <div class="alert alert-warning py-2 px-3 small mb-3" role="alert">
                                                                                    <i class="mdi mdi-clock-alert-outline me-1 align-middle"></i>
                                                                                    <strong class="align-middle">¡Plazo por vencer!</strong> Quedan:
                                                                                    
                                                                                    {{-- Usamos %a para los días totales restantes --}}
                                                                                    <strong class="align-middle">{{ $diff->format('%a') }}</strong> días, 
                                                                                    <strong class="align-middle">{{ $diff->h }}</strong> horas y 
                                                                                    <strong class="align-middle">{{ $diff->i }}</strong> minutos.
                                                                                </div>
                                                                            @endif
                                                                            {{-- =============================================== --}}
                                                                            {{-- === FIN: CÓDIGO NUEVO DEL RELOJ CONTADOR     === --}}
                                                                            {{-- =============================================== --}}
                                                                                <span class="fw-semibold text-black">Nota del corte:</span>
                                                                                <span wire:key="nc-detail-{{ $userIdActual }}-{{ $cortePeriodoId }}">
                                                                                    {{ isset($notasCalculadasPorCorte[$userIdActual][$cortePeriodoId]) ? number_format($notasCalculadasPorCorte[$userIdActual][$cortePeriodoId], 2) : '0.00' }}
                                                                                </span>
                                                                            </div>

                                                                            {{-- Contenedor para las tarjetas de los ítems --}}
                                                                            <div class="row g-3 mt-1">
                                                                                
                                                                                {{-- Bucle que recorre cada ítem de calificación del corte --}}
                                                                                @foreach ($dataCorte['itemInstancias'] as $item)
                                                                                @php
                                                                                    $itemId = $item->id;
                                                                                    // ----> INICIO LÓGICA PARA DESHABILITAR INPUT <----
                                                                                    $infoCorteParaItem = $cortesPeriodoInfo[$cortePeriodoId] ?? null;
                                                                                    $fechaFinCorteParaItem = $infoCorteParaItem ? $infoCorteParaItem['fecha_fin'] : null;
                                                                                    $plazoVencidoParaItem = $fechaFinCorteParaItem && $fechaActual->gt($fechaFinCorteParaItem);
                                                                                    $isDisabled = $plazoVencidoParaItem && !$puedeCalificarSinFecha;
                                                                                @endphp
                                                                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3" wire:key="item-calif-{{ $userIdActual }}-{{ $item->id }}">
                                                                                        <div class="border rounded card h-100">
                                                                                            <div class="card-body small d-flex flex-column">
                                                                                                
                                                                                                {{-- Parte superior: Nombre y porcentaje del ítem --}}
                                                                                                <div class="flex-grow-1">
                                                                                                    <p class="fw-semibold text-truncate mb-1" title="{{ $item->nombre }}">
                                                                                                        {{ $item->nombre }}
                                                                                                    </p>
                                                                                                    <p class="text-muted x-small mb-2">
                                                                                                        ({{ number_format($item->porcentaje, 1) }}%)
                                                                                                    </p>
                                                                                                </div>
                                                                                                
                                                                                                {{-- Parte media: Input para la nota y el indicador de carga --}}
                                                                                                <div class="d-flex align-items-center mb-2">
                                                                                                    <input type="number" step="0.01" min="{{ $configuracion->nota_minima ?? 0 }}" max="{{ $configuracion->nota_maxima ?? 5 }}"
                                                                                                            class="form-control form-control-sm text-center @error('notas.'.$userIdActual.'.'.$itemId) is-invalid @enderror"
                                                                                                            placeholder="-"
                                                                                                            wire:model.blur="notas.{{ $userIdActual }}.{{ $itemId }}"
                                                                                                            aria-label="Nota para {{ $item->nombre }}"
                                                                                                            {{-- ----> APLICA EL DISABLED <---- --}}
                                                                                                            @if($isDisabled) disabled title="Plazo vencido ({{ $fechaFinCorteParaItem->format('d/m/y') }})" @endif
                                                                                                    >
                                                                                                    @error('notas.'.$userIdActual.'.'.$itemId)
                                                                                                        <div class="invalid-feedback d-block x-small text-center">{{ $message }}</div>
                                                                                                    @enderror

                                                                                                  
                                                                                                </div>

                                                                                                {{-- Muestra errores de validación para este input específico --}}
                                                                                                @if ($errors->has("notas.{$userIdActual}.{$item->id}"))
                                                                                                    <div class="invalid-feedback d-block x-small">
                                                                                                        {{ $errors->first("notas.{$userIdActual}.{$item->id}") }}
                                                                                                    </div>
                                                                                                @endif
                                                                                                
                                                                                                {{-- Parte inferior: Botones de acción --}}
                                                                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                                                                    {{-- Botón "Ver Respuesta": Solo aparece si el alumno ha enviado una respuesta --}}

                                                                                                    @if(!$plazoVencidoParaItem || $puedeCalificarSinFecha)
                                                                                                    <div>
                                                                                                        @if ($item->respuestaDelAlumno)
                                                                                                            <button 
                                                                                                                wire:click="verRespuesta({{ $userIdActual }}, {{ $item->id }})"
                                                                                                                class="btn btn-sm btn-outline-secondary waves-effect waves-light">
                                                                                                                <i class="mdi mdi-eye-outline me-1"></i>
                                                                                                                Respuesta
                                                                                                            </button>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                    
                                                                                                    {{-- Botón "Calificar": Para guardar la nota manualmente (útil si se desactiva el autoguardado) --}}
                                                                                                    <div>
                                                                                                        <button type="button"
                                                                                                            class="btn btn-sm btn-primary waves-effect waves-light"
                                                                                                            wire:click="guardarCalificacionCompleta({{ $userIdActual }}, {{ $item->id }})"
                                                                                                            wire:loading.attr="disabled"
                                                                                                            wire:target="guardarCalificacionCompleta({{ $userIdActual }}, {{ $item->id }})">
                                                                                                            {{-- El spinner reemplaza el ícono y texto mientras se guarda --}}
                                                                                                            <span wire:loading.remove wire:target="guardarCalificacionCompleta({{ $userIdActual }}, {{ $item->id }})">
                                                                                                              
                                                                                                                Calificar 
                                                                                                            </span>
                                                                                                           
                                                                                                        </button>
                                                                                                    </div>
                                                                                                    @else
                                                                                                    <div class="text-center">
                                                                                                        {{-- Botón Ver/Añadir Observación --}}
                                                                                                        <button type="button" wire:click="verRespuesta({{ $userIdActual }}, {{ $itemId }})"
                                                                                                                class="btn btn-xs @if($item->respuestaDelAlumno && $item->respuestaDelAlumno->observaciones_maestro) btn-info @else btn-outline-secondary @endif"
                                                                                                                title="{{ $item->respuestaDelAlumno && $item->respuestaDelAlumno->observaciones_maestro ? 'Ver/Editar Observación' : 'Añadir Observación' }}">
                                                                                                            <i class="ti @if($item->respuestaDelAlumno && ($item->respuestaDelAlumno->respuesta_alumno || $item->respuestaDelAlumno->enlace_documento_alumno)) ti-eye @else ti-message-plus @endif"></i>
                                                                                                        </button>
                                                                                                        {{-- El botón "Calificar" individual puede ser redundante con el blur --}}
                                                                                                    </div>
                                                                                                    @endif
                                                                                                </div>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            {{-- Mensaje que se muestra si el corte no tiene ningún ítem de calificación asignado --}}
                                                                            <p class="text-muted text-center fst-italic py-3">No hay ítems calificables definidos para este corte.</p>
                                                                        @endif
                                                                </div>
                                                          
                                                        </div>
                                                    @empty
                                                        {{-- Este bloque no debería alcanzarse si el bucle de pestañas ya maneja la lista vacía --}}
                                                    @endforelse
                                                </div>
                                            @elseif($alumnoActivoIdParaCalificaciones === $estadoAcademico->user->id)
                                                {{-- Se intentó cargar pero no hay datos (ej. no hay cortes en el periodo) --}}
                                                <p class="text-muted text-center fst-italic py-3">No se encontraron
                                                    cortes o ítems de calificación para este alumno.</p>
                                            @endif
                                            {{-- Fin del contenido cargado --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center p-4">
                    <i class="mdi mdi-account-search-outline mdi-48px text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron alumnos</h5>
                    <p class="text-muted mb-0">
                        @if (!empty($busquedaAlumno))
                            No hay alumnos que coincidan con "{{ $busquedaAlumno }}" en esta clase.
                        @else
                            No hay alumnos matriculados en esta clase.
                        @endif
                    </p>
                </div>
            @endif
        </div>

    </div>

     {{-- ========================================================== --}}
    {{-- === INICIO: CÓDIGO NUEVO PARA EL MODAL "VER RESPUESTA" === --}}
    {{-- ========================================================== --}}
    @if ($showRespuestaModal && $respuestaSeleccionada)
        <div class="modal fade show" style="display: block;" tabindex="-1" >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Respuesta de: 
                            <span class="text-primary">{{ $respuestaSeleccionada->alumno->nombre(3) }}</span>
                        </h5>
                        <button wire:click="$set('showRespuestaModal', false)" type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Actividad: <span class="fw-normal">{{ $respuestaSeleccionada->itemCalificado->nombre }}</span></h6>
                        <hr>
                        
                        {{-- Sección de la respuesta del alumno (sin cambios) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Respuesta Escrita del Alumno:</label>
                            <div class="p-3 bg-light rounded border">
                                <p class="mb-0">{{ $respuestaSeleccionada->respuesta_alumno ?? 'El alumno no dejó una respuesta escrita.' }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Archivo Adjunto del Alumno:</label>
                            @if ($respuestaSeleccionada->enlace_documento_alumno)
                                <a href="{{ $respuestaSeleccionada->archivo_url }}" target="_blank" class="btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-download-outline me-1"></i> Descargar Archivo
                                </a>
                            @else
                                <p class="text-muted">El alumno no adjuntó ningún archivo.</p>
                            @endif
                        </div>
                        <hr>

                        {{-- --- INICIO: SECCIÓN NUEVA PARA EL MAESTRO --- --}}
                        <div class="mb-3">
                            <label for="observacionMaestroText" class="form-label fw-semibold">Feedback u Observaciones (Maestro):</label>
                            <textarea 
                                id="observacionMaestroText"
                                wire:model="observacionMaestro" 
                                class="form-control" 
                                rows="4" 
                                placeholder="Escribe aquí tu retroalimentación para el alumno..."></textarea>
                            @error('observacionMaestro') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        {{-- --- FIN: SECCIÓN NUEVA PARA EL MAESTRO --- --}}

                    </div>
                    <div class="modal-footer">
                        <button wire:click="$set('showRespuestaModal', false)" type="button" class="btn btn-outline-secondary rounded-pill">Cerrar</button>
                        
                        {{-- --- BOTÓN NUEVO PARA GUARDAR LA OBSERVACIÓN --- --}}
                        <button wire:click.prevent="guardarObservacion" type="button" class="btn btn-primary rounded-pill">
                            <span wire:loading wire:target="guardarObservacion" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Guardar Observación
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
    {{-- ========================================================== --}}
    {{-- === FIN: CÓDIGO NUEVO PARA EL MODAL "VER RESPUESTA"    === --}}
    {{-- ========================================================== --}}


    {{-- Dentro de calificacion-multiple-alumnos.blade.php --}}
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                // Evento para mostrar mensaje de ÉXITO con SweetAlert
                Livewire.on('mostrarExitoConSweetAlert', (event) => {
                    const detail = Array.isArray(event) ? event[0] :
                        event; // Livewire 3 puede pasar el evento en un array
                    Swal.fire({
                        icon: 'success',
                        title: detail.titulo || '¡Realizado!', // Título del modal
                        text: detail.texto, // Texto del cuerpo del modal
                        timer: detail.timer || 2500, // Duración antes de que se cierre solo (opcional)
                        showConfirmButton: detail.showConfirmButton === undefined ? false : detail
                            .showConfirmButton, // Mostrar botón de confirmación (por defecto no)
                        // Estilos para un modal centrado (por defecto SweetAlert es centrado)
                        // No necesitas 'toast: true' ni 'position: top-end' para un modal centrado
                    });
                });

                // Evento para mostrar mensaje de ERROR con SweetAlert
                Livewire.on('mostrarError', (event) => {
                    const message = Array.isArray(event) ? (event[0].texto || event[0].message ||
                        'Ocurrió un error') : (event.texto || event.message || 'Ocurrió un error');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        // showConfirmButton: true, // Puedes querer que el usuario confirme que vio el error
                    });
                });

                // Evento para mostrar mensaje de ALERTA (warning) con SweetAlert
                Livewire.on('mostrarAlerta', (event) => {
                    const message = Array.isArray(event) ? (event[0].texto || event[0].message ||
                        'Atención requerida') : (event.texto || event.message || 'Atención requerida');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: message,
                        // showConfirmButton: true,
                    });
                });

                // Tu lógica existente para Offcanvas (si la necesitas en esta misma vista)
                // Si este script es SOLO para esta vista de calificación, puedes omitir la parte del Offcanvas
                // si no se usa aquí.
                Livewire.on('abrirOffcanvas', (event) => {
                    const detail = Array.isArray(event) ? event[0] : event;
                    const nombreOffCanvas = detail.nombreModal;
                    const offcanvasElement = document.getElementById(nombreOffCanvas);
                    if (!offcanvasElement) return;

                    let backdrop = document.querySelector('.offcanvas-backdrop.show');
                    if (!backdrop) {
                        backdrop = document.createElement('div');
                        backdrop.className = 'offcanvas-backdrop fade';
                        document.body.appendChild(backdrop);
                        void backdrop.offsetWidth; // Forzar reflow para la transición
                        backdrop.classList.add('show');
                    }

                    var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement, {
                        backdrop: true
                    });
                    offcanvas.show();

                    const backdropToRemove = backdrop;

                    function handleHidden() {
                        if (backdropToRemove && backdropToRemove.parentNode) {
                            backdropToRemove.remove();
                        }
                        offcanvasElement.removeEventListener('hidden.bs.offcanvas', handleHidden);
                    }
                    offcanvasElement.addEventListener('hidden.bs.offcanvas', handleHidden);
                });

                Livewire.on('cerrarOffcanvas', (event) => {
                    const detail = Array.isArray(event) ? event[0] : event;
                    const nombreOffCanvas = detail.nombreModal;
                    const offcanvasElement = document.getElementById(nombreOffCanvas);
                    if (offcanvasElement) {
                        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                        if (bsOffcanvas) {
                            bsOffcanvas.hide();
                        }
                    }
                });
            });
        </script>
    @endpush
</div>
