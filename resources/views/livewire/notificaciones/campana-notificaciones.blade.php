<div wire:poll.30s="actualizarConteo">
    {{-- Campana de Notificaciones --}}
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2" wire:ignore.self>
        <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
            href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
            aria-expanded="false" id="campanaNotificacionesBtn">
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

            // Función centralizada para aplicar el badge de manera segura
            const triggerBadge = async (count) => {
                if (!('setAppBadge' in navigator && 'clearAppBadge' in navigator)) {
                    console.log('App Badging API no soportado por este dispositivo o no instalado como App.');
                    return;
                }
                try {
                    if (count > 0) {
                        await navigator.setAppBadge(count);
                        console.log('Badge colocado:', count);
                    } else {
                        await navigator.clearAppBadge();
                        console.log('Badge limpiado.');
                    }
                } catch (error) {
                    console.error('Error aplicando Badge (quizás sin permisos):', error);
                }
            };

            // Solicitud de permisos obligatorios (iOS y Android moderno) al interactuar con la campana
            const btnCampana = document.getElementById('campanaNotificacionesBtn');
            if(btnCampana) {
                btnCampana.addEventListener('click', async () => {
                    if ('Notification' in window && Notification.permission !== 'granted') {
                        const perm = await Notification.requestPermission();
                        console.log('Permisos configurados como:', perm);
                        // Re-aplicar el badge si obtuvimos el permiso en este clic
                        if(perm === 'granted'){
                            triggerBadge({{ $conteoNoLeidas }});
                        }
                    }
                });
            }

            // Sincronización inicial rápida
            triggerBadge({{ $conteoNoLeidas }});

            // Sincronización al momento en que la Base de Datos cambia
            window.addEventListener('AppBadgeUpdated', (event) => {
                let unreadCount = 0;
                if (event.detail && typeof event.detail.count !== 'undefined') {
                    unreadCount = event.detail.count;
                } else if (event.detail && event.detail[0] && typeof event.detail[0].count !== 'undefined') {
                    unreadCount = event.detail[0].count;
                }
                triggerBadge(unreadCount);
            });
        });
    </script>
    @endpush
</div>
