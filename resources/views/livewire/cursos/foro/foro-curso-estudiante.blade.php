<div>
    @php
        $configuracion = \App\Models\Configuracion::find(1);
    @endphp
    {{-- MENSAJES DE ALERTA --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- --------------------------------------
       VISTA 1: LISTADO DE PREGUNTAS
    -------------------------------------- --}}
    @if ($vistaActual === 'lista')
        <div class="d-flex justify-content-between align-items-center mb-4">

            <button class="btn btn-outline-primary mt-5" wire:click="abrirFormularioNuevaPregunta">
                <i class="ti ti-plus me-1"></i> Nueva pregunta
            </button>
        </div>

        {{-- Filtros y Buscador --}}
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div class="input-group" style="max-width: 400px;">
                <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                <input wire:model.live.debounce.500ms="search" type="text" class="form-control"
                    placeholder="Buscar en el foro...">
            </div>

            <div class="form-check form-switch d-flex align-items-center mb-0">
                <input wire:model.live="filtroMisPreguntas" class="form-check-input mt-0 me-2" type="checkbox"
                    id="filtroMisPreguntasSwitch" style="cursor:pointer">
                <label class="form-check-label mb-0" for="filtroMisPreguntasSwitch" style="cursor:pointer">Mostrar solo
                    mis preguntas</label>
            </div>
        </div>

        {{-- Listado de Hilos --}}
        <div class="lista-hilos">
            @forelse($hilos as $h)
                <div class="card shadow-none border mb-3 p-3 rounded-3" style="cursor: pointer; transition: all 0.2s;"
                    wire:click="verHilo({{ $h->id }})" onmouseover="this.style.backgroundColor='#f8f9fa'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center mb-2">
                            {{-- Avatar del usuario --}}
                            <div class="avatar avatar-sm me-3">
                                @if($h->user->foto && !in_array($h->user->foto, ["default-m.png", "default-f.png"]))
                                    <img src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$h->user->foto) }}" alt="{{ $h->user->nombre(3) }}" class="avatar-initial rounded-circle border border-2 border-white bg-light object-fit-cover">
                                @else
                                    <span class="avatar-initial rounded-circle bg-primary bg-opacity-10 text-primary fw-bold">{{ $h->user->inicialesNombre() }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="mb-0 text-dark fw-bold" style="font-size: 0.95rem;">{{ $h->user->nombre(3) }}</p>
                                <small class="text-muted"
                                    style="font-size: 0.75rem;">{{ $h->created_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        {{-- Badges de Estado --}}
                        <div>
                            @if ($h->estado === 'pendiente')
                                <span class="badge bg-label-warning text-lowercase">pendiente</span>
                            @elseif($h->estado === 'resuelto')
                                <span class="badge bg-label-success text-lowercase">resuelto</span>
                            @else
                                <span class="badge bg-label-secondary text-lowercase">cerrado</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 ms-5">
                        <h6 class="fw-bold text-dark mb-1">{{ $h->titulo }}</h6>
                        <p class="text-black mb-2 text-truncate" style="max-width: 100%;">{{ $h->cuerpo }}</p>

                        <div class="d-flex align-items-center mt-2">
                            <i class="ti ti-messages text-muted me-1"></i>
                            <span class="text-muted small">{{ $h->respuestas->count() }} respuestas</span>

                            @if ($h->item)
                                <span class="mx-2 text-muted">•</span>
                                <i class="ti ti-book text-muted me-1"></i>
                                <span class="text-muted small">Módulo: {{ $h->item->titulo }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 border rounded-3 bg-light">
                    <i class="ti ti-messages fs-1 text-muted mb-3 d-block"></i>
                    <h6 class="text-muted">No hay preguntas en el foro todavía.</h6>
                    <p class="text-muted small">Sé el primero en iniciar una conversación.</p>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $hilos->links() }}
            </div>
        </div>
    @endif


    {{-- --------------------------------------
       VISTA 2: CREAR NUEVA PREGUNTA
    -------------------------------------- --}}
    @if ($vistaActual === 'crear')
        <div class="card shadow-none border rounded-3 p-4">
            <h5 class="fw-bold mb-4">Haz una nueva pregunta al foro</h5>

            <form wire:submit.prevent="guardarNuevaPregunta">
                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Título o resumen de tu duda</label>
                    <input type="text" class="form-control" wire:model.defer="nuevaPreguntaTitulo"
                        placeholder="Ej. Duda sobre configuración del sistema principal...">
                    @error('nuevaPreguntaTitulo')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Detalle tu pregunta</label>
                    <textarea class="form-control" rows="5" wire:model.defer="nuevaPreguntaCuerpo"
                        placeholder="Explica detalladamente qué intentabas hacer y dónde tuviste problemas..."></textarea>
                    @error('nuevaPreguntaCuerpo')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Selector Opcional del Módulo --}}
                <div class="mb-4">
                    <label class="form-label text-dark fw-bold">¿En qué parte del curso tienes la duda?</label>
                    <select class="form-select" wire:model.defer="moduloItemAsociadoId">
                        <option value="">Pregunta general del curso (Aplica a todo)</option>
                        @foreach ($curso->modulos as $modulo)
                            <optgroup label="Módulo: {{ $modulo->titulo }}">
                                @foreach ($modulo->items as $item)
                                    @if($item->tipo && !in_array($item->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                                        <option value="{{ $item->id }}">{{ $item->titulo }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="volverALista">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i> Publicar
                       </button>
                </div>
            </form>
        </div>
    @endif


    {{-- --------------------------------------
       VISTA 3: DETALLE DEL HILO (CHAT)
    -------------------------------------- --}}
    @if ($vistaActual === 'detalle_hilo' && $hiloDetalle)
        <div class="hilo-detalle">
            <button class="btn btn-sm btn-outline-secondary mb-3" wire:click="volverALista">
                <i class="ti ti-arrow-left me-1"></i> Volver al foro
            </button>

            {{-- Pregunta Principal (Raíz) --}}
            <div class="card shadow-sm border-0 mb-4 p-4" style="background-color: #f8f9fa;">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ $hiloDetalle->titulo }}</h5>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-primary rounded-pill me-2">{{ $hiloDetalle->user->name }}</span>
                            <small class="text-muted">{{ $hiloDetalle->created_at->format('d M. Y, h:i A') }}</small>
                            @if ($hiloDetalle->item)
                                <span class="mx-2 text-muted">-</span>
                                <span class="text-muted small"><i class="ti ti-book text-muted me-1"></i> Módulo:
                                    {{ $hiloDetalle->item->titulo }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if ($hiloDetalle->estado === 'pendiente')
                            <span class="badge bg-label-warning px-3 py-2 text-lowercase">Pendiente</span>
                        @elseif($hiloDetalle->estado === 'resuelto')
                            <span class="badge bg-label-success px-3 py-2 text-lowercase">Resuelto</span>
                        @else
                            <span class="badge bg-label-secondary px-3 py-2 text-lowercase">Cerrado</span>
                        @endif
                    </div>
                </div>
                <hr>
                <p class="text-dark">{{ $hiloDetalle->cuerpo }}</p>
            </div>

            {{-- Respuestas Cronológicas --}}
            <h6 class="fw-bold mb-3 ms-2 text-muted">Respuestas ({{ $hiloDetalle->respuestas->count() }})</h6>

            <div class="respuestas-container mb-4 ms-2 ms-md-5">
                @foreach ($hiloDetalle->respuestas as $respuesta)
                    <div
                        class="card shadow-none border mb-3 {{ $respuesta->es_respuesta_oficial ? 'border-primary' : '' }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        @if($respuesta->user->foto && !in_array($respuesta->user->foto, ["default-m.png", "default-f.png"]))
                                            <img src="{{ Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$respuesta->user->foto) }}" alt="{{ $respuesta->user->nombre(3) }}" class="avatar-initial rounded-circle border border-2 border-white bg-light object-fit-cover">
                                        @else
                                            <span class="avatar-initial rounded-circle {{ $respuesta->es_respuesta_oficial ? 'bg-primary text-white' : 'bg-primary bg-opacity-10 text-primary' }} fw-bold" style="font-size: 0.60rem;">{{ $respuesta->user->inicialesNombre() }}</span>
                                        @endif
                                    </div>
                                    <span class="fw-bold text-dark small">{{ $respuesta->user->name }}</span>

                                    @if ($respuesta->user_id === $hiloDetalle->user_id)
                                        <span class="badge bg-label-dark ms-2"
                                            style="font-size: 0.65rem;">Autor</span>
                                    @endif
                                    @if ($respuesta->es_respuesta_oficial)
                                        <span class="badge bg-primary ms-2" style="font-size: 0.65rem;"><i
                                                class="ti ti-star me-1" style="font-size: 0.65rem;"></i>Asesor
                                            oficial</span>
                                    @endif
                                </div>
                                <small class="text-muted"
                                    style="font-size: 0.70rem;">{{ $respuesta->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 text-dark small ms-4 ps-2">{{ $respuesta->cuerpo }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Caja de Respuesta (Solo si no está cerrado) --}}
            @if ($hiloDetalle->estado !== 'cerrado')
                <div class="card shadow-none border mt-3 ms-2 ms-md-5">
                    <div class="card-body p-3">
                        <form wire:submit.prevent="guardarRespuesta">
                            <div class="mb-2">
                                <textarea class="form-control border-0 bg-light" rows="3" wire:model.defer="nuevaRespuestaCuerpo"
                                    placeholder="Escribe tu respuesta para enriquecer la comunidad..."></textarea>
                                @error('nuevaRespuestaCuerpo')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send me-1"></i>
                                    Responder</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-secondary ms-2 ms-md-5">
                    <i class="ti ti-lock me-1"></i> Esta conversación ha sido cerrada y ya no admite nuevas respuestas.
                </div>
            @endif

        </div>
    @endif
</div>
