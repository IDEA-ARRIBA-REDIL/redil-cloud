<div>
    <!-- Contenido Cabezote -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Listado de Informes Personalizados</h4>
                    <p class="text-muted mb-0">Aquí encontrarás todos los informes disponibles y podrás asignarles permisos.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="card">
        <div class="card-body">
            @if($informes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Acciones</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Descripción</th>
                                <th>Tipos de Usuario</th>
                                <th>Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($informes as $informe)
                                <tr>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" wire:click="openModal({{ $informe->id }})" title="Gestionar Tipos de Usuarios">
                                            <i class="ti ti-users"></i>
                                        </button>
                                        <button type="button" class="btn {{ $informe->activo ? 'btn-success' : 'btn-secondary' }} btn-sm" wire:click="toggleActivo({{ $informe->id }})" title="Cambiar Estado">
                                            <i class="ti {{ $informe->activo ? 'ti-eye' : 'ti-eye-off' }}"></i>
                                        </button>
                                    </td>
                                    <td><strong>{{ $informe->nombre }}</strong></td>
                                    <td>
                                        @if($informe->activo)
                                            <span class="badge bg-label-success">Activo</span>
                                        @else
                                            <span class="badge bg-label-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $informe->descripcion }}</td>
                                    <td>
                                        @if($informe->tiposUsuarios->count() > 0)
                                            <span class="badge bg-label-info">{{ $informe->tiposUsuarios->count() }} roles asignados</span>
                                        @else
                                            <span class="badge bg-label-warning">Ninguno</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($informe->activo)
                                            <a href="{{ $informe->add_id_a_la_url ? url($informe->link.'/'.$informe->id) : url($informe->link) }}" class="btn btn-info btn-sm">
                                                {{ $informe->nombre_boton ?? 'Ver' }}
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary btn-sm" disabled>{{ $informe->nombre_boton ?? 'Ver' }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ti ti-file-analytics display-4 text-muted mb-3"></i>
                    <h4>Por el momento no tienes ningún informe habilitado.</h4>
                    <p class="text-muted">Vuelve más tarde o contacta con soporte.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Gestionar Tipo Usuarios -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="ti ti-users me-2"></i>
                        Gestionar tipos de usuarios: {{ $informeSeleccionado->nombre }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Selecciona qué tipos de usuarios (roles) podrán ver y generar este informe.</p>

                    <div class="mb-3">
                        <label class="form-label">Tipos de Usuario</label>
                        <div class="row">
                            @foreach($tiposUsuarios as $tipo)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="tipo_{{ $tipo->id }}" value="{{ $tipo->id }}" wire:model="tiposUsuariosSeleccionados">
                                        <label class="form-check-label" for="tipo_{{ $tipo->id }}">{{ $tipo->nombre }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarTiposUsuarios">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
