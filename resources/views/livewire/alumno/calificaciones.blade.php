<div>
    {{-- Bucle principal para mostrar los cortes académicos --}}
    @forelse ($cortes as $corte)
        <div class="card corte-alumno-card mb-4" wire:key="corte-alumno-{{ $corte->id }}">
            <div class="corte-alumno-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="section-icon-box">
                        <i class="ti ti-list-check text-primary"></i>
                    </span>
                    <h5 class="mb-0 fw-bold text-dark fs-6">{{ $corte->nombre_completo }}</h5>
                </div>
                <span class="ui-pill ui-pill-neutral">
                    {{ $corte->itemInstancias->count() }} {{ $corte->itemInstancias->count() === 1 ? 'actividad' : 'actividades' }}
                </span>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    @forelse ($corte->itemInstancias as $item)
                        @php
                            $estadoLower = strtolower((string) $item->estado);
                            $pillEstado = match ($estadoLower) {
                                'calificado' => 'ui-pill-success',
                                'entregado' => 'ui-pill-info',
                                default => 'ui-pill-warning',
                            };
                            $iconoEstado = match ($estadoLower) {
                                'calificado' => 'ti-circle-check',
                                'entregado' => 'ti-send',
                                default => 'ti-clock',
                            };
                            $tieneNota = $item->nota !== null && $item->nota !== '';
                            $pesoFormateado = is_numeric($item->porcentaje)
                                ? rtrim(rtrim(number_format((float) $item->porcentaje, 2), '0'), '.')
                                : '0';
                        @endphp
                        {{-- 3 columnas en xl, 2 en lg y md, 1 en pantallas pequeñas --}}
                        <div class="col-12 col-md-6 col-xl-4" wire:key="item-calificacion-{{ $item->id }}">
                            <div class="card h-100 card-item-calificacion status-{{ $estadoLower }}">
                                <div class="card-body p-3 d-flex flex-column justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                            <span class="ui-pill {{ $pillEstado }}">
                                                <i class="ti {{ $iconoEstado }}"></i>
                                                {{ $item->estado }}
                                            </span>
                                            <span class="ui-pill ui-pill-neutral">
                                                Peso: {{ $pesoFormateado }}%
                                            </span>
                                        </div>

                                        <h6 class="mb-1 fw-bold text-dark lh-sm" title="{{ $item->nombre }}">
                                            {{ $item->nombre }}
                                        </h6>

                                        <small class="text-muted d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-calendar-event"></i>
                                            Entrega: {{ $item->fecha_fin ? $item->fecha_fin->format('d/m/Y') : 'Sin fecha' }}
                                        </small>

                                        @if (filled($item->contenido))
                                            <div class="mt-3 pt-2 border-top small text-muted">
                                                {!! $item->contenido !!}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Pie de la tarjeta con la nota y acción --}}
                                    <div class="d-flex align-items-end justify-content-between gap-2 pt-3 border-top">
                                        <div>
                                            <small class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                                Nota
                                            </small>
                                            @if ($tieneNota)
                                                <span class="nota-valor {{ $item->nota >= $notaMinimaAprobacion ? 'text-success' : 'text-danger' }}">
                                                    {{ $item->nota }}
                                                </span>
                                            @else
                                                <span class="nota-valor text-muted">--</span>
                                            @endif
                                        </div>

                                        <div>
                                            @if (is_null($item->nota))
                                                @if ($item->entregado)
                                                    <button type="button" wire:click="abrirModal({{ $item->id }})"
                                                        class="btn btn-primary rounded-pill btn-sm px-3">
                                                        <i class="ti ti-edit me-1"></i>Editar
                                                    </button>
                                                @else
                                                    <button type="button" wire:click="abrirModal({{ $item->id }})"
                                                        class="btn btn-primary rounded-pill btn-sm px-3">
                                                        <i class="ti ti-pencil me-1"></i>Responder
                                                    </button>
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm px-3"
                                                    data-bs-toggle="modal" data-bs-target="#verRespuestaModal"
                                                    data-item-titulo="{{ $item->nombre }}"
                                                    data-respuesta-alumno="{{ $item->respuesta_alumno }}"
                                                    data-feedback-maestro="{{ $item->feedback_maestro }}">
                                                    <i class="ti ti-eye me-1"></i>Ver respuesta
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-4 bg-lighter rounded-3 small">
                                <i class="ti ti-clipboard-off fs-3 d-block mb-1"></i>
                                Aún no hay actividades calificables para este corte.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        <div class="card alumno-surface-card">
            <div class="card-body text-center py-5">
                <i class="ti ti-calculator-off fs-1 text-muted d-block mb-2"></i>
                <h5 class="fw-bold text-dark mb-1">Sin cortes registrados</h5>
                <p class="text-muted mb-0">Aún no hay cortes de calificación configurados para esta materia.</p>
            </div>
        </div>
    @endforelse

    {{-- ============================================= --}}
    {{-- === MODAL UNIFICADO PARA RESPONDER/EDITAR === --}}
    {{-- ============================================= --}}
    @if ($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="ti ti-pencil text-primary"></i>
                            Responder a: {{ $selectedItem?->nombre }}
                        </h5>
                        <button wire:click="$set('showModal', false)" type="button" class="btn-close" aria-label="Cerrar"></button>
                    </div>
                    <form wire:submit="guardarRespuesta">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="respuestaTexto" class="form-label fw-semibold">Tu respuesta:</label>
                                <textarea wire:model="respuestaTexto" id="respuestaTexto"
                                    class="form-control @error('respuestaTexto') is-invalid @enderror"
                                    rows="7" placeholder="Escribe tu respuesta aquí..."></textarea>
                                @error('respuestaTexto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Componente Alpine.js para la subida de archivos --}}
                            <div class="mb-1" x-data="{
                                subiendo: false,
                                errorMsg: '',
                                subirArchivo(event) {
                                    const file = event.target.files[0];
                                    if (!file) return;

                                    this.subiendo = true;
                                    this.errorMsg = '';

                                    const formData = new FormData();
                                    formData.append('archivo', file);
                                    formData.append('item_id', {{ $selectedItem->id }});
                                    formData.append('periodo_id', {{ $horario->materiaPeriodo->periodo_id }});

                                    fetch('{{ route('alumnos.uploadArchivoRespuesta') }}', {
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
                                        } else {
                                            this.errorMsg = data?.errors?.archivo?.[0] || data.message || 'Error al subir el archivo.';
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
                                    $wire.call('eliminarArchivo');
                                }
                            }">
                                <label class="form-label fw-semibold">Archivo adjunto (opcional):</label>

                                <template x-if="!$wire.nombreArchivoSubido">
                                    <div>
                                        <input type="file" class="form-control" x-ref="archivoInput" :disabled="subiendo" @change="subirArchivo">

                                        {{-- Separar x-show de d-flex para que Bootstrap !important no lo deje fijo --}}
                                        <div x-show="subiendo" x-cloak class="mt-2">
                                            <div class="small text-primary d-flex align-items-center gap-2">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                <span>Subiendo archivo...</span>
                                            </div>
                                        </div>

                                        <div x-show="errorMsg" x-cloak class="small text-danger mt-2" x-text="errorMsg"></div>
                                    </div>
                                </template>

                                <template x-if="$wire.nombreArchivoSubido">
                                    <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 p-2 px-3 mb-0">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <i class="ti ti-circle-check fs-4 flex-shrink-0"></i>
                                            <div class="text-truncate">
                                                <strong>Archivo adjunto:</strong>
                                                <span x-text="$wire.nombreArchivoSubido"></span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close flex-shrink-0" @click="eliminarArchivoLocal" aria-label="Quitar archivo"></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="modal-footer border-top">
                            <button wire:click="$set('showModal', false)" type="button" class="btn btn-outline-secondary rounded-pill">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <span wire:loading wire:target="guardarRespuesta" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Guardar respuesta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
