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
                <!-- Banner para pedir permisos de alertas (Oculto por defecto, JS lo muestra) -->
                <div id="banner-permiso-notificaciones" class="d-none px-3 pb-2">
                    <button id="btn-pedir-permisos" class="btn btn-sm btn-label-warning w-100 rounded-pill">
                        <i class="ti ti-bell-ringing me-1"></i> Activar alertas en celular
                    </button>
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

            // ========================================================================
            // NAVEGADOR -> SISTEMA OPERATIVO DEL TELÉFONO (App Badging API)
            // ========================================================================
            // Función centralizada para aplicar el "puntito rojo" de manera segura.
            // Esta función usa 'navigator.setAppBadge' que es una tecnología web
            // moderna (PWA). Le permite a un sitio web comunicarse con el sistema
            // operativo de iOS o Android para ponerle un número al icono de la app.
            const triggerBadge = async (count) => {
                // Primero validamos que el teléfono/navegador soporte esta función.
                if (!('setAppBadge' in navigator && 'clearAppBadge' in navigator)) {
                    console.log('App Badging API no soportado por este dispositivo o no instalado como App.');
                    return;
                }
                try {
                    if (count > 0) {
                        await navigator.setAppBadge(count); // Pone el número en el icono de la pantalla de inicio
                        console.log('Badge colocado:', count);
                    } else {
                        await navigator.clearAppBadge(); // Quita el número si llegó a cero
                        console.log('Badge limpiado.');
                    }
                } catch (error) {
                    console.error('Error aplicando Badge (quizás sin permisos):', error);
                }
            };

            // ========================================================================
            // PERMISOS DEL USUARIO
            // ========================================================================
            // Apple y Google exigen que el usuario dé permiso explícito para recibir 
            // notificaciones y para poder modificar el icono de la app.
            const bannerPermisos = document.getElementById('banner-permiso-notificaciones');
            const btnPedirPermisos = document.getElementById('btn-pedir-permisos');

            // Mostrar el banner solo si el navegador soporta Notificaciones y el usuario no ha respondido ('default')
            if ('Notification' in window && Notification.permission === 'default' && 'setAppBadge' in navigator && bannerPermisos) {
                bannerPermisos.classList.remove('d-none');
            }

            if(btnPedirPermisos) {
                btnPedirPermisos.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Usamos el objeto PwaPush que creamos en assets/js/pwa-push.js
                    // Este objeto no solo pide permiso, sino que registra la llave VAPID y la envía al servidor.
                    if (typeof PwaPush !== 'undefined') {
                        const success = await PwaPush.requestPermission();
                        if (success) {
                            bannerPermisos.classList.add('d-none');
                            triggerBadge({{ $conteoNoLeidas }});
                            // Opcional: Feedback al usuario
                            alert('¡Notificaciones activadas correctamente!');
                        }
                    } else {
                        // Fallback si por alguna razón el script no cargó
                        const perm = await Notification.requestPermission();
                        if(perm === 'granted' || perm === 'denied') bannerPermisos.classList.add('d-none');
                        if(perm === 'granted') triggerBadge({{ $conteoNoLeidas }});
                    }
                });
            }


            // Sincronización inicial rápida cuando la página carga
            triggerBadge({{ $conteoNoLeidas }});

            // ========================================================================
            // ESCUCHADOR DE EVENTOS DE LIVEWIRE
            // ========================================================================
            // Cuando Livewire allá en el servidor PHP hace un ->dispatch('AppBadgeUpdated'),
            // este bloque de código lo escucha en tiempo real.
            window.addEventListener('AppBadgeUpdated', (event) => {
                let unreadCount = 0;
                // Extraemos el número que PHP nos envió
                if (event.detail && typeof event.detail.count !== 'undefined') {
                    unreadCount = event.detail.count;
                } else if (event.detail && event.detail[0] && typeof event.detail[0].count !== 'undefined') {
                    unreadCount = event.detail[0].count;
                }
                // Llamamos a la función que habla con el teléfono para actualizar el número en el icono.
                triggerBadge(unreadCount);
            });
        });
    </script>
    @endpush
</div>
