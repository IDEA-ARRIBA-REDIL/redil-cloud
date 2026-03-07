<div>
    
    <h4 class=" mb-1 fw-semibold text-primary mb-10">{{ $curso->nombre }}</h4>

    @if($modulos->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-circle-plus display-3 text-black"></i>
                </div>
                <h5 class="text-black">Comienza creando tu primer módulo</h5>                
            </div>
        </div>
    @else
        <div class="listadoModulos accordion accordion-bordered" id="accordionModulos">
            @foreach($modulos as $modulo)
                <div wire:key="modulo-{{ $modulo->id }}" wire:ignore.self class="accordion-item card shadow-none border mb-3" data-modulo-id="{{ $modulo->id }}">
                    <h2 class="accordion-header d-flex align-items-center" id="heading{{ $modulo->id }}">
                        <i class="ti ti-grip-vertical drag-handle ms-5 cursor-move text-black" style="font-size: 1.2rem;"></i>
                        <button class="accordion-button collapsed border-0 shadow-none bg-transparent py-3" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $modulo->id }}" 
                                aria-expanded="false" 
                                aria-controls="collapse{{ $modulo->id }}"
                                wire:ignore.self
                                style="padding-right: 0;">
                            <style>
                                #heading{{ $modulo->id }} .accordion-button::after { display: none !important; }
                            </style>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-primary">{{ $modulo->nombre }} </span>
                            </div>
                        </button>
                        <div class="ms-auto pe-2 pb-2">
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0);" wire:click="editarModulo({{ $modulo->id }})"><i class="ti ti-edit me-1"></i> Editar</a></li>
                                    <hr class="dropdown-divider">
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion('modulo', {{ $modulo->id }})"><i class="ti ti-trash me-1"></i> Eliminar</a></li>
                                </ul>
                            </div>
                        </div>
                    </h2>
                    <div id="collapse{{ $modulo->id }}" 
                         class="accordion-collapse collapse" 
                         aria-labelledby="heading{{ $modulo->id }}" 
                         data-bs-parent="#accordionModulos"
                         wire:ignore.self>
                        <div class="accordion-body bg-lighter pt-2">
                            <div class="p-2 mb-2">
                                <small class="text-black d-block">{{ $modulo->descripcion ?: 'Sin descripción' }}</small>
                            </div> 
                            <!-- Listado de Ítems (Lecciones/Evaluaciones) -->
                            <div class="listadoItems mt-3 pt-3" data-modulo-id="{{ $modulo->id }}" id="accordionItems{{ $modulo->id }}">
                                @forelse($modulo->items as $item)
                                    <div wire:key="item-{{ $item->id }}" class="card mb-3 border shadow-none" data-item-id="{{ $item->id }}">
                                        <div class="card-body p-2" 
                                             x-data="{ 
                                                 tipoContenido: '{{ $item->tipo->codigo === 'lectura' ? 'texto' : ($item->tipo->codigo === 'recurso' ? 'archivo' : $item->tipo->codigo) }}',
                                                 modo: '{{ $item->itemable->video_url || $item->itemable->contenido_html || $item->itemable->archivo_path || $item->itemable->iframe_code ? 'visualizar' : 'editar' }}'
                                             }"
                                             @cerrar-visualizacion-{{ $item->itemable->id }}.window="modo = 'editar'">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <i class="ti ti-grip-vertical drag-handle-item me-2 text-muted cursor-move"></i>
                                                    <i class="{{ $item->tipo->icono }} me-2 text-secondary"></i>
                                                    
                                                    @if($itemEditandoId === $item->id)
                                                        <div class="d-flex align-items-center flex-grow-1">
                                                            <input type="text" wire:model="nuevoTituloItem" class="form-control form-control-sm me-2" autofocus wire:keydown.enter="guardarNombreItem" wire:keydown.escape="cancelarEdicionItem">
                                                            <button wire:click="guardarNombreItem" class="btn btn-sm btn-icon text-primary me-1">
                                                                <i class="ti ti-check"></i>
                                                            </button>
                                                            <button wire:click="cancelarEdicionItem" class="btn btn-sm btn-icon text-danger">
                                                                <i class="ti ti-x"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="fw-semibold cursor-pointer text-black" wire:click="editarNombreItem({{ $item->id }})">{{ $item->titulo }}</span>
                                                        <i class="ti ti-edit ms-2 text-muted cursor-pointer" style="font-size: 0.8rem;" wire:click="editarNombreItem({{ $item->id }})"></i>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    
                                                    <button class="btn btn-sm btn-icon text-muted me-1" data-bs-toggle="collapse" data-bs-target="#itemContent{{ $item->id }}" aria-expanded="false" wire:ignore.self>
                                                        <i class="ti ti-chevron-down"></i>
                                                    </button>
                                                    @if($item->tipo->categoria === 'leccion')
                                                        <button x-show="modo === 'visualizar'" 
                                                                @click="modo = 'editar'; 
                                                                        const collEl = document.getElementById('itemContent{{ $item->id }}');
                                                                        const bsColl = bootstrap.Collapse.getInstance(collEl) || new bootstrap.Collapse(collEl, {toggle: false});
                                                                        bsColl.show();" 
                                                                class="btn btn-sm btn-icon btn-text-primary rounded-pill me-1" 
                                                                title="Editar contenido">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                    @endif
                                                    <button onclick="confirmarEliminacion('item', {{ $item->id }})" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Eliminar">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Contenido del Ítem (Lección) -->
                                            <div id="itemContent{{ $item->id }}" class="accordion-collapse collapse mt-3  pt-3" 
                                                 data-bs-parent="#accordionItems{{ $modulo->id }}"
                                                 wire:ignore.self>
                                                @if($item->tipo->categoria === 'leccion')
                                                    <!-- Contenido Dinámico de Lección -->

                                                    <!-- Video -->
                                                    <div x-show="tipoContenido === 'video'" x-cloak>
                                                        <!-- Vista Previa Video -->
                                                        <div x-show="modo === 'visualizar'" class="mb-3">
                                                            <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden shadow-sm p-5" style="background-color: #000;">
                                                                @if($item->itemable->video_plataforma === 'youtube')
                                                                    <iframe src="https://www.youtube.com/embed/{{ $item->itemable->video_id }}" allowfullscreen></iframe>
                                                                @elseif($item->itemable->video_plataforma === 'vimeo')
                                                                    <iframe src="https://player.vimeo.com/video/{{ $item->itemable->video_id }}" allowfullscreen></iframe>
                                                                @else
                                                                    <div class="d-flex align-items-center justify-content-center text-white h-100">
                                                                        <a href="{{ $item->itemable->video_url }}" target="_blank" class="text-white text-decoration-none">
                                                                            <i class="ti ti-external-link me-2"></i> Ver video externo
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            
                                                        </div>

                                                        <!-- Edición Video -->
                                                        <div x-show="modo === 'editar'">
                                                            <div class="mb-3">
                                                                <label class="form-label">URL del Video (YouTube o Vimeo)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="ti ti-brand-youtube"></i></span>
                                                                    <input type="text" class="form-control" placeholder="https://www.youtube.com/watch?v=..." x-ref="videoUrl{{ $item->id }}" value="{{ $item->itemable->video_url }}">
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <!-- Texto (Lectura) -->
                                                    <div x-show="tipoContenido === 'texto'" x-cloak>
                                                        <!-- Esta sección principal solo contiene el botón; el contenido se renderiza genéricamente al final de la lección -->
                                                    </div>

                                                    <!-- Archivo (Recurso) -->
                                                    <div x-show="tipoContenido === 'archivo'" x-cloak>
                                                         <!-- Vista Previa Archivo -->
                                                         <div x-show="modo === 'visualizar'" class="mb-3">
                                                                <div class="ratio ratio-16x9 border rounded overflow-hidden">
                                                                    @if($item->itemable->archivo_path)
                                                                        <iframe src="{{ route('cursos.recurso.preview', $item->itemable->id) }}?t={{ $item->itemable->updated_at?->timestamp }}" 
                                                                                class="w-100 h-100" style="border: none;" allowfullscreen title="Visor de Recursos"></iframe>
                                                                    @endif
                                                                </div>
                                                           
                                                         </div>

                                                        <!-- Edición Archivo -->
                                                        <div x-show="modo === 'editar'">
                                                            <div class="mb-3 text-center p-4 border-dashed rounded bg-light">
                                                                <i class="ti ti-cloud-upload text-primary mb-2" style="font-size: 3.5rem;"></i>
                                                                <p class="mb-2 fw-semibold">Sube un archivo PDF, imagen o PowerPoint</p>
                                                                <small class="text-muted d-block mb-3">Máximo 10MB</small>
                                                                
                                                                <input type="file" wire:model="archivo" class="form-control mt-2" id="file{{ $item->id }}">
                                                                
                                                                <div wire:loading wire:target="archivo" class="mt-2">
                                                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                                    <span class="ms-1">Cargando...</span>
                                                                </div>

                                                                @if($archivo)
                                                                    <div class="mt-3">
                                                                        <div class="alert alert-info d-flex align-items-center mb-0">
                                                                            <i class="ti ti-info-circle me-2"></i>
                                                                            <div>Archivo listo. Use el botón <strong>Guardar</strong> de abajo para finalizar.</div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            

                                                        </div>
                                                    </div>

                                                    <!-- Embebido (Iframe) -->
                                                    <div x-show="tipoContenido === 'iframe'" x-cloak>
                                                        <!-- Vista Previa Iframe -->
                                                        <div x-show="modo === 'visualizar'" class="mb-3">
                                                            @if($item->itemable->iframe_code)
                                                                <div class="rounded overflow-hidden shadow-sm border mb-3">
                                                                    {!! $item->itemable->iframe_code !!}
                                                                </div>
                                                            @else
                                                                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm border mb-3 bg-dark">
                                                                    <div class="d-flex align-items-center justify-content-center text-white h-100">
                                                                        <span class="text-muted">Sin código embebido</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            
                                                        </div>

                                                        <!-- Edición Iframe -->
                                                        <div x-show="modo === 'editar'">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold"><i class="ti ti-code me-1"></i> Código Embebido (Iframe)</label>
                                                                <textarea class="form-control font-monospace" rows="6" 
                                                                          placeholder="Pegue aquí el código <iframe> proporcionado por Canva, Genially, etc."
                                                                          x-ref="iframeCode{{ $item->id }}">{{ $item->itemable->iframe_code }}</textarea>
                                                                <div class="form-text mt-2">
                                                                    <i class="ti ti-info-circle me-1"></i> Use este campo para insertar contenido de Canva, YouTube, Genially, etc.
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-start gap-2">
                                                                <!-- El botón de guardado se movió a la sección de texto para unificarlo -->
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TEXTO COMÚN PARA TODAS LAS LECCIONES -->
                                                    <div class="mt-1">
                                                        <!-- Vista Previa Texto -->
                                                        <div x-show="modo === 'visualizar'" class="mb-3">
                                                            @if($item->itemable->contenido_html)
                                                                <div class="p-3 border rounded bg-white mb-3 text-black ql-editor">
                                                                    {!! $item->itemable->contenido_html !!}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Edición Texto -->
                                                        <div x-show="modo === 'editar'">
                                                            <div class="mb-3" wire:ignore>
                                                                <label class="form-label fw-bold"><i class="ti ti-align-left me-1"></i> Contenido de texto</label>
                                                                <div id="editor{{ $item->id }}" style="height: 200px; background: white;">
                                                                    {!! $item->itemable->contenido_html !!}
                                                                </div>
                                                                <div class="form-text mt-2" x-show="tipoContenido !== 'texto'">
                                                                    <i class="ti ti-info-circle me-1"></i> Opcional: Puede agregar texto para complementar el recurso multimedia.
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-start gap-2">                                                        
                                                                @if($item->tipo->codigo === 'lectura')
                                                                    <!-- Botón para Lectura (Solo texto) -->
                                                                    <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                                            @click="$wire.guardarTextoLeccion({{ $item->itemable->id }}, document.querySelector('#editor{{ $item->id }} .ql-editor').innerHTML).then(() => { modo = 'visualizar' })">
                                                                        <i class="ti ti-device-floppy me-1"></i> Guardar
                                                                    </button>
                                                                @elseif($item->tipo->codigo === 'recurso')
                                                                    <!-- Botón para Recurso/Archivo (Archivo + Texto) -->
                                                                    <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                                            @click="$wire.guardarArchivoYTextoLeccion({{ $item->itemable->id }}, document.querySelector('#editor{{ $item->id }} .ql-editor').innerHTML).then(() => { modo = 'visualizar' })">
                                                                        <i class="ti ti-device-floppy me-1"></i> Guardar
                                                                    </button>
                                                                @elseif($item->tipo->codigo === 'video')
                                                                    <!-- Botón para Video (Link + Texto) -->
                                                                    <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                                            @click="$wire.guardarVideoYTextoLeccion({{ $item->itemable->id }}, $refs.videoUrl{{ $item->id }}.value, document.querySelector('#editor{{ $item->id }} .ql-editor').innerHTML).then((res) => { if(res !== false) modo = 'visualizar' })">
                                                                        <i class="ti ti-device-floppy me-1"></i> Guardar
                                                                    </button>
                                                                @elseif($item->tipo->codigo === 'iframe')
                                                                    <!-- Botón para Iframe (Código + Texto) -->
                                                                    <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                                                            @click="$wire.guardarIframeYTextoLeccion({{ $item->itemable->id }}, $refs.iframeCode{{ $item->id }}.value, document.querySelector('#editor{{ $item->id }} .ql-editor').innerHTML).then((res) => { if(res !== false) modo = 'visualizar' })">
                                                                        <i class="ti ti-device-floppy me-1"></i> Guardar
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Configuración de Evaluación -->
                                                    <div x-data="{ configurando: false }">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">                                                            
                                                            <button @click="configurando = !configurando" class="btn btn-sm btn-label-secondary rounded-pill">
                                                                <i class="ti" :class="configurando ? 'ti-chevron-up' : 'ti-adjustments'"></i>
                                                                <span x-text="configurando ? 'Cerrar ajustes' : 'Ajustes de la evaluación'"></span>
                                                            </button>
                                                        </div>

                                                        <!-- Ajustes Generales -->
                                                        <div x-show="configurando" class="p-3 border rounded bg-light mb-4 shadow-sm" x-transition
                                                             x-data="{ 
                                                                 config: {
                                                                     minimo_aprobacion: {{ $item->itemable->minimo_aprobacion ?? 50 }},
                                                                     limite_tiempo: {{ $item->itemable->limite_tiempo ?? 0 }},
                                                                     cantidad_repeticiones: {{ $item->itemable->cantidad_repeticiones ?? 0 }},
                                                                     tiempo_dilatacion: {{ $item->itemable->tiempo_dilatacion ?? 0 }}
                                                                 }
                                                             }">
                                                            <h6 class="mb-3 text-black"><i class="ti ti-settings me-1"></i> Configuración del examen</h6>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small">Nota mínima para aprobar: <span x-text="config.minimo_aprobacion" class="fw-bold text-primary"></span>%</label>
                                                                    <input type="range" class="form-range form-range-success" min="1" max="100" step="1" x-model="config.minimo_aprobacion">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small">Tiempo máximo (minutos)</label>
                                                                    <input type="number" class="form-control form-control-sm" min="0" x-model="config.limite_tiempo" placeholder="0 = Sin límite">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small">Intentos de repetición permitidos</label>
                                                                    <input type="number" class="form-control form-control-sm" min="0" x-model="config.cantidad_repeticiones" title="0 = Solo pueden hacerlo una vez, sin repeticiones">
                                                                    <small class="text-muted" style="font-size: 0.70rem;">0 = Sin intentos extra.</small>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small">Tiempo de espera tras fallar (Horas)</label>
                                                                    <input type="number" class="form-control form-control-sm" min="0" x-model="config.tiempo_dilatacion">
                                                                </div>
                                                            </div>
                                                            <div class="mt-3 text-end">
                                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill"
                                                                        @click="$wire.guardarConfiguracionEvaluacion({{ $item->itemable->id }}, config).then(() => { configurando = false })">
                                                                    <i class="ti ti-device-floppy me-1"></i> Guardar ajustes
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Listado de Preguntas -->
                                                        <div class="listadoPreguntas mt-3" data-evaluacion-id="{{ $item->itemable->id }}">
                                                            @foreach($item->itemable->preguntas as $pregunta)
                                                                <div wire:key="pregunta-{{ $pregunta->id }}" class="card mb-3 border bg-white shadow-none hover-shadow transition-all" x-data="{ expanded: true }" data-pregunta-id="{{ $pregunta->id }}">
                                                                    <div class="card-header p-2 d-flex justify-content-between align-items-center  border-bottom">
                                                                        <div class="d-flex align-items-center flex-grow-1 cursor-pointer drag-handle-pregunta" @click="expanded = !expanded">
                                                                            <i class="ti ti-grid-dots text-muted me-2"></i>
                                                                            <span class="fw-semibold small">Pregunta #{{ $loop->iteration }}</span>
                                                                        </div>
                                                                        <div class="d-flex gap-1">
                                                                            <button onclick="confirmarEliminacionPregunta({{ $pregunta->id }})" class="btn btn-sm btn-icon btn-text-danger rounded-pill" >
                                                                                <i class="ti ti-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-body p-3" x-show="expanded" x-transition>
                                                                        <div class="row g-3 mb-3">
                                                                            <div class="col-md-9">
                                                                                <input type="text" class="form-control" value="{{ $pregunta->pregunta }}" placeholder="Escribe la pregunta..."
                                                                                       @blur="$wire.guardarPregunta({{ $pregunta->id }}, $event.target.value, $refs.tipo{{ $pregunta->id }}.value)">
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <select x-ref="tipo{{ $pregunta->id }}" class="form-select" @change="$wire.guardarPregunta({{ $pregunta->id }}, $event.target.closest('.row').querySelector('input').value, $event.target.value)">
                                                                                    <option value="unica" {{ $pregunta->tipo_respuesta == 'unica' ? 'selected' : '' }}>Única</option>
                                                                                    <option value="multiple" {{ $pregunta->tipo_respuesta == 'multiple' ? 'selected' : '' }}>Múltiple</option>
                                                                                    <option value="verdadero_falso" {{ $pregunta->tipo_respuesta == 'verdadero_falso' ? 'selected' : '' }}>V/F</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Opciones -->
                                                                        <div class="opciones ps-2">
                                                                            @foreach($pregunta->opciones as $opcion)
                                                                                <div wire:key="opcion-{{ $opcion->id }}" class="d-flex align-items-center mb-2 gap-2 group-option">
                                                                                    <div class="form-check mb-0">
                                                                                        <input class="{{ $pregunta->tipo_respuesta == 'multiple' ? 'form-check-input' : 'form-check-input rounded-circle' }}" 
                                                                                               type="{{ $pregunta->tipo_respuesta == 'multiple' ? 'checkbox' : 'radio' }}" 
                                                                                               name="pregunta{{ $pregunta->id }}"
                                                                                               {{ $opcion->es_correcta ? 'checked' : '' }}
                                                                                               wire:click="marcarCorrecta({{ $opcion->id }})">
                                                                                    </div>
                                                                                    <input type="text" class="form-control form-control-sm border-0 bg-transparent edit-on-focus" 
                                                                                           value="{{ $opcion->opcion }}"
                                                                                           @blur="$wire.guardarOpcion({{ $opcion->id }}, $event.target.value)"
                                                                                           {{ $pregunta->tipo_respuesta == 'verdadero_falso' ? 'readonly' : '' }}>
                                                                                    @if($pregunta->tipo_respuesta !== 'verdadero_falso' && $pregunta->opciones->count() > 1)
                                                                                        <button wire:click="eliminarOpcion({{ $opcion->id }})" class="btn btn-xs btn-label-danger btn-icon border-0 opacity-0 btn-delete-option">
                                                                                            <i class="ti ti-x"></i>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>

                                                                        @if($pregunta->tipo_respuesta !== 'verdadero_falso')
                                                                            <button wire:click="agregarOpcion({{ $pregunta->id }})" class="btn btn-sm btn-text-primary mt-2 p-0">
                                                                                <i class="ti ti-plus me-1"></i> Agregar otra opción
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="text-center mt-3">
                                                            <button wire:click="agregarPregunta({{ $item->itemable->id }})" class="btn btn-sm btn-icon btn-outline-secondary rounded-pill">
                                                                <i class="ti ti-plus"></i> 
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-muted">
                                        <small class="text-black"><i>No hay lecciones ni evaluaciones en este módulo.</i></small>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Botones para agregar ítems -->
                            <div class="d-flex justify-content-end gap-3 mt-4 mb-2">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-outline-primary btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-plus me-1"></i> Agregar lección
                                    </button>
                                    <ul class="dropdown-menu shadow-sm">
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarItem({{ $modulo->id }}, 'video')"><i class="ti ti-player-play me-2"></i> Video</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarItem({{ $modulo->id }}, 'lectura')"><i class="ti ti-file-text me-2"></i> Lectura</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarItem({{ $modulo->id }}, 'recurso')"><i class="ti ti-file-download me-2"></i> Recurso</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarItem({{ $modulo->id }}, 'iframe')"><i class="ti ti-code me-2"></i> Contenido Embebido</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-plus me-1"></i> Agregar evaluación
                                    </button>
                                    @php
                                        $existeFinal = \App\Models\CursoItem::whereHas('modulo', function($q) use ($curso) {
                                            $q->where('curso_id', $curso->id);
                                        })->whereHas('tipo', function($q) {
                                            $q->where('codigo', 'evaluacion_final');
                                        })->exists();
                                    @endphp
                                    <ul class="dropdown-menu shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" wire:click="agregarItem({{ $modulo->id }}, 'quiz')">
                                                <i class="ti ti-question-mark me-2"></i> Quiz
                                            </a>
                                        </li>
                                        <li>
                                            @if($existeFinal)
                                                <div class="dropdown-item text-muted" style="cursor: not-allowed;" title="Ya existe una Evaluación Final en este curso">
                                                    <i class="ti ti-clipboard-check me-2"></i> Evaluación Final 
                                                    <span class="badge bg-label-secondary ms-2 p-1" style="font-size: 0.6rem;">Agregada</span>
                                                </div>
                                            @else
                                                <a class="dropdown-item" href="javascript:void(0);" wire:click="agregarItem({{ $modulo->id }}, 'evaluacion_final')">
                                                    <i class="ti ti-clipboard-check me-2"></i> Evaluación Final
                                                </a>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-grid gap-2 mt-4">
        <button wire:click="crearModulo" class="btn btn-primary btn-lg rounded-pill shadow-sm">
            <i class="ti ti-plus me-2"></i> Agregar nuevo módulo
        </button>
    </div>

    <!-- Offcanvas para Módulo -->
    <div wire:ignore.self class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasModulo" aria-labelledby="offcanvasModuloLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasModuloLabel" class="offcanvas-title text-primary fw-bold">
                {{ $modoEdicion ? 'Editar módulo' : 'Nuevo módulo' }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
            <form wire:submit.prevent="guardarModulo" class="pt-4">
                <div class="mb-3">
                    <label class="form-label fw-bold" for="nombre">Nombre del módulo</label>
                    <input type="text" id="nombre" class="form-control @error('nombre') is-invalid @enderror" wire:model.defer="nombre" placeholder="Ej: Fundamentos de Laravel" />
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold" for="descripcion">Descripción / Objetivo de aprendizaje</label>
                    <textarea id="descripcion" class="form-control" wire:model.defer="descripcion" rows="4" placeholder="¿Qué aprenderán en este módulo?"></textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mt-4 pt-2 border-top">
                    <button type="submit" class="btn btn-primary d-grid w-100 mb-2 rounded-pill">Aceptar</button>
                    <button type="button" class="btn btn-label-secondary d-grid w-100 rounded-pill" data-bs-dismiss="offcanvas">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    @assets
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        @vite([
            'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
            'resources/assets/vendor/libs/sortablejs/sortable.js'
        ])
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    @endassets

    @script
    <script>
        window.initQuillEditors = function() {
            document.querySelectorAll('[id^="editor"]').forEach(container => {
                // Check if Quill has already been initialized on this container
                if (!container.classList.contains('ql-container')) {
                    new Quill(container, {
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'header': 1 }, { 'header': 2 }],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'size': ['small', false, 'large', 'huge'] }],
                                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                [{ 'font': [] }],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                ['link', 'image', 'video'],
                                ['clean']
                            ],
                        },
                        theme: 'snow'
                    });
                }
            });
        }

        window.initSortableModulos = function() {
            const listadoModulos = document.querySelector('.listadoModulos');

            if (listadoModulos) {
                if (listadoModulos.sortableInstance) {
                    listadoModulos.sortableInstance.destroy();
                }

                listadoModulos.sortableInstance = Sortable.create(listadoModulos, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function (evt) {
                        let nuevoOrden = [];
                        const items = listadoModulos.querySelectorAll(':scope > [data-modulo-id]');
                        items.forEach((el, index) => {
                            nuevoOrden.push({
                                id: el.dataset.moduloId,
                                orden: index + 1
                            });
                        });
                        $wire.actualizarOrdenModulos(JSON.stringify(nuevoOrden));
                    }
                });
            }
        }

        window.initSortableItems = function() {
            const contenedoresItems = document.querySelectorAll('.listadoItems');

            contenedoresItems.forEach(contenedor => {
                if (contenedor.sortableInstance) {
                    contenedor.sortableInstance.destroy();
                }

                contenedor.sortableInstance = Sortable.create(contenedor, {
                    animation: 150,
                    handle: '.drag-handle-item',
                    onEnd: function (evt) {
                        const moduloId = contenedor.dataset.moduloId;
                        let nuevoOrden = [];
                        const items = contenedor.querySelectorAll(':scope > [data-item-id]');
                        items.forEach((el, index) => {
                            nuevoOrden.push({
                                id: el.dataset.itemId,
                                orden: index + 1
                            });
                        });
                        $wire.actualizarOrdenItems(moduloId, JSON.stringify(nuevoOrden));
                    }
                });
            });
        }

        window.initSortablePreguntas = function() {
            const contenedoresPreguntas = document.querySelectorAll('.listadoPreguntas');

            contenedoresPreguntas.forEach(contenedor => {
                if (contenedor.sortableInstance) {
                    contenedor.sortableInstance.destroy();
                }

                contenedor.sortableInstance = Sortable.create(contenedor, {
                    animation: 150,
                    handle: '.drag-handle-pregunta',
                    onEnd: function (evt) {
                        const evaluacionId = contenedor.dataset.evaluacionId;
                        let nuevoOrden = [];
                        const items = contenedor.querySelectorAll(':scope > [data-pregunta-id]');
                        items.forEach((el, index) => {
                            nuevoOrden.push({
                                id: el.dataset.preguntaId,
                                orden: index + 1
                            });
                        });
                        $wire.actualizarOrdenPreguntas(evaluacionId, JSON.stringify(nuevoOrden));
                    }
                });
            });
        }

        document.addEventListener('livewire:initialized', () => {
            initSortableModulos();
            initSortableItems();
            initSortablePreguntas();
            initQuillEditors();
        });

        $wire.on('refreshSortable', () => {
            setTimeout(() => {
                initSortableModulos();
                initSortableItems();
                initSortablePreguntas();
                initQuillEditors();
            }, 200);
        });

        $wire.on('refreshSortableItems', (data) => {
            setTimeout(() => {
                initSortableItems();
                initSortablePreguntas();
                initQuillEditors();
            }, 200);
        });

        $wire.on('msn', data => {
            Swal.fire({
                title: data.msnTitulo,
                text: data.msnTexto,
                icon: data.msnIcono,
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        });

        $wire.on('abrirModal', data => {
            // Agregar backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'offcanvas-backdrop fade show';
            document.body.appendChild(backdrop);

            const offcanvasElement = document.getElementById(data.nombreModal);
            if (offcanvasElement) {
                const offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
                    backdrop: true
                });
                offcanvas.show();

                // Remover backdrop al cerrar
                offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
                    backdrop.remove();
                });
            }
        });

        $wire.on('cerrarModal', data => {
            const offcanvasElement = document.getElementById(data.nombreModal);
            if (offcanvasElement) {
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvas) offcanvas.hide();
            }
        });

        $wire.on('confirmarEliminarModulo', (data) => {
            const moduloId = data.id;
            const nombreModulo = data.nombre;
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Deseas eliminar el módulo "${nombreModulo}". Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.eliminarModulo(moduloId);
                }
            });
        });

        window.confirmarEliminacion = (tipo, id) => {
            const titulo = tipo === 'modulo' ? '¿Eliminar módulo?' : '¿Eliminar ítem?';
            const texto = tipo === 'modulo' 
                ? 'Se eliminarán todos los ítems y archivos asociados a este módulo. Esta acción no se puede deshacer.' 
                : 'Se eliminará permanentemente este contenido y sus archivos asociados.';

            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (tipo === 'modulo') {
                        $wire.eliminarModulo(id);
                    } else {
                        $wire.eliminarItem(id);
                    }
                }
            });
        };

        window.confirmarEliminacionPregunta = (id) => {
            Swal.fire({
                title: '¿Eliminar pregunta?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.eliminarPregunta(id);
                }
            });
        };

        window.addEventListener('itemAgregado', (event) => {
            // Livewire 3 dispatch compatibility
            const data = (event.detail && event.detail.length > 0) ? event.detail[0] : event.detail;
            if (!data) return;
            
            setTimeout(() => {
                const moduloId = data.moduloId;
                const itemId = data.itemId;

                if (!moduloId || !itemId) return;

                // 1. Asegurar que el módulo esté abierto
                const collapseElement = document.getElementById(`collapse${moduloId}`);
                if (collapseElement) {
                    if (!collapseElement.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
                        bsCollapse.show();
                    }
                    
                    const btnAccordion = document.querySelector(`button[data-bs-target="#collapse${moduloId}"]`);
                    if (btnAccordion) {
                        btnAccordion.classList.remove('collapsed');
                        btnAccordion.setAttribute('aria-expanded', 'true');
                    }
                }

                // 2. Cerrar otras lecciones del mismo módulo antes de abrir la nueva
                const otherCollapses = document.querySelectorAll(`#accordionItems${moduloId} .accordion-collapse.show`);
                otherCollapses.forEach(coll => {
                    if (coll.id !== `itemContent${itemId}`) {
                        const bsColl = bootstrap.Collapse.getInstance(coll) || new bootstrap.Collapse(coll, { toggle: false });
                        bsColl.hide();
                        
                        // Ajustar botón del ítem antiguo
                        const btnOld = document.querySelector(`button[data-bs-target="#${coll.id}"]`);
                        if(btnOld){
                            btnOld.setAttribute('aria-expanded', 'false');
                            btnOld.classList.add('collapsed');
                        }
                    }
                });

                // 3. Hacer scroll al nuevo ítem y ABRIRLO
                const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                if (itemElement) {
                    itemElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Abrir el detalle del ítem automáticamente
                    const itemContent = document.getElementById(`itemContent${itemId}`);
                    if (itemContent) {
                         const bsCollapseItem = bootstrap.Collapse.getInstance(itemContent) || new bootstrap.Collapse(itemContent, { toggle: false });
                         bsCollapseItem.show();
                         
                         const btnNew = document.querySelector(`button[data-bs-target="#itemContent${itemId}"]`);
                         if(btnNew){
                             btnNew.setAttribute('aria-expanded', 'true');
                             btnNew.classList.remove('collapsed');
                         }
                    }
                }
            }, 400); // Dar suficiente tiempo para que Livewire termine de inyectar el DOM
        });
        
        // Auto-scroll al crear módulo
        window.addEventListener('moduloCreado', event => {
            setTimeout(() => {
                const moduloId = event.detail.moduloId;
                const element = document.querySelector(`[data-modulo-id="${moduloId}"]`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    const collapseElement = document.getElementById(`collapse${moduloId}`);
                    if (collapseElement) {
                        const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
                        bsCollapse.show();
                    }
                }
            }, 300);
        });
    </script>
    @endscript
    <style>
        .edit-on-focus:focus {
            background-color: #fff !important;
            border-bottom: 1px solid #7367f0 !important;
            box-shadow: none !important;
        }
        .group-option:hover .btn-delete-option {
            opacity: 1 !important;
        }
        .hover-shadow:hover {
            box-shadow: 0 .25rem 1rem rgba(165, 163, 174, .3) !important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .btn-delete-option {
            transition: opacity 0.2s ease;
        }
    </style>
</div>
