<div>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .recurso-header-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.65rem;
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .recurso-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.85rem;
            background-color: var(--bs-card-bg, #fff);
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }

        .recurso-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.4);
            box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.07);
            transform: translateY(-1px);
        }

        .recurso-type-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .recurso-type-icon.is-video {
            background-color: rgba(255, 76, 81, 0.12);
            color: #c82333;
        }

        .recurso-type-icon.is-documento {
            background-color: rgba(var(--bs-primary-rgb), 0.12);
            color: var(--bs-primary);
        }

        .recurso-type-icon.is-enlace {
            background-color: rgba(0, 186, 209, 0.14);
            color: #007b8a;
        }

        .recurso-type-icon.is-libro {
            background-color: rgba(115, 103, 240, 0.14);
            color: #5b4fd6;
        }

        .recurso-type-icon.is-clase {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943;
        }

        .recurso-type-icon.is-predica {
            background-color: rgba(255, 159, 67, 0.16);
            color: #9a5700;
        }

        /* Pills con contraste garantizado frente a _custom-variables.css */
        .recurso-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.22rem 0.6rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        .recurso-pill-video {
            background-color: rgba(255, 76, 81, 0.14);
            color: #c82333 !important;
        }

        .recurso-pill-documento {
            background-color: rgba(var(--bs-primary-rgb), 0.12);
            color: var(--bs-primary) !important;
        }

        .recurso-pill-enlace {
            background-color: rgba(0, 186, 209, 0.14);
            color: #007b8a !important;
        }

        .recurso-pill-libro {
            background-color: rgba(115, 103, 240, 0.14);
            color: #5b4fd6 !important;
        }

        .recurso-pill-clase {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943 !important;
        }

        .recurso-pill-predica {
            background-color: rgba(255, 159, 67, 0.18);
            color: #9a5700 !important;
        }

        .recurso-pill-neutral {
            background-color: rgba(108, 117, 125, 0.12);
            color: #495057 !important;
        }

        .recurso-description {
            color: #566a7f;
            font-size: 0.875rem;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recurso-option-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .recurso-option-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.55rem 0.8rem;
            border-radius: 0.6rem;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-gray-100);
            color: #2f2b3d;
            text-decoration: none;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .recurso-option-item:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.45);
            background-color: rgba(var(--bs-primary-rgb), 0.04);
            color: #2f2b3d;
        }

        .recurso-option-icon {
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .recurso-option-icon.is-youtube {
            background-color: rgba(255, 76, 81, 0.14);
            color: #d92d20;
        }

        .recurso-option-icon.is-link {
            background-color: rgba(0, 186, 209, 0.14);
            color: #007b8a;
        }

        .recurso-option-icon.is-file {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943;
        }

        .recurso-upload-hint {
            font-size: 0.78rem;
            color: #566a7f;
        }

        .recurso-upload-status {
            padding: 0.5rem 0.75rem;
            border-radius: 0.55rem;
            background-color: rgba(var(--bs-primary-rgb), 0.08);
            color: var(--bs-primary);
            font-size: 0.82rem;
            font-weight: 500;
        }

        .recurso-upload-error {
            padding: 0.5rem 0.75rem;
            border-radius: 0.55rem;
            background-color: rgba(255, 76, 81, 0.1);
            border: 1px solid rgba(255, 76, 81, 0.25);
            color: #c82333;
            font-size: 0.82rem;
            font-weight: 500;
        }
    </style>

    {{-- Barra superior del listado debajo de las pestañas: título + contador a la izquierda, botón a la derecha --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark">Listado de recursos</h5>
            <span class="recurso-pill recurso-pill-neutral">
                <i class="ti ti-files"></i>
                {{ $recursos->count() }} {{ $recursos->count() === 1 ? 'recurso' : 'recursos' }}
            </span>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 waves-effect waves-light" wire:click="abrirModalCrear">
                <i class="ti ti-plus me-1"></i> Crear nuevo recurso
            </button>
        </div>
    </div>

    {{-- Grilla de Cards de Recursos (6 columnas = 2 por fila en escritorio) --}}
    <div class="row g-4 mb-4">
        @forelse ($recursos as $recurso)
            @php
                $tipoNormalizado = strtolower((string) $recurso->tipo);
                $metaTipo = match ($tipoNormalizado) {
                    'video' => ['icono' => 'ti-video', 'box' => 'is-video', 'pill' => 'recurso-pill-video', 'etiqueta' => 'Video'],
                    'documento' => ['icono' => 'ti-file-text', 'box' => 'is-documento', 'pill' => 'recurso-pill-documento', 'etiqueta' => 'Documento'],
                    'enlace' => ['icono' => 'ti-link', 'box' => 'is-enlace', 'pill' => 'recurso-pill-enlace', 'etiqueta' => 'Enlace'],
                    'libro' => ['icono' => 'ti-book', 'box' => 'is-libro', 'pill' => 'recurso-pill-libro', 'etiqueta' => 'Libro'],
                    'clase' => ['icono' => 'ti-presentation', 'box' => 'is-clase', 'pill' => 'recurso-pill-clase', 'etiqueta' => 'Clase'],
                    'predica', 'prédica' => ['icono' => 'ti-microphone-2', 'box' => 'is-predica', 'pill' => 'recurso-pill-predica', 'etiqueta' => 'Prédica'],
                    default => ['icono' => 'ti-file', 'box' => 'is-documento', 'pill' => 'recurso-pill-neutral', 'etiqueta' => $recurso->tipo ?: 'Recurso'],
                };

                $tieneYoutube = filled($recurso->link_youtube);
                $tieneExterno = filled($recurso->link_externo);
                $tieneArchivo = filled($recurso->ruta_archivo);
                $totalVinculos = ($tieneYoutube ? 1 : 0) + ($tieneExterno ? 1 : 0) + ($tieneArchivo ? 1 : 0);

                $dominioExterno = $tieneExterno ? (parse_url($recurso->link_externo, PHP_URL_HOST) ?: $recurso->link_externo) : null;
                $extensionArchivo = $tieneArchivo ? strtolower((string) pathinfo((string) $recurso->ruta_archivo, PATHINFO_EXTENSION)) : '';
                $iconoArchivo = match ($extensionArchivo) {
                    'pdf' => 'ti-file-type-pdf',
                    'docx' => 'ti-file-type-docx',
                    'txt' => 'ti-file-type-txt',
                    default => 'ti-file-description',
                };
            @endphp

            <div class="col-12 col-md-6" wire:key="recurso-card-{{ $recurso->id }}">
                <div class="card recurso-card h-100 d-flex flex-column">
                    {{-- Cabecera de la card --}}
                    <div class="card-header bg-transparent border-bottom pb-3 pt-3 px-4">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="d-flex align-items-start gap-3 min-w-0">
                                <span class="recurso-type-icon {{ $metaTipo['box'] }}">
                                    <i class="ti {{ $metaTipo['icono'] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <h6 class="mb-1 fw-bold text-dark lh-sm text-break">{{ $recurso->nombre }}</h6>
                                    <span class="recurso-pill {{ $metaTipo['pill'] }}">
                                        <i class="ti {{ $metaTipo['icono'] }}"></i>
                                        {{ $metaTipo['etiqueta'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <button type="button" wire:click="abrirModalEditar({{ $recurso->id }})"
                                    class="btn btn-sm btn-icon btn-outline-secondary rounded-pill"
                                    title="Editar recurso" aria-label="Editar recurso">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button type="button"
                                    onclick="confirmarEliminarRecurso({{ $recurso->id }}, @js($recurso->nombre))"
                                    class="btn btn-sm btn-icon btn-outline-danger rounded-pill"
                                    title="Eliminar recurso" aria-label="Eliminar recurso">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Cuerpo de la card: descripción + cada opción claramente visible --}}
                    <div class="card-body px-4 py-3 flex-grow-1 d-flex flex-column justify-content-between gap-3">
                        @if (filled($recurso->descripcion))
                            <p class="recurso-description mb-0" title="{{ $recurso->descripcion }}">
                                {{ $recurso->descripcion }}
                            </p>
                        @else
                            <p class="text-muted small fst-italic mb-0">Sin descripción adicional.</p>
                        @endif

                        @if ($totalVinculos > 0)
                            <div class="recurso-option-list">
                                @if ($tieneYoutube)
                                    <a href="{{ $recurso->link_youtube }}" target="_blank" rel="noopener noreferrer"
                                        class="recurso-option-item">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <span class="recurso-option-icon is-youtube">
                                                <i class="ti ti-brand-youtube"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <span class="d-block fw-semibold small lh-sm">Video en YouTube</span>
                                                <small class="text-muted d-block text-truncate">{{ $recurso->link_youtube }}</small>
                                            </div>
                                        </div>
                                        <i class="ti ti-external-link text-muted flex-shrink-0"></i>
                                    </a>
                                @endif

                                @if ($tieneExterno)
                                    <a href="{{ $recurso->link_externo }}" target="_blank" rel="noopener noreferrer"
                                        class="recurso-option-item">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <span class="recurso-option-icon is-link">
                                                <i class="ti ti-world-www"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <span class="d-block fw-semibold small lh-sm">Enlace externo</span>
                                                <small class="text-muted d-block text-truncate">{{ $dominioExterno }}</small>
                                            </div>
                                        </div>
                                        <i class="ti ti-external-link text-muted flex-shrink-0"></i>
                                    </a>
                                @endif

                                @if ($tieneArchivo)
                                    <a href="{{ $recurso->archivo_url }}" target="_blank" rel="noopener noreferrer"
                                        class="recurso-option-item">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <span class="recurso-option-icon is-file">
                                                <i class="ti {{ $iconoArchivo }}"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <span class="d-block fw-semibold small lh-sm">Archivo adjunto ({{ strtoupper($extensionArchivo ?: 'DOC') }})</span>
                                                <small class="text-muted d-block text-truncate">{{ $recurso->nombre_archivo ?: $recurso->ruta_archivo }}</small>
                                            </div>
                                        </div>
                                        <i class="ti ti-download text-muted flex-shrink-0"></i>
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="p-2 rounded bg-lighter border text-muted small d-flex align-items-center gap-2">
                                <i class="ti ti-info-circle"></i>
                                <span>Sin enlaces ni archivo adjunto vinculado.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Pie de la card --}}
                    <div class="card-footer bg-transparent border-top px-4 py-2 d-flex justify-content-between align-items-center small text-muted">
                        <span class="d-inline-flex align-items-center gap-1">
                            <i class="ti ti-clock"></i>
                            {{ $recurso->updated_at ? $recurso->updated_at->diffForHumans() : 'Reciente' }}
                        </span>
                        <span>
                            {{ $totalVinculos }} {{ $totalVinculos === 1 ? 'opción cargada' : 'opciones cargadas' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card recurso-card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-folder-off fs-1 text-muted d-block mb-2"></i>
                        <h5 class="fw-bold text-dark mb-1">Aún no hay recursos</h5>
                        <p class="text-muted mb-3">Haz clic en "Crear nuevo recurso" para compartir material con tus estudiantes.</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" wire:click="abrirModalCrear">
                            <i class="ti ti-plus me-1"></i> Crear nuevo recurso
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- MODAL PARA CREAR / EDITAR RECURSO --}}
    @if ($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content"
                    x-data="{
                        subiendo: false,
                        errorMsg: '',
                        extensionesPermitidas: ['pdf', 'docx', 'txt'],
                        extensionesPeligrosas: ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh', 'js', 'vbs', 'ps1', 'html', 'htm', 'svg'],
                        tamanoMaximoBytes: 2 * 1024 * 1024,
                        subirArchivo(event) {
                            const file = event.target.files[0];
                            if (!file) {
                                return;
                            }

                            this.errorMsg = '';

                            const segmentos = file.name.toLowerCase().trim().split('.').filter(Boolean);
                            if (segmentos.length < 2) {
                                this.errorMsg = 'El archivo debe tener extensión .pdf, .docx o .txt.';
                                this.$refs.archivoInput.value = '';
                                return;
                            }

                            const extensionFinal = segmentos[segmentos.length - 1];
                            if (!this.extensionesPermitidas.includes(extensionFinal)) {
                                this.errorMsg = 'Formato no permitido. Solo se aceptan archivos .pdf, .docx o .txt.';
                                this.$refs.archivoInput.value = '';
                                return;
                            }

                            const segmentosIntermedios = segmentos.slice(1, -1);
                            if (segmentosIntermedios.some(seg => this.extensionesPeligrosas.includes(seg))) {
                                this.errorMsg = 'Archivo bloqueado por seguridad: contiene una doble extensión no permitida.';
                                this.$refs.archivoInput.value = '';
                                return;
                            }

                            if (file.size <= 0) {
                                this.errorMsg = 'El archivo está vacío.';
                                this.$refs.archivoInput.value = '';
                                return;
                            }

                            if (file.size > this.tamanoMaximoBytes) {
                                this.errorMsg = 'El archivo supera el tamaño máximo permitido de 2 MB.';
                                this.$refs.archivoInput.value = '';
                                return;
                            }

                            this.subiendo = true;

                            const formData = new FormData();
                            formData.append('archivo', file);
                            formData.append('horario_id', {{ $horario->id }});

                            fetch('{{ route('maestros.uploadArchivoRecurso') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: formData
                            })
                            .then(async response => {
                                const data = await response.json().catch(() => ({}));
                                return { ok: response.ok, data };
                            })
                            .then(({ ok, data }) => {
                                this.subiendo = false;
                                if (ok && data.success) {
                                    $wire.set('nombreArchivoSubido', data.nombre);
                                    $wire.set('rutaArchivoSubida', data.ruta_relativa);
                                } else {
                                    const errorValidacion = data?.errors?.archivo?.[0];
                                    this.errorMsg = errorValidacion || data.message || 'No se pudo validar o subir el archivo.';
                                    if (this.$refs.archivoInput) {
                                        this.$refs.archivoInput.value = '';
                                    }
                                }
                            })
                            .catch(() => {
                                this.subiendo = false;
                                this.errorMsg = 'Error de conexión al subir el archivo.';
                                if (this.$refs.archivoInput) {
                                    this.$refs.archivoInput.value = '';
                                }
                            });
                        },
                        eliminarArchivoLocal() {
                            this.errorMsg = '';
                            $wire.call('eliminarArchivoLocal');
                        }
                    }">
                    <form wire:submit.prevent="guardarRecurso">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="ti {{ $recursoId ? 'ti-pencil' : 'ti-plus' }} text-primary"></i>
                                {{ $recursoId ? 'Editar recurso' : 'Crear nuevo recurso' }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="cerrarModal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre del recurso <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    wire:model="nombre" placeholder="Ej: Guía de estudio Corte 1">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                    wire:model="descripcion" rows="3" placeholder="Explica brevemente de qué trata este recurso..."></textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Tipo de recurso <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tipo') is-invalid @enderror" wire:model="tipo">
                                        <option value="">Seleccione un tipo...</option>
                                        <option value="Video">Video</option>
                                        <option value="Documento">Documento</option>
                                        <option value="Enlace">Enlace</option>
                                        <option value="Libro">Libro</option>
                                        <option value="Clase">Clase</option>
                                        <option value="Predica">Prédica</option>
                                    </select>
                                    @error('tipo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Enlace de YouTube (Opcional)</label>
                                    <input type="url" class="form-control @error('link_youtube') is-invalid @enderror"
                                        wire:model="link_youtube" placeholder="https://www.youtube.com/watch?v=...">
                                    @error('link_youtube')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Enlace externo (Opcional)</label>
                                    <input type="url" class="form-control @error('link_externo') is-invalid @enderror"
                                        wire:model="link_externo" placeholder="https://ejemplo.com/documento">
                                    @error('link_externo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-1">
                                <label class="form-label fw-semibold">Archivo adjunto (Opcional)</label>

                                @if ($editingResource && $editingResource->nombre_archivo)
                                    <div class="d-flex align-items-center justify-content-between gap-2 p-2 px-3 border rounded-3 bg-lighter">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <i class="ti ti-file-check fs-4 text-success flex-shrink-0"></i>
                                            <a href="{{ $editingResource->archivo_url }}" target="_blank" rel="noopener noreferrer"
                                                class="text-dark fw-semibold text-truncate">
                                                {{ $editingResource->nombre_archivo }}
                                            </a>
                                        </div>
                                        <button type="button"
                                            class="btn btn-outline-danger btn-icon btn-sm rounded-pill flex-shrink-0"
                                            onclick="confirmarEliminarArchivoAdjunto()"
                                            wire:loading.attr="disabled"
                                            title="Eliminar archivo adjunto" aria-label="Eliminar archivo adjunto">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                @else
                                    <template x-if="!$wire.nombreArchivoSubido">
                                        <div>
                                            <input type="file"
                                                class="form-control"
                                                accept=".pdf,.docx,.txt,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain"
                                                x-ref="archivoInput"
                                                :disabled="subiendo"
                                                @change="subirArchivo">

                                            <div class="recurso-upload-hint mt-1 d-flex align-items-center gap-1">
                                                <i class="ti ti-shield-check text-primary"></i>
                                                <span>Solo archivos <strong>.pdf</strong>, <strong>.docx</strong> o <strong>.txt</strong> (máximo <strong>2 MB</strong>).</span>
                                            </div>

                                            {{-- Importante: NO mezclar x-show con .d-flex (!important de Bootstrap pisa display:none) --}}
                                            <div x-show="subiendo" x-cloak class="mt-2">
                                                <div class="recurso-upload-status d-flex align-items-center gap-2">
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    <span>Validando y subiendo archivo...</span>
                                                </div>
                                            </div>

                                            <div x-show="errorMsg" x-cloak class="mt-2">
                                                <div class="recurso-upload-error d-flex align-items-center gap-2">
                                                    <i class="ti ti-alert-triangle flex-shrink-0"></i>
                                                    <span x-text="errorMsg"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="$wire.nombreArchivoSubido">
                                        <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 p-2 px-3 mb-0">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <i class="ti ti-circle-check fs-4 flex-shrink-0"></i>
                                                <div class="text-truncate">
                                                    <strong>Archivo listo:</strong>
                                                    <span x-text="$wire.nombreArchivoSubido"></span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close flex-shrink-0" @click="eliminarArchivoLocal" aria-label="Quitar archivo"></button>
                                        </div>
                                    </template>
                                @endif

                                @error('archivo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="cerrarModal" :disabled="subiendo">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" :disabled="subiendo">
                                <span wire:loading wire:target="guardarRecurso" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @script
    <script>
        Livewire.on('notificacion', (event) => {
            const detail = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: detail.icono || 'success',
                title: detail.titulo || '¡Realizado!',
                text: detail.texto || detail.mensaje || '',
                timer: detail.timer || 2500,
                showConfirmButton: detail.showConfirmButton === undefined ? false : detail.showConfirmButton
            });
        });

        window.confirmarEliminarRecurso = function (id, nombre) {
            Swal.fire({
                title: '¿Eliminar recurso?',
                text: `Se eliminará "${nombre}" y su archivo adjunto si tiene uno. Esta acción no se puede revertir.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-2 rounded-pill',
                    cancelButton: 'btn btn-outline-secondary rounded-pill'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.dispatch('eliminar-recurso', { id: id });
                }
            });
        };

        window.confirmarEliminarArchivoAdjunto = function () {
            Swal.fire({
                title: '¿Eliminar archivo adjunto?',
                text: 'El archivo se eliminará permanentemente de este recurso.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar archivo',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-2 rounded-pill',
                    cancelButton: 'btn btn-outline-secondary rounded-pill'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.dispatch('eliminar-archivo-adjunto');
                }
            });
        };
    </script>
    @endscript
</div>
