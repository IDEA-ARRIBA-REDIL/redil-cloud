@php
    $configData = Helper::appClasses();
@endphp

@section('title', 'Mis Notificaciones')

<div>
    <div class="row">
        <div class="col-12">
            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="ti ti-bell me-2"></i>Mis Notificaciones
                    </h4>
                    <p class="text-muted mb-0">
                        @if ($conteoNoLeidas > 0)
                            Tienes <span class="fw-bold text-primary">{{ $conteoNoLeidas }}</span>
                            notificación{{ $conteoNoLeidas > 1 ? 'es' : '' }} sin leer
                        @else
                            Estás al día con tus notificaciones
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @if ($conteoNoLeidas > 0)
                        <button class="btn btn-sm btn-outline-primary rounded-pill"
                            wire:click="marcarTodasComoLeidas">
                            <i class="ti ti-checks me-1"></i>Marcar todas como leídas
                        </button>
                    @endif
                </div>
            </div>

            {{-- Filtros --}}
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <button type="button"
                        class="btn btn-sm rounded-pill me-1 {{ $filtro === 'todas' ? 'btn-primary' : 'btn-outline-secondary' }}"
                        wire:click="cambiarFiltro('todas')">
                        Todas
                    </button>
                    <button type="button"
                        class="btn btn-sm rounded-pill me-1 {{ $filtro === 'no-leidas' ? 'btn-primary' : 'btn-outline-secondary' }}"
                        wire:click="cambiarFiltro('no-leidas')">
                        <i class="ti ti-point-filled me-1"></i>No leídas
                    </button>
                    <button type="button"
                        class="btn btn-sm rounded-pill {{ $filtro === 'leidas' ? 'btn-primary' : 'btn-outline-secondary' }}"
                        wire:click="cambiarFiltro('leidas')">
                        Leídas
                    </button>
                </div>
            </div>

            {{-- Lista de Notificaciones --}}
            <div class="card" style="border: 1px dashed #d4d4d4;">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($notificaciones as $notificacion)
                            @php
                                $datos = $notificacion->data;
                                $esLeida = $notificacion->read_at !== null;
                            @endphp
                            <li class="list-group-item py-3 {{ !$esLeida ? 'bg-label-primary bg-opacity-10' : '' }}">
                                <div class="d-flex align-items-start">
                                    {{-- Icono --}}
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span
                                                class="avatar-initial rounded-circle bg-label-{{ $datos['color'] ?? 'primary' }}">
                                                <i class="ti {{ $datos['icono'] ?? 'ti-bell' }} ti-md"></i>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Contenido --}}
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 {{ !$esLeida ? 'fw-bold' : 'text-muted' }}">
                                                    {{ $datos['titulo'] ?? 'Notificación' }}
                                                </h6>
                                                <p class="mb-1 text-body">
                                                    {{ $datos['mensaje'] ?? '' }}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="ti ti-clock me-1"></i>
                                                    {{ $notificacion->created_at->diffForHumans() }}
                                                </small>
                                            </div>

                                            {{-- Acciones --}}
                                            <div class="d-flex gap-1 ms-2">
                                                @if (!$esLeida)
                                                    <button
                                                        class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                                                        wire:click="marcarComoLeida('{{ $notificacion->id }}')"
                                                        title="Marcar como leída">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                @endif
                                                <button
                                                    class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                                                    wire:click="eliminar('{{ $notificacion->id }}')"
                                                    wire:confirm="¿Eliminar esta notificación?"
                                                    title="Eliminar">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">
                                <div class="text-center py-5">
                                    <i class="ti ti-bell-off" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-3 mb-0">
                                        @if ($filtro === 'no-leidas')
                                            No tienes notificaciones sin leer
                                        @elseif ($filtro === 'leidas')
                                            No tienes notificaciones leídas
                                        @else
                                            Aún no tienes notificaciones
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Paginación --}}
            <div class="mt-3">
                {{ $notificaciones->links() }}
            </div>
        </div>
    </div>
</div>
