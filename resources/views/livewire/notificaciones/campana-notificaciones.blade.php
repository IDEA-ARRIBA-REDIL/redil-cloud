<div wire:poll.30s="actualizarConteo">
    {{-- Campana de Notificaciones --}}
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
        <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
            href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
            aria-expanded="false" wire:click="abrirDropdown">
            <span class="position-relative">
                <i class="ti ti-bell ti-md"></i>
                @if ($conteoNoLeidas > 0)
                    <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                @endif
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width: 22rem;">
            {{-- Cabecera --}}
            <li class="dropdown-menu-header border-bottom">
                <div class="dropdown-header d-flex align-items-center py-3">
                    <h6 class="mb-0 me-auto fw-bold">Notificaciones</h6>
                    @if ($conteoNoLeidas > 0)
                        <span class="badge rounded-pill bg-label-primary">
                            {{ $conteoNoLeidas }} nueva{{ $conteoNoLeidas > 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>
            </li>

            {{-- Lista de notificaciones --}}
            <li class="dropdown-notifications-list scrollable-container" style="max-height: 20rem; overflow-y: auto;">
                <ul class="list-group list-group-flush">
                    @forelse ($notificaciones as $notif)
                        <li class="list-group-item list-group-item-action dropdown-notifications-item {{ !$notif['leida'] ? '' : 'mark-as-read' }}"
                            wire:click="marcarComoLeida('{{ $notif['id'] }}')"
                            style="cursor: pointer;">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $notif['color'] }}">
                                            <i class="ti {{ $notif['icono'] }} ti-md"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="small mb-0 {{ !$notif['leida'] ? 'fw-bold' : 'text-muted' }}">
                                        {{ $notif['titulo'] }}
                                    </h6>
                                    <small class="mb-1 d-inline-block text-truncate" style="max-width: 15rem;">
                                        {{ $notif['mensaje'] }}
                                    </small>
                                    <small class="text-muted d-block">{{ $notif['tiempo'] }}</small>
                                </div>
                                @if (!$notif['leida'])
                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                        <span class="badge badge-dot bg-primary"></span>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item">
                            <div class="text-center py-4">
                                <i class="ti ti-bell-off ti-xl text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-0">No tienes notificaciones</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </li>

            {{-- Footer --}}
            <li class="dropdown-menu-footer border-top p-3">
                <div class="d-flex justify-content-between align-items-center">
                    @if ($conteoNoLeidas > 0)
                        <button class="btn btn-sm btn-outline-secondary rounded-pill"
                            wire:click="marcarTodasComoLeidas">
                            <i class="ti ti-checks me-1"></i>Marcar todas como leídas
                        </button>
                    @else
                        <span></span>
                    @endif
                    <a href="{{ route('notificaciones.lista') }}" class="btn btn-sm btn-primary rounded-pill">
                        Ver todas <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            </li>
        </ul>
    </li>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // Sincronización inicial al cargar la plataforma
            const initialCount = {{ $conteoNoLeidas }};
            if ('setAppBadge' in navigator && initialCount > 0) {
                navigator.setAppBadge(initialCount).catch(e => console.error('Error Badge Inicial', e));
            } else if ('clearAppBadge' in navigator && initialCount === 0) {
                navigator.clearAppBadge().catch(e => console.error('Error Badge Inicial', e));
            }

            // Escuchar el evento de actualización de Livewire y sincronizar con PWA
            window.addEventListener('AppBadgeUpdated', (event) => {
                // Livewire v3 suele mandar el dato de una forma directa, pero verificamos ambos casos
                let unreadCount = 0;
                if (event.detail && typeof event.detail.count !== 'undefined') {
                    unreadCount = event.detail.count;
                } else if (event.detail && event.detail[0] && typeof event.detail[0].count !== 'undefined') {
                    unreadCount = event.detail[0].count;
                }
                
                if (unreadCount > 0) {
                    if ('setAppBadge' in navigator) {
                        navigator.setAppBadge(unreadCount).catch((error) => {
                            console.error('Error al actualizar el Badge de la PWA:', error);
                        });
                    }
                } else {
                    if ('clearAppBadge' in navigator) {
                        navigator.clearAppBadge().catch((error) => {
                            console.error('Error al limpiar el Badge de la PWA:', error);
                        });
                    }
                }
            });
        });
    </script>
    @endpush
</div>
