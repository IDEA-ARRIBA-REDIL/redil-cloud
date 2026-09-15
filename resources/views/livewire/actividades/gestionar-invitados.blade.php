<div wire:poll.15s.visible="cargarInvitados">
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <div class="container my-5">
        {{-- Bloque Informativo Principal --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title text-primary">{{ $inscripcionPrincipal->categoriaActividad->actividad->nombre }}</h4>
                <p class="text-black"><strong>Participante Principal:</strong> {{ $inscripcionPrincipal->user ? $inscripcionPrincipal->user->nombre(3) : ($inscripcionPrincipal->nombre_inscrito ?? ($inscripcionPrincipal->compra->nombre_completo_comprador ?? 'Participante')) }}</p>
                <p class="mb-0 text-black"><strong>Cupos de Invitados Aprobados:</strong> {{ $inscripcionPrincipal->limite_invitados }} | <strong>Disponibles:</strong> {{ $cuposDisponibles }}</p>
            </div>
        </div>

        {{-- Sección de Gestión de Invitados --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestionar invitados</h5>
                @if($cuposDisponibles > 0)
                <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalAnadirInvitado">
                    <i class="ti ti-plus me-1"></i> Añadir invitado
                </button>
                @endif
            </div>
            <div class="card-body">
                @if($invitados->isEmpty())
                <p class="text-muted text-center">Aún no has registrado a ningún invitado.</p>
                @else
                <div class="list-group">
                    @foreach($invitados as $invitado)
                    <div class="list-group-item justify-content-between align-items-center">
                        <div class="row">

                            <div class="col-xs-12 col-lg-6">
                                <div class="fw-semibold">{{ $invitado->nombre_inscrito }}</div>
                                <small class="text-muted">{{ $invitado->email }}</small>
                            </div>
                            <div class="col-xs-12 col-lg-6 text-end">
                                {{-- Botón para descargar QR en PDF --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary my-3 rounded-pill" title="Descargar Código QR en PDF" wire:click="descargarQrInvitado({{ $invitado->id }})" wire:loading.attr="disabled" wire:target="descargarQrInvitado({{ $invitado->id }})">
                                    <span wire:loading.remove wire:target="descargarQrInvitado({{ $invitado->id }})">
                                        <i class="ti ti-qrcode me-1"></i> Descargar QR (PDF)
                                    </span>
                                    <span wire:loading wire:target="descargarQrInvitado({{ $invitado->id }})">
                                        <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Generando PDF...
                                    </span>
                                </button>

                                {{-- Botón de Eliminar (sin cambios) --}}
                                <button class="btn btn-sm btn-outline-danger my-3" title="Eliminar Invitado" wire:click="eliminarInvitado({{ $invitado->id }})" wire:confirm="¿Estás seguro de que quieres eliminar a este invitado?">
                                    <i class="ti ti-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="modal fade" id="modalAnadirInvitado" tabindex="-1" aria-hidden="true" wire:ignore.self x-data x-on:cerrar-modal-invitado.window="bootstrap.Modal.getInstance(document.getElementById('modalAnadirInvitado')).hide()">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Añadir nuevo invitado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombreNuevoInvitado" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="nombreNuevoInvitado" wire:model="nombreNuevoInvitado" placeholder="Nombre del invitado">
                            @error('nombreNuevoInvitado') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3 ">
                            <label for="emailNuevoInvitado" class="form-label">Email</label>
                            <input type="email"
                                class="form-control"
                                id="emailNuevoInvitado"
                                wire:model="emailNuevoInvitado"
                                placeholder="correo@ejemplo.com"
                                autocomplete="off"
                                oncopy="return false;"
                                oncut="return false;"
                                ondragstart="return false;"
                                @copy.prevent
                                @cut.prevent>
                            @error('emailNuevoInvitado') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="emailConfirmacionNuevoInvitado" class="form-label">Confirmar email</label>
                            <input type="email"
                                class="form-control"
                                id="emailConfirmacionNuevoInvitado"
                                wire:model="emailConfirmacionNuevoInvitado"
                                placeholder="correo@ejemplo.com"
                                autocomplete="off"
                                onpaste="return false;"
                                oncopy="return false;"
                                oncut="return false;"
                                ondrop="return false;"
                                @paste.prevent
                                @drop.prevent>
                            <small class="text-muted d-block mt-1">
                                <i class="ti ti-info-circle me-1"></i>Por seguridad, escribe el correo manualmente (no se permite pegar).
                            </small>
                            @error('emailConfirmacionNuevoInvitado') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-pill mt-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary rounded-pill mt-3" wire:click="guardarInvitado" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="guardarInvitado">Guardar inscripción</span>
                            <span wire:loading wire:target="guardarInvitado">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
