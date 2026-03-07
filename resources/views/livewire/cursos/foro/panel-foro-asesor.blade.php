<div>
    <h4 class="fw-semibold  text-primary py-3 mb-4">
        Panel de foro
    </h4>

    {{-- Filtros Superiores --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row gx-3 gy-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label mb-1">Buscar por título / contenido</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.500ms="searchTitle" class="form-control"
                            placeholder="Ej. error en video..." />
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">Filtrar por curso</label>
                    <div wire:ignore>
                        <select class="form-select select2" id="filtroCursoId">
                            <option value="">Todos los cursos</option>
                            @foreach ($cursosFiltro as $cursoF)
                                <option value="{{ $cursoF->id }}">{{ $cursoF->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">Estado de la pregunta</label>
                    <div wire:ignore>
                        <select class="form-select select2" id="filtroEstado">
                            <option value="todos">Todos los estados</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="resuelto">Resueltos</option>
                            <option value="cerrado">Cerrados</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Hilos (Tarjetas) --}}
    <div class="row">
        @forelse($hilosList as $hilo)
            <div class="col-12 mb-3" wire:key="hilo-{{ $hilo->id }}">
                <div
                    class="card shadow-sm border-0 h-100 {{ $hilo->estado === 'pendiente' ? 'border-start border-warning border-3' : '' }}">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9 mb-3 mb-md-0">
                                <h6 class="fw-bold mb-1 text-primary">{{ $hilo->titulo }}</h6>
                                <p class="text-black mb-2 text-truncate" style="max-width: 100%;">
                                    {{ mb_strimwidth($hilo->cuerpo, 0, 150, '...') }}</p>

                                <div class="d-flex align-items-center flex-wrap gap-2">

                                    @if ($hilo->user->foto && !in_array($hilo->user->foto, ['default-m.png', 'default-f.png']))
                                        <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $hilo->user->foto) }}"
                                            alt="{{ $hilo->user->nombre(3) }}"
                                            class="avatar-initial rounded-circle border border-2 border-white bg-light object-fit-cover" style="width: 30px; height: 30px;">
                                    @else
                                        <span
                                            class="avatar-initial p-2 rounded-circle bg-primary bg-opacity-10 text-primary fw-bold" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">{{ $hilo->user->inicialesNombre() }}</span>
                                    @endif
                                    <span class="text-black small">{{ $hilo->user->nombre(3) }}</span>

                                    <span class="text-black small ms-md-2"><i class="ti ti-clock me-1"></i>
                                        {{ $hilo->created_at->format('d M. Y, h:i A') }}</span>
                                    <span class="text-black small ms-md-2"><i class="ti ti-book me-1"></i> Curso:
                                        {{ mb_strimwidth($hilo->curso->nombre ?? 'N/A', 0, 40, '...') }}</span>
                                </div>
                            </div>

                            <div class="col-md-3 text-md-end">
                                <div class="mb-2 d-flex justify-content-md-end align-items-center">
                                    @if ($hilo->estado === 'pendiente')
                                        <span class="badge bg-label-warning text-lowercase">pendiente</span>
                                    @elseif($hilo->estado === 'resuelto')
                                        <span class="badge bg-label-success text-lowercase">resuelto</span>
                                    @else
                                        <span class="badge bg-label-secondary text-lowercase">cerrado</span>
                                    @endif

                                    <span class="btn btn-outline-primary ms-1 p-1 px-2" style="font-size: 0.8rem;"><i class="ti ti-messages me-1"></i>
                                        {{ $hilo->respuestas->count() }}</span>
                                </div>

                                <button class="btn btn-primary rounded-pill waves-effect waves-light btn-sm"
                                    wire:click="abrirHilo({{ $hilo->id }})" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="abrirHilo({{ $hilo->id }})">Responder / Ver</span>
                                    <span wire:loading wire:target="abrirHilo({{ $hilo->id }})"><i class="ti ti-loader ti-spin"></i> Cargando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="ti ti-checkups fs-1 text-black mb-3 d-block"></i>
                <h5 class="text-black">No se encontraron hilos de foro.</h5>
                <p class="text-black">¡Todo está bajo control!</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $hilosList->links() }}
    </div>


    {{-- --------------------------------------
       OFFCANVAS: MODERACIÓN Y RESPUESTA
    -------------------------------------- --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHiloDetalle" aria-labelledby="offcanvasHiloLabel"
        wire:ignore.self data-bs-focus="false" data-bs-backdrop="false" style="width: 500px !important; z-index: 2050;">
        <div class="offcanvas-header btn-primary">
            <h5 id="offcanvasHiloLabel" class="offcanvas-title text-white">Conversación</h5>
            <button type="button" class="btn-close text-reset text-white" data-bs-dismiss="offcanvas"
                aria-label="Close"> <i class="ti ti-x text-white"></i></button>
        </div>

        <div class="offcanvas-body h-100 d-flex flex-column pb-1">

            <div wire:key="success-container" style="min-height: 50px;">
                @if (session()->has('successHilo'))
                    <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
                        {{ session('successHilo') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            @if ($hiloActivo)
                {{-- Info cabecera (La pregunta del alumno) --}}
                <div class="mb-3" wire:key="cabecera-hilo-{{ $hiloActivo->id }}">
                    <h5 class="fw-bold mb-1 text-primary">{{ $hiloActivo->titulo }}</h5>
                    <div class="mb-2">
                        <span class="btn  btn-secondary  me-1">{{ $hiloActivo->user->nombre(3) }}</span>
                        <small class="text-black">{{ $hiloActivo->created_at->format('d/m/Y h:i') }}</small>
                    </div>

                    @if ($hiloActivo->item)
                        <div class="mb-2">
                            <span class="btn btn-outline-secondary"><i class="ti ti-book me-1"></i>Módulo:
                                {{ $hiloActivo->item->titulo }}</span>
                        </div>
                    @endif

                    <div class="border border-light p-3 rounded-3 text-black mb-3">
                        {{ $hiloActivo->cuerpo }}
                    </div>

                    {{-- Herramientas de Estado Activo --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold text-black">Estado actual:</span>
                        <div class="btn-group" role="group">
                            <button type="button"
                                class="btn btn-xs {{ $hiloActivo->estado === 'pendiente' ? 'btn-warning' : 'btn-outline-warning' }}"
                                wire:click="cambiarEstadoHilo('pendiente')" wire:loading.class="disabled" wire:target="cambiarEstadoHilo">Pendiente</button>
                            <button type="button"
                                class="btn btn-xs {{ $hiloActivo->estado === 'resuelto' ? 'btn-success' : 'btn-outline-success' }}"
                                wire:click="cambiarEstadoHilo('resuelto')" wire:loading.class="disabled" wire:target="cambiarEstadoHilo">Resuelto</button>
                            <button type="button"
                                class="btn btn-xs {{ $hiloActivo->estado === 'cerrado' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                wire:click="cambiarEstadoHilo('cerrado')" wire:loading.class="disabled" wire:target="cambiarEstadoHilo">Cerrado</button>
                        </div>
                    </div>
                </div>

                <hr class="mt-0">

                {{-- Hilo de Mensajes --}}
                <div class="flex-grow-1 overflow-auto pe-2 mb-3" style="max-height: 50vh;">
                    @forelse($hiloActivo->respuestas as $respuesta)
                        <div wire:key="respuesta-{{ $respuesta->id }}"
                            class="mb-3 d-flex flex-column {{ $respuesta->user_id === $hiloActivo->user_id ? 'align-items-start' : 'align-items-end' }}">

                            <div class="d-flex flex-column {{ $respuesta->user_id === $hiloActivo->user_id ? 'align-items-start' : 'align-items-end' }}"
                                style="max-width: 85%;">
                                <div class="mb-1 d-flex align-items-center">
                                    <small class="btn  btn-outline-secondary "
                                        style="font-size: 0.65rem;">{{ $respuesta->user->nombre(3) }}</small>
                                    @if ($respuesta->user_id === $hiloActivo->user_id)
                                        <span class="btn  btn-outline-secondary ms-2 "
                                            style="font-size: 0.6rem;">Autor</span>
                                    @endif
                                    @if ($respuesta->es_respuesta_oficial)
                                        <span class="badge btn-primary text-white py-1 px-2"
                                            style="font-size: 0.6rem;"><i class="ti ti-star"></i> Oficial</span>
                                    @endif
                                </div>

                                <div
                                    class="p-2 rounded-3 shadow-sm {{ $respuesta->es_respuesta_oficial ? 'btn-outline-primary text-white' : 'bg-white border' }}">
                                    <p class="mb-0 text-break text-black" style="font-size: 0.85rem;">{{ $respuesta->cuerpo }}
                                    </p>
                                </div>
                                <small class="text-black mt-1"
                                    style="font-size: 0.65rem;">{{ $respuesta->created_at->diffForHumans() }}</small>
                            </div>

                        </div>
                    @empty
                        <div class="text-center text-black py-4 bg-label-secondary rounded-3" wire:key="sin-respuestas">
                            <span class="text-black">Aún no hay respuestas en este hilo.</span>
                        </div>
                    @endforelse
                </div>

                {{-- Formulario para Responder --}}
                <div class="mt-auto" wire:key="form-respuesta-hilo-{{ $hiloActivo->id }}">
                    @if ($hiloActivo->estado !== 'cerrado')
                        <form wire:submit="enviarRespuestaOficial">
                            <div class="input-group" wire:ignore.self>
                                <textarea class="form-control" rows="2" wire:model="respuestaAsesorCuerpo"
                                    id="inputRespuestaAsesor"
                                    placeholder="Responder como asesor oficial..."></textarea>
                                <button class="btn btn-primary" type="submit" wire:loading.class="disabled" wire:target="enviarRespuestaOficial">
                                    <span wire:loading.remove wire:target="enviarRespuestaOficial"><i class="ti ti-send"></i></span>
                                    <span wire:loading wire:target="enviarRespuestaOficial"><i class="ti ti-loader ti-spin"></i></span>
                                </button>
                            </div>
                            @error('respuestaAsesorCuerpo')
                                <span class="text-danger small ms-1">{{ $message }}</span>
                            @enderror
                        </form>
                    @else
                        <div class="alert alert-secondary py-2 m-0 text-center">
                            <i class="ti ti-lock me-1"></i> Este hilo está cerrado.
                        </div>
                    @endif
                </div>
            @else
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Backdrop manual para evitar conflictos con Livewire --}}
    <div id="custom-offcanvas-backdrop" class="offcanvas-backdrop fade" style="display: none; z-index: 2040;" wire:ignore></div>

    @script
    <script>
        // Sync Select2 changes to Livewire
        $('#filtroCursoId').on('change', function (e) {
            $wire.set('filtroCursoId', $(this).val());
        });

        $('#filtroEstado').on('change', function (e) {
            $wire.set('filtroEstado', $(this).val());
        });

        $wire.on('abrir-offcanvas-foro', () => {
            let elemento = document.getElementById('offcanvasHiloDetalle');
            let backdrop = document.getElementById('custom-offcanvas-backdrop');

            let instancia = bootstrap.Offcanvas.getInstance(elemento);
            if (!instancia) {
                instancia = new bootstrap.Offcanvas(elemento);
            }

            // Mostrar custom backdrop
            backdrop.style.display = 'block';
            setTimeout(() => { backdrop.classList.add('show'); }, 10);

            instancia.show();
        });

        // Ocultar custom backdrop cuando se cierra el offcanvas manualmente (por el boton X o ESC)
        document.getElementById('offcanvasHiloDetalle').addEventListener('hide.bs.offcanvas', function () {
            let backdrop = document.getElementById('custom-offcanvas-backdrop');
            backdrop.classList.remove('show');
            setTimeout(() => { backdrop.style.display = 'none'; }, 300);
        });

        // Cerrar al hacer click en el custom backdrop
        document.getElementById('custom-offcanvas-backdrop').addEventListener('click', function () {
            let elemento = document.getElementById('offcanvasHiloDetalle');
            let instancia = bootstrap.Offcanvas.getInstance(elemento);
            if(instancia) {
                instancia.hide();
            }
        });
    </script>
    @endscript
</div>
