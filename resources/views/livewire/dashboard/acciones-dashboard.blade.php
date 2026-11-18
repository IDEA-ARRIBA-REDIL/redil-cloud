<div>
    <style>
        .acciones-dashboard-wrapper {
            position: relative;
            margin-top: -90px;
            padding: 0 4px 20px 4px;
        }

        .acciones-dashboard-Wrapper-con-flechas {
            padding-left: 36px;
            padding-right: 36px;
        }

        .acciones-carousel-viewport {
            overflow: hidden;
            width: 100%;
            position: relative;
        }

        .acciones-carousel-viewport.tiene-mas-de-cuatro {
            -webkit-mask-image: linear-gradient(to right, #000 0%, #000 92%, transparent 100%);
            mask-image: linear-gradient(to right, #000 0%, #000 92%, transparent 100%);
        }

        .acciones-carousel-track {
            display: flex;
            flex-wrap: nowrap;
            gap: 15px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 8px 4px 12px 4px;
            align-items: stretch;
        }

        .acciones-carousel-track::-webkit-scrollbar {
            display: none;
        }

        .acciones-slide-item {
            flex: 0 0 110px;
            width: 110px;
            max-width: 110px;
        }

        .acciones-carousel-viewport.tiene-mas-de-cuatro .acciones-slide-item {
            flex: 0 0 calc((100% - 45px) / 4);
            width: calc((100% - 45px) / 4);
            min-width: 95px;
            max-width: 115px;
        }

        .action-card-item {
            width: 100%;
            height: 110px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 6px;
            text-decoration: none !important;
        }

        .action-card-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .action-icon-box {
            width: 48px;
            height: 48px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .action-icon-box img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .action-icon-circle-template {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }

        .action-icon-circle-template i {
            font-size: 24px;
            line-height: 1;
        }

        .action-item-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #444444;
            margin: 0;
            text-align: center;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .btn-accion-carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-60%);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            color: #2F5D50;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }

        .btn-accion-carousel-nav:hover:not(:disabled) {
            background: #2F5D50;
            color: #ffffff;
            transform: translateY(-60%) scale(1.08);
        }

        .btn-accion-carousel-nav:disabled {
            opacity: 0.35;
            cursor: default;
        }

        .btn-accion-carousel-prev {
            left: 0;
        }

        .btn-accion-carousel-next {
            right: 0;
        }

        /* Estilos responsivos para Tablets y Móviles (1 sola línea, 4 elementos visibles inicialmente) */
        @media (max-width: 991.98px) {
            .acciones-slide-item,
            .acciones-carousel-viewport.tiene-mas-de-cuatro .acciones-slide-item {
                flex: 0 0 calc((100% - 36px) / 4);
                width: calc((100% - 36px) / 4);
                min-width: calc((100% - 36px) / 4);
                max-width: 110px;
            }

            .acciones-carousel-track {
                gap: 12px;
            }
        }

        @media (max-width: 767.98px) {
            .acciones-dashboard-wrapper {
                margin-top: -60px;
                padding-bottom: 12px;
            }

            .acciones-dashboard-Wrapper-con-flechas {
                padding-left: 28px;
                padding-right: 28px;
            }

            .acciones-carousel-track {
                gap: 10px;
                padding: 4px 2px 8px 2px;
            }

            .acciones-slide-item,
            .acciones-carousel-viewport.tiene-mas-de-cuatro .acciones-slide-item {
                flex: 0 0 calc((100% - 30px) / 4);
                width: calc((100% - 30px) / 4);
                min-width: calc((100% - 30px) / 4);
                max-width: calc((100% - 30px) / 4);
            }

            .action-card-item {
                background: transparent !important;
                box-shadow: none !important;
                height: auto !important;
                padding: 0 !important;
            }

            .action-card-item:hover {
                transform: none;
            }

            .action-icon-box {
                width: 64px !important;
                height: 64px !important;
                background: #ffffff !important;
                border-radius: 50% !important;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
                margin-bottom: 8px !important;
                border: 1px solid #eeeeee;
            }

            .action-icon-box img {
                width: 48px !important;
                height: 48px !important;
            }

            .action-icon-circle-template {
                width: 48px !important;
                height: 48px !important;
            }

            .action-icon-circle-template i {
                font-size: 25px !important;
            }

            .action-item-title {
                font-size: 0.82rem !important;
                color: #222222 !important;
                font-weight: 600;
            }

            .btn-accion-carousel-nav {
                width: 26px;
                height: 26px;
                top: 34px;
                transform: none;
            }

            .btn-accion-carousel-nav:hover:not(:disabled) {
                transform: scale(1.05);
            }
        }
    </style>

    @if ($totalElementos > 0)
        <div class="acciones-dashboard-wrapper pt-5 {{ $totalElementos > 4 ? 'acciones-dashboard-Wrapper-con-flechas' : '' }}"
            x-data="{
                puedeIrAtras: false,
                puedeIrAdelante: {{ $totalElementos > 4 ? 'true' : 'false' }},
                actualizarEstadoFlechas() {
                    const track = this.$refs.trackAcciones;
                    if (!track) return;
                    this.puedeIrAtras = track.scrollLeft > 5;
                    this.puedeIrAdelante = (track.scrollLeft + track.clientWidth) < (track.scrollWidth - 5);
                },
                desplazar(direccion) {
                    const track = this.$refs.trackAcciones;
                    if (!track) return;
                    const paso = track.clientWidth * 0.75;
                    track.scrollBy({ left: direccion * paso, behavior: 'smooth' });
                    setTimeout(() => this.actualizarEstadoFlechas(), 350);
                }
            }"
            x-init="setTimeout(() => actualizarEstadoFlechas(), 150)">

            {{-- Flecha Izquierda (solo cuando hay más de 4 elementos) --}}
            @if ($totalElementos > 4)
                <button type="button"
                    class="btn-accion-carousel-nav btn-accion-carousel-prev"
                    x-on:click="desplazar(-1)"
                    x-bind:disabled="!puedeIrAtras"
                    aria-label="Anterior">
                    <i class="ti ti-chevron-left fs-6"></i>
                </button>
            @endif

            <div class="acciones-carousel-viewport {{ $totalElementos > 4 ? 'tiene-mas-de-cuatro' : '' }}">
                <div class="acciones-carousel-track"
                    x-ref="trackAcciones"
                    x-on:scroll.debounce.50ms="actualizarEstadoFlechas()"
                    x-on:resize.window.debounce.100ms="actualizarEstadoFlechas()">

                    {{-- 1. Enlaces configurables activos para el usuario --}}
                    @foreach ($acciones as $accion)
                        <div class="acciones-slide-item" wire:key="accion-dash-{{ $accion->id ?? $loop->index }}">
                            <a href="{{ $accion->url_resuelta }}"
                                @if ($accion->abrir_nueva_pestana) target="_blank" rel="noopener noreferrer" @endif
                                class="action-card-item"
                                title="{{ $accion->nombre }}">
                                <div class="action-icon-box">
                                    @if ($accion->tipo_visual === 'icono')
                                        <div class="action-icon-circle-template"
                                            style="background-color: {{ $accion->icono_color ?: '#2F5D50' }};">
                                            <i class="{{ $accion->icono_completo }}"></i>
                                        </div>
                                    @else
                                        <img src="{{ $accion->imagen_url }}" alt="{{ $accion->nombre }}">
                                    @endif
                                </div>
                                <p class="action-item-title">{{ $accion->nombre }}</p>
                            </a>
                        </div>
                    @endforeach

                    {{-- 2. Opción automática de Cumpleaños (Siempre al final si cumple condiciones) --}}
                    @if ($mostrarCumpleanos)
                        <div class="acciones-slide-item" wire:key="accion-dash-cumpleanos">
                            <div class="action-card-item"
                                role="button"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvasBirthday"
                                title="Cumpleaños">
                                <div class="action-icon-box">
                                    <img src="{{ $iconoCumpleanosUrl }}" alt="Cumpleaños">
                                </div>
                                <p class="action-item-title">Cumpleaños</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Flecha Derecha (solo cuando hay más de 4 elementos) --}}
            @if ($totalElementos > 4)
                <button type="button"
                    class="btn-accion-carousel-nav btn-accion-carousel-next"
                    x-on:click="desplazar(1)"
                    x-bind:disabled="!puedeIrAdelante"
                    aria-label="Siguiente">
                    <i class="ti ti-chevron-right fs-6"></i>
                </button>
            @endif
        </div>
    @endif

    {{-- Offcanvas de Cumpleaños (se renderiza únicamente cuando el usuario tiene acceso a la opción) --}}
    @if ($mostrarCumpleanos)
        <div class="offcanvas offcanvas-end theme-bg-secondary" tabindex="-1" id="offcanvasBirthday"
            aria-labelledby="offcanvasBirthdayLabel">
            <div class="offcanvas-header d-flex align-items-center justify-content-between px-2">
                <button type="button" class="btn text-white" data-bs-dismiss="offcanvas">
                    <i class="ti ti-x"></i>
                </button>
                <h5 id="offcanvasBirthdayLabel" class="offcanvas-title text-white fw-semibold">
                    <i class="ti ti-cake me-2 ti-xl"></i>
                </h5>
            </div>
            <div class="offcanvas-body">
                @livewire('proximos-cumpleanos', [])
            </div>
        </div>
    @endif
</div>
