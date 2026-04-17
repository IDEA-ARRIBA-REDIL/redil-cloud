<div>
    <h4 class="mb-1 fw-semibold text-primary mb-10">{{ $plan->titulo }}</h4>

    @if($dias->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-calendar-plus display-3 text-black"></i>
                </div>
                <h5 class="text-black">Comienza agregando el primer día del plan lector</h5>                
            </div>
        </div>
    @else
        <div class="listadoDias accordion accordion-bordered" id="accordionDias">
            @foreach($dias as $dia)
                <div wire:key="dia-{{ $dia->id }}" wire:ignore.self class="accordion-item card shadow-none border mb-3" data-dia-id="{{ $dia->id }}">
                    <h2 class="accordion-header d-flex align-items-center" id="heading{{ $dia->id }}">
                        <i class="ti ti-grip-vertical drag-handle ms-5 cursor-move text-black" style="font-size: 1.2rem;"></i>
                        <button class="accordion-button collapsed border-0 shadow-none bg-transparent py-3" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $dia->id }}" 
                                aria-expanded="false" 
                                aria-controls="collapse{{ $dia->id }}"
                                wire:ignore.self
                                style="padding-right: 0;">
                            <style>
                                #heading{{ $dia->id }} .accordion-button::after { display: none !important; }
                            </style>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-primary">Día {{ $dia->dia }}: {{ $dia->titulo }}</span>
                            </div>
                        </button>
                        <div class="ms-auto pe-2 pb-2">
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0);" wire:click="editarDia({{ $dia->id }})"><i class="ti ti-edit me-1"></i> Editar</a></li>
                                    <hr class="dropdown-divider">
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion('dia', {{ $dia->id }})"><i class="ti ti-trash me-1"></i> Eliminar</a></li>
                                </ul>
                            </div>
                        </div>
                    </h2>
                    <div id="collapse{{ $dia->id }}" 
                         class="accordion-collapse collapse" 
                         aria-labelledby="heading{{ $dia->id }}" 
                         data-bs-parent="#accordionDias"
                         wire:ignore.self>
                        <div class="accordion-body bg-lighter pt-2">
                            <!-- Listado de Contenidos -->
                            <div class="listadoContenidos mt-3 pt-3" data-dia-id="{{ $dia->id }}" id="accordionContenidos{{ $dia->id }}">
                                @forelse($dia->contenidos as $contenido)
                                    <div wire:key="contenido-{{ $contenido->id }}" class="card mb-3 border shadow-none" data-contenido-id="{{ $contenido->id }}">
                                        <div class="card-body p-2" 
                                             x-data="{ 
                                                 tipo: {
                                                     slug: '{{ $contenido->tipoContenido->slug }}',
                                                     es_html: {{ $contenido->tipoContenido->es_html ? 'true' : 'false' }} || '{{ $contenido->tipoContenido->slug }}'.includes('reflexion'),
                                                     es_json: {{ $contenido->tipoContenido->es_json ? 'true' : 'false' }} || '{{ $contenido->tipoContenido->slug }}'.includes('pasaje') || '{{ $contenido->tipoContenido->slug }}'.includes('biblia'),
                                                     es_link: {{ $contenido->tipoContenido->es_link ? 'true' : 'false' }} || '{{ $contenido->tipoContenido->slug }}'.includes('video') || '{{ $contenido->tipoContenido->slug }}'.includes('url')
                                                 },
                                                 modo: '{{ ($contenido->contenido && $contenido->contenido !== "[]") ? 'visualizar' : 'editar' }}',
                                                 previewCita: '',
                                                 previewTexto: '',
                                                 hasPreview: false,

                                                 init() {
                                                     // Solo intentamos parsear si el tipo está marcado como JSON y tiene contenido
                                                     if (this.tipo.es_json && {{ $contenido->contenido ? 'true' : 'false' }}) {
                                                         try {
                                                             let rawData = @js($contenido->contenido ?: "[]");
                                                             let data = (typeof rawData === 'string' && rawData.trim().startsWith('[')) ? JSON.parse(rawData) : rawData;
                                                             if (Array.isArray(data) && data.length > 0) {
                                                                 this.renderPreview(data[0]);
                                                             }
                                                         } catch(e) { console.error('Error parsing content:', e); }
                                                     }
                                                 },

                                                 renderPreview(data) {
                                                     if (data && data.versiculos && data.versiculos.length > 0) {
                                                         this.previewCita = data.cita_larga || data.cita;
                                                         let htmlVerses = '';
                                                         data.versiculos.forEach(v => {
                                                             htmlVerses += `<sup class='text-primary fw-bold me-1'>${v.numero}</sup>${v.texto} `;
                                                         });
                                                         this.previewTexto = htmlVerses.trim();
                                                         this.hasPreview = true;
                                                     } else {
                                                         this.hasPreview = false;
                                                     }
                                                 }
                                             }"
                                             x-on:pasaje-actualizado.window="if($event.detail.contenidoId == {{ $contenido->id }}) { renderPreview($event.detail.data) }">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <i class="ti ti-grip-vertical drag-handle-contenido me-2 text-muted cursor-move"></i>
                                                    @if($contenido->tipo->slug === 'reflexion')
                                                        <i class="ti ti-file-text me-2 text-secondary"></i>
                                                    @elseif($contenido->tipo->slug === 'pasaje')
                                                        <i class="ti ti-book me-2 text-secondary"></i>
                                                    @elseif($contenido->tipo->slug === 'video')
                                                        <i class="ti ti-player-play me-2 text-secondary"></i>
                                                    @endif
                                                    <span class="fw-semibold text-black">{{ $contenido->tipo->nombre }}</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button class="btn btn-sm btn-icon text-muted me-1" data-bs-toggle="collapse" data-bs-target="#contenidoContent{{ $contenido->id }}" aria-expanded="false" wire:ignore.self>
                                                        <i class="ti ti-chevron-down"></i>
                                                    </button>
                                                    <button x-show="modo === 'visualizar'" 
                                                            @click.stop="modo = 'editar'; 
                                                                    $nextTick(() => {
                                                                        const collEl = document.getElementById('contenidoContent{{ $contenido->id }}');
                                                                        const bsColl = bootstrap.Collapse.getInstance(collEl) || new bootstrap.Collapse(collEl, {toggle: false});
                                                                        bsColl.show();
                                                                        if(window.initQuillEditors) window.initQuillEditors();
                                                                    });" 
                                                            class="btn btn-sm btn-icon btn-text-primary rounded-pill me-1" 
                                                            type="button"
                                                            title="Editar contenido">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button onclick="confirmarEliminacion('contenido', {{ $contenido->id }})" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Eliminar">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="contenidoContent{{ $contenido->id }}" class="accordion-collapse collapse mt-3 pt-3" 
                                                 data-bs-parent="#accordionContenidos{{ $dia->id }}"
                                                 wire:ignore.self>
                                                
                                                <!-- Video -->
                                                <div x-show="tipo.es_link" x-cloak>
                                                    <div x-show="modo === 'visualizar'" class="mb-3">
                                                        @php
                                                            $videoData = json_decode($contenido->contenido, true);
                                                            $url = $videoData['url'] ?? $contenido->contenido;
                                                            $plat = $videoData['plataforma'] ?? 'otro';
                                                            $vId = $videoData['id'] ?? '';
                                                        @endphp

                                                        @if($plat === 'youtube')
                                                            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm mb-3">
                                                                <iframe src="https://www.youtube.com/embed/{{ $vId }}" allowfullscreen></iframe>
                                                            </div>
                                                        @elseif($plat === 'vimeo')
                                                            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm mb-3">
                                                                <iframe src="https://player.vimeo.com/video/{{ $vId }}" allowfullscreen></iframe>
                                                            </div>
                                                        @else
                                                            <div class="p-3 border rounded bg-white text-black text-center mb-3">
                                                                <a href="{{ $url }}" target="_blank" class="btn btn-outline-primary shadow-sm"><i class="ti ti-external-link"></i> Ver Video Original</a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div x-show="modo === 'editar'">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">URL del Video (YouTube o Vimeo)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ti ti-brand-youtube"></i></span>
                                                                <input type="text" class="form-control" placeholder="https://www.youtube.com/watch?v=..." x-ref="videoUrl{{ $contenido->id }}" value="{{ $url }}">
                                                            </div>
                                                        </div>
                                                        <button class="btn btn-sm btn-outline-primary rounded-pill mb-2" 
                                                                @click="$wire.guardarVideo({{ $contenido->id }}, $refs.videoUrl{{ $contenido->id }}.value).then((res) => { if(res !== false) modo = 'visualizar' })">
                                                            <i class="ti ti-device-floppy me-1"></i> Guardar
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Reflexion -->
                                                <div x-show="tipo.es_html" x-cloak>
                                                    <div x-show="modo === 'visualizar'" class="mb-3">
                                                        <div class="p-3 border rounded bg-white text-black ql-editor">
                                                            {!! $contenido->contenido !!}
                                                        </div>
                                                    </div>
                                                    <div x-show="modo === 'editar'">
                                                        <div class="mb-3" wire:ignore>
                                                            <label class="form-label fw-bold">Escribe la reflexión</label>
                                                            <div id="editor{{ $contenido->id }}" style="height: 200px; background: white;">
                                                                {!! $contenido->contenido !!}
                                                            </div>
                                                        </div>
                                                        <button class="btn btn-sm btn-outline-primary rounded-pill mb-2" 
                                                                @click="$wire.guardarReflexion({{ $contenido->id }}, document.querySelector('#editor{{ $contenido->id }} .ql-editor').innerHTML).then(() => { modo = 'visualizar' })">
                                                            <i class="ti ti-device-floppy me-1"></i> Guardar
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Pasaje Biblico -->
                                                <div x-show="tipo.es_json" class="m-3" x-cloak>
                                                    <div x-show="modo === 'visualizar'" class="mb-3">
                                                        <div class="p-4 border rounded bg-white text-black shadow-sm" x-show="hasPreview">
                                                            <h6 class="text-primary fw-bold mb-2" x-text="previewCita"></h6>
                                                            <div class="mb-0 fs-5 lh-base text-dark" style="text-align: justify;" x-html="previewTexto"></div>
                                                        </div>
                                                        <div class="p-3 border rounded bg-light text-muted text-center" x-show="!hasPreview">
                                                           <i class="ti ti-info-circle me-1"></i> Pasaje bíblico no configurado.
                                                        </div>
                                                    </div>
                                                    <div x-show="modo === 'editar'">
                                                        <div class="mb-3" wire:ignore>
                                                            <label class="form-label fw-bold">Selecciona el pasaje bíblico</label>
                                                            @livewire('TiempoConDios.biblia', ['name_id' => 'biblia_'.$contenido->id, 'despacharEvento' => true], key('biblia-'.$contenido->id))
                                                            
                                                            <!-- Panel de Previsualización Dinámica (Dynamics like Versiculos/Crear) -->
                                                            <div class="mt-4 border rounded p-4 bg-white shadow-sm" x-show="hasPreview" x-cloak>
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h6 class="text-primary fw-bold mb-0">Previsualización del Versículo</h6>
                                                                    <span class="badge bg-label-primary rounded-pill">Vista rápida</span>
                                                                </div>
                                                                <div class="border-start border-primary border-3 ps-3">
                                                                    <h6 class="fw-bold mb-2 text-dark" x-text="previewCita"></h6>
                                                                    <div class="text-dark fs-5" style="line-height: 1.6;" x-html="previewTexto"></div>
                                                                </div>
                                                            </div>

                                                            <div class="form-text mt-2"><i class="ti ti-info-circle"></i> Al seleccionar el pasaje, aparecerá la vista previa. Luego haz clic en Guardar.</div>
                                                        </div>
                                                        <div class="d-none">
                                                            <textarea id="tempPasaje{{ $contenido->id }}" x-ref="tempPasaje{{ $contenido->id }}"></textarea>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-primary rounded-pill mb-2" 
                                                                    @click="$wire.guardarPasajeBiblico({{ $contenido->id }}, $refs.tempPasaje{{ $contenido->id }}.value).then(() => { modo = 'visualizar' })">
                                                                <i class="ti ti-device-floppy me-1"></i> Guardar
                                                            </button>
                                                            <button class="btn btn-sm btn-label-secondary rounded-pill mb-2" @click="modo = 'visualizar'">Cancelar</button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-muted">
                                        <small class="text-black"><i>No hay contenidos para este día.</i></small>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Botones para agregar controles -->
                            <div class="d-flex justify-content-end gap-3 mt-4 mb-2">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-outline-primary btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-plus me-1"></i> Agregar Contenido
                                    </button>
                                    <ul class="dropdown-menu shadow-sm">
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarContenido({{ $dia->id }}, 'pasaje')"><i class="ti ti-book me-2"></i> Pasaje Bíblico</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarContenido({{ $dia->id }}, 'reflexion')"><i class="ti ti-file-text me-2"></i> Reflexión</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" wire:click="agregarContenido({{ $dia->id }}, 'video')"><i class="ti ti-player-play me-2"></i> Video</a></li>
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
        <button wire:click="crearDia" class="btn btn-primary btn-lg rounded-pill shadow-sm">
            <i class="ti ti-plus me-2"></i> Agregar nuevo día
        </button>
    </div>

    <!-- Offcanvas para Día -->
    <div wire:ignore.self class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDia" aria-labelledby="offcanvasDiaLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasDiaLabel" class="offcanvas-title text-primary fw-bold">
                {{ $modoEdicionDia ? 'Editar Día' : 'Nuevo Día' }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
            <form wire:submit.prevent="guardarDia" class="pt-4">
                <div class="mb-3">
                    <label class="form-label fw-bold" for="diaTitulo">Título del Día</label>
                    <input type="text" id="diaTitulo" class="form-control @error('diaTitulo') is-invalid @enderror" wire:model.defer="diaTitulo" placeholder="Ej: La creación del mundo" />
                    @error('diaTitulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                if (!container.classList.contains('ql-container')) {
                    try {
                        new Quill(container, {
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{ 'header': 1 }, { 'header': 2 }],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    ['clean']
                                ],
                            },
                            theme: 'snow'
                        });
                    } catch (e) { console.error('Quill Init Error:', e); }
                }
            });
        }

        window.initSortableDias = function() {
            const listadoDias = document.querySelector('.listadoDias');
            if (listadoDias) {
                if (listadoDias.sortableInstance) {
                    listadoDias.sortableInstance.destroy();
                }
                listadoDias.sortableInstance = Sortable.create(listadoDias, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function (evt) {
                        let nuevoOrden = [];
                        const items = listadoDias.querySelectorAll(':scope > [data-dia-id]');
                        items.forEach((el, index) => {
                            nuevoOrden.push({
                                id: el.dataset.diaId,
                                orden: index + 1
                            });
                        });
                        $wire.actualizarOrdenDias(JSON.stringify(nuevoOrden));
                    }
                });
            }
        }

        window.initSortableContenidos = function() {
            const contenedoresContenidos = document.querySelectorAll('.listadoContenidos');
            contenedoresContenidos.forEach(contenedor => {
                if (contenedor.sortableInstance) {
                    contenedor.sortableInstance.destroy();
                }
                contenedor.sortableInstance = Sortable.create(contenedor, {
                    animation: 150,
                    handle: '.drag-handle-contenido',
                    onEnd: function (evt) {
                        const diaId = contenedor.dataset.diaId;
                        let nuevoOrden = [];
                        const items = contenedor.querySelectorAll(':scope > [data-contenido-id]');
                        items.forEach((el, index) => {
                            nuevoOrden.push({
                                id: el.dataset.contenidoId,
                                orden: index + 1
                            });
                        });
                        $wire.actualizarOrdenContenidos(diaId, JSON.stringify(nuevoOrden));
                    }
                });
            });
        }

        document.addEventListener('livewire:initialized', () => {
            initSortableDias();
            initSortableContenidos();
            initQuillEditors();

            // Setup listeners for bible selection components
            Livewire.on('bibliaSeleccionada', (data) => {
                // Determine which biblia component emitted the event. 
                // Since biblia.php emits globally, we use a heuristic or find the active "editing" content
                let validJson = "[]";
                let rawData = (data[0] !== undefined) ? data[0] : data;

                if (rawData && rawData.versiculos && rawData.versiculos.length > 0) {
                    validJson = JSON.stringify([rawData]);
                }

                // Update ALL tempPasaje textareas for safety since the bible selector might be duplicated
                // AND trigger the Alpine preview
                document.querySelectorAll('textarea[id^="tempPasaje"]').forEach(function(el) {
                    // Only update if its parent wrapper is visible, indicating the user is editing it
                    let parentEditar = el.closest('[x-show="modo === \'editar\'"]');
                    if (parentEditar && getComputedStyle(parentEditar).display != 'none') {
                        el.value = validJson;
                        
                        // Encontramos el ID de contenido para disparar el evento específico de Alpine
                        let card = el.closest('[data-contenido-id]');
                        if (card) {
                            let contenidoId = card.dataset.contenidoId;
                            window.dispatchEvent(new CustomEvent('pasaje-actualizado', { 
                                detail: { 
                                    contenidoId: contenidoId,
                                    data: rawData
                                }
                            }));
                        }
                    }
                });
            });
        });

        $wire.on('refreshSortable', () => {
            setTimeout(() => {
                initSortableDias();
                initSortableContenidos();
                initQuillEditors();
            }, 200);
        });

        $wire.on('refreshSortableItems', (data) => {
            setTimeout(() => {
                initSortableContenidos();
                initQuillEditors();
            }, 200);
        });

        $wire.on('contenidoAgregado', (data) => {
            // Livewire 3 event dispatch syntax
            const info = (data[0] !== undefined) ? data[0] : data;
            
            setTimeout(() => {
                const diaId = info.diaId;
                const contenidoId = info.contenidoId;
                if (!diaId || !contenidoId) return;

                // Expand module
                const collapseElement = document.getElementById(`collapse${diaId}`);
                if (collapseElement && !collapseElement.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
                    bsCollapse.show();
                }

                // Expand new item
                const contentCollapse = document.getElementById(`contenidoContent${contenidoId}`);
                if (contentCollapse && !contentCollapse.classList.contains('show')) {
                    const bsContentCollapse = new bootstrap.Collapse(contentCollapse, { toggle: false });
                    bsContentCollapse.show();
                }
            }, 300);
        });

        $wire.on('msn', data => {
            const args = (data[0] !== undefined) ? data[0] : data;
            Swal.fire({
                title: args.msnTitulo,
                text: args.msnTexto,
                icon: args.msnIcono,
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });

        $wire.on('abrirModal', data => {
            const args = (data[0] !== undefined) ? data[0] : data;
            
            // Solo actuar si es el offcanvas del Día
            if (args.nombreModal === 'offcanvasDia') {
                const backdrop = document.createElement('div');
                backdrop.className = 'offcanvas-backdrop fade show';
                document.body.appendChild(backdrop);

                const offcanvasElement = document.getElementById(args.nombreModal);
                if (offcanvasElement) {
                    const offcanvas = new bootstrap.Offcanvas(offcanvasElement, { backdrop: false });
                    offcanvas.show();
                    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
                        const existingBackdrop = document.querySelector('.offcanvas-backdrop.fade.show');
                        if(existingBackdrop) existingBackdrop.remove();
                    });
                }
            }
        });

        $wire.on('cerrarModal', data => {
            const args = (data[0] !== undefined) ? data[0] : data;
            if (args.nombreModal === 'offcanvasDia') {
                const offcanvasElement = document.getElementById(args.nombreModal);
                if (offcanvasElement) {
                    const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                    if (offcanvas) offcanvas.hide();
                }
            }
        });

        window.confirmarEliminacion = (tipo, id) => {
            const titulo = tipo === 'dia' ? '¿Eliminar Día?' : '¿Eliminar Contenido?';
            const texto = tipo === 'dia' 
                ? 'Se eliminarán todos los contenidos asociados a este día. Esta acción no se puede deshacer.' 
                : 'Se eliminará permanentemente este contenido.';

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
                    if (tipo === 'dia') {
                        $wire.eliminarDia(id);
                    } else {
                        $wire.eliminarContenido(id);
                    }
                }
            });
        };
    </script>
    @endscript
</div>
