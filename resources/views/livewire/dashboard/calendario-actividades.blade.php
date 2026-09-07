<div class="{{ $claseColumnas }}" id="contenedor-calendario-actividades">
    @if ($tienePermiso)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="text-black fw-bold mb-0">
                <i class="ti ti-calendar me-1"></i> Calendario de Actividades
            </h5>
            @if(auth()->check() && auth()->user()->can('actividades.subitem_nueva_actividad'))
                <a href="{{ route('actividades.nueva') }}" class="btn btn-sm btn-primary rounded-pill">
                    <i class="ti ti-plus me-1"></i> Nueva Actividad
                </a>
            @endif
        </div>

        <div class="card app-calendar-wrapper border shadow-sm dashboard-calendar-card">
            <div class="row g-0">
                <!-- Barra lateral de Filtros del Calendario -->
                <div class="col-12 col-lg-3 app-calendar-sidebar border-end" id="app-calendar-sidebar-dashboard">
                    <div class="p-3 border-bottom d-none d-lg-block">
                        <small class="text-muted text-uppercase fw-semibold">Filtros de Actividades</small>
                    </div>

                    <div class="p-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input select-all" type="checkbox" id="dashboardCalendarSelectAll" data-value="all" checked>
                            <label class="form-check-label fw-bold" for="dashboardCalendarSelectAll">Ver Todas</label>
                        </div>

                        <div class="app-calendar-events-filter mt-2">
                            <div class="text-muted small mb-2 fw-semibold">Por Categoría:</div>
                            @forelse($tagsGenerales as $tag)
                                <div class="form-check mb-2">
                                    <input class="form-check-input input-filter-calendario" type="checkbox" id="calendar-select-{{ $tag['id'] }}" data-value="{{ $tag['id'] }}" checked>
                                    <label class="form-check-label" for="calendar-select-{{ $tag['id'] }}">{{ $tag['nombre'] }}</label>
                                </div>
                            @empty
                                <div class="text-muted small mb-2 fst-italic">No hay categorías asociadas a tus actividades.</div>
                            @endforelse

                            <div class="form-check form-check-secondary mt-2">
                                <input class="form-check-input input-filter-calendario" type="checkbox" id="calendar-select-others" data-value="0" checked>
                                <label class="form-check-label text-muted" for="calendar-select-others">Sin Categoría (Otros)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Barra lateral -->

                <!-- Contenedor del Calendario -->
                <div class="col-12 col-lg-9 app-calendar-content">
                    <div class="card shadow-none border-0">
                        <div class="card-body p-2 p-md-3">
                            <div id="calendar-dashboard" wire:ignore></div>
                        </div>
                    </div>
                </div>
                <!-- /Contenedor del Calendario -->
            </div>
        </div>

        <style>
            @media (max-width: 991.98px) {
                .dashboard-calendar-card .app-calendar-sidebar {
                    position: static !important;
                    width: 100% !important;
                    height: auto !important;
                    left: 0 !important;
                    border-bottom: 1px solid rgba(0,0,0,0.125) !important;
                    border-right: none !important;
                }
            }
        </style>

        @script
        <script>
            let calendarInstanceDashboard = null;

            function inicializarCalendarioDashboard() {
                const calendarEl = document.getElementById('calendar-dashboard');
                if (!calendarEl || typeof Calendar === 'undefined') {
                    return;
                }

                if (calendarInstanceDashboard) {
                    calendarInstanceDashboard.destroy();
                }

                const actividadesOriginales = @json($arrayActividades);

                // Función que filtra los eventos en base a los checkboxes seleccionados
                function getActividadesFiltradasDashboard() {
                    const seleccionados = Array.from(document.querySelectorAll('.input-filter-calendario:checked'))
                        .map(cb => cb.getAttribute('data-value'));
                    const selectAllEl = document.getElementById('dashboardCalendarSelectAll');
                    const verTodo = selectAllEl ? selectAllEl.checked : false;

                    if (verTodo) {
                        return actividadesOriginales;
                    }

                    return actividadesOriginales.filter(function(event) {
                        const eventTags = (event.extendedProps && event.extendedProps.tags) || [];

                        // 1. Caso: "Sin Categoría" (valor '0') y evento sin tags
                        if (seleccionados.includes('0') && eventTags.length === 0) {
                            return true;
                        }

                        // 2. Caso: Algún tag coincide con los seleccionados
                        return eventTags.some(tag => seleccionados.includes(tag.toString()));
                    });
                }

                calendarInstanceDashboard = new Calendar(calendarEl, {
                    plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        start: 'prev,next today',
                        center: 'title',
                        end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        day: 'Día',
                        list: 'Lista'
                    },
                    initialDate: new Date(),
                    navLinks: true,
                    events: function(info, successCallback, failureCallback) {
                        successCallback(getActividadesFiltradasDashboard());
                    },
                    editable: false,
                    selectable: false,
                    locale: 'es',
                    eventClick: function(info) {
                        const idActividad = info.event.id;
                        if (idActividad) {
                            window.location.href = '/actividades/' + idActividad + '/perfil';
                        }
                    }
                });

                calendarInstanceDashboard.render();

                // Escuchar cambios en los filtros de checkboxes
                const filtrosCheckboxes = document.querySelectorAll('.input-filter-calendario, #dashboardCalendarSelectAll');
                filtrosCheckboxes.forEach(el => {
                    el.addEventListener('change', function() {
                        const selectAllEl = document.getElementById('dashboardCalendarSelectAll');
                        if (this.id === 'dashboardCalendarSelectAll') {
                            document.querySelectorAll('.input-filter-calendario').forEach(cb => cb.checked = this.checked);
                        } else {
                            if (!this.checked && selectAllEl) {
                                selectAllEl.checked = false;
                            }
                        }
                        if (calendarInstanceDashboard) {
                            calendarInstanceDashboard.refetchEvents();
                        }
                    });
                });
            }

            // Inicializar cuando el DOM y scripts estén listos
            setTimeout(() => {
                inicializarCalendarioDashboard();
            }, 200);

            // Re-renderizar si Livewire navega con SPA
            document.addEventListener('livewire:navigated', () => {
                setTimeout(inicializarCalendarioDashboard, 200);
            });
        </script>
        @endscript
    @endif
</div>
