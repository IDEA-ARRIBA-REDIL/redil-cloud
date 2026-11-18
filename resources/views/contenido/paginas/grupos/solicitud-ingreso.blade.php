@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Solicitud para pertenecer a un grupo')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body {
            overflow-x: hidden;
        }

        .select2-container--open .select2-dropdown {
            z-index: 1060 !important;
        }

        #mapaSedes {
            width: 100%;
            height: 380px;
            border-radius: 0.75rem;
            z-index: 1;
        }

        .sede-popup-btn {
            cursor: pointer;
        }

        @media (max-width: 767.98px) {
            .formulario-tipo-next-container {
                padding-bottom: 160px !important;
            }

            select.select2.form-select {
                height: auto;
                min-height: 44px;
                font-size: 0.9375rem;
                border-radius: 0.375rem;
                padding-top: 0.6rem;
                padding-bottom: 0.6rem;
            }
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
        integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
@endsection

@section('content')
<div class="col-12">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
        <div class="col-3 text-start">
            <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
                <span class="ti-xs ti ti-arrow-left me-2"></span>
                <span class="d-none d-md-block fw-normal">Volver</span>
            </button>
        </div>
        <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Solicitud para pertenecer a un grupo</h5>
        </div>
        <div class="col-3 text-end">
            <a href="{{ route('dashboard') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
                <span class="d-none d-md-block fw-normal">Salir</span>
                <span class="ti-xs ti ti-x mx-2"></span>
            </a>
        </div>
    </nav>

    <div class="pt-5 px-7 px-sm-0 formulario-tipo-next-container" style="padding-bottom: 110px;">
        <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">

            {{-- PASO 1: Selección de Sede --}}
            <div class="step row" id="step-1">
                <div class="p-2 col-12">
                    <div class="d-flex align-items-start p-2 mt-1">
                        <div class="badge rounded rounded-circle bg-label-primary p-3 me-1">
                            <i class="ti ti-building-church ti-md"></i>
                        </div>
                        <div class="my-auto ms-1">
                            <small class="text-muted">Paso 1 de 2</small>
                            <h6 class="mb-0">Selecciona la sede</h6>
                        </div>
                    </div>
                    <div class="progress mx-2">
                        <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="row mt-6 m-0 p-0">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                                    <div>
                                        <h5 class="fw-semibold text-black mb-1">¿En qué sede deseas buscar tu grupo?</h5>
                                        <p class="text-muted small mb-0">Puedes elegir la sede desde la lista tradicional o ubicándola en el mapa.</p>
                                    </div>

                                    {{-- Switch entre Lista y Mapa --}}
                                    <div class="d-flex align-items-center bg-lighter rounded-pill px-3 py-2 border">
                                        <span class="small fw-semibold me-2 text-muted" id="labelModoLista">
                                            <i class="ti ti-list me-1"></i>Lista
                                        </span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="switchModoSede" checked>
                                        </div>
                                        <span class="small fw-semibold ms-1" id="labelModoMapa">
                                            <i class="ti ti-map-2 me-1"></i>Mapa
                                        </span>
                                    </div>
                                </div>

                                {{-- Opción A: Select tradicional --}}
                                <div id="contenedorSelectSede" class="d-none">
                                    <div class="row">
                                        <div class="col-12 col-md-8 mb-2">
                                            <label for="selectSede" class="form-label fw-semibold">Sede</label>
                                            <select id="selectSede" class="select2 form-select" data-placeholder="Selecciona una sede">
                                                <option value="">Selecciona una sede</option>
                                                @foreach ($sedes as $sede)
                                                    <option value="{{ $sede->id }}" data-nombre="{{ $sede->nombre }}"
                                                        data-direccion="{{ $sede->direccion }}">
                                                        {{ $sede->nombre }} {{ $sede->direccion ? '— ' . $sede->direccion : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Opción B: Mapa interactivo --}}
                                <div id="contenedorMapaSede">
                                    <div id="mapaSedes" class="border mb-3"></div>
                                    <div id="infoSedeMapa" class="alert alert-primary d-none align-items-center justify-content-between mb-0" role="alert">
                                        <div>
                                            <i class="ti ti-map-pin me-1"></i>
                                            Sede seleccionada en el mapa: <strong id="nombreSedeMapa"></strong>
                                        </div>
                                    </div>
                                    @if ($sedes->whereNotNull('latitud')->whereNotNull('longitud')->isEmpty())
                                        <div class="alert alert-warning mb-0" role="alert">
                                            <i class="ti ti-alert-triangle me-1"></i>
                                            Ninguna sede tiene coordenadas registradas actualmente. Usa el selector de lista para continuar.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
                    <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <button type="button" id="btnContinuarPaso2" class="btn btn-primary rounded-pill next-step px-7 py-2" disabled>
                            <span class="align-middle me-sm-1 me-0">Continuar</span>
                        </button>
                    </div>
                </div>
            </div>
            {{-- /PASO 1 --}}

            {{-- PASO 2: Listado de Grupos --}}
            <div class="step row d-none" id="step-2">
                <div class="p-2 col-12">
                    <div class="d-flex align-items-start justify-content-between p-2 mt-1">
                        <div class="d-flex align-items-start">
                            <div class="badge rounded rounded-circle bg-label-primary p-3 me-1">
                                <i class="ti ti-users-group ti-md"></i>
                            </div>
                            <div class="my-auto ms-1">
                                <small class="text-muted">Paso 2 de 2</small>
                                <h6 class="mb-0">Elige el grupo al que deseas pertenecer</h6>
                            </div>
                        </div>
                        <span id="badgeSedeSeleccionada" class="badge bg-label-info rounded-pill my-auto d-none">
                            <i class="ti ti-map-pin ti-xs me-1"></i>
                            <span id="nombreSedeBadge"></span>
                        </span>
                    </div>
                    <div class="progress mx-2">
                        <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="row mt-6 m-0 p-0">
                    <div class="col-12 mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-7">
                                <div class="input-group input-group-merge bg-white">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text" id="buscarGrupo" class="form-control"
                                        placeholder="Buscar grupo por nombre o dirección...">
                                </div>
                            </div>
                            <div class="col-12 col-md-5 text-md-end">
                                <span id="contadorGrupos" class="text-muted small"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 m-0 p-0" id="gridGrupos">
                        @foreach ($grupos as $grupo)
                            <div class="col-12 col-xl-4 col-md-6 grupo-card-item"
                                data-sede-id="{{ $grupo->sede_id }}"
                                data-nombre="{{ mb_strtolower($grupo->nombre) }}"
                                data-direccion="{{ mb_strtolower($grupo->direccion ?? '') }}">
                                <div class="card h-100 shadow-sm">
                                    <img class="card-img-top object-fit-cover" style="height: 140px;"
                                        src="{{ $grupo->portada_vinculada }}" alt="Portada {{ $grupo->nombre }}" />

                                    <div class="card-header pb-2">
                                        <h6 class="mb-0 fw-semibold text-primary lh-sm">
                                            {{ $grupo->tipoGrupo ? $grupo->tipoGrupo->nombre : 'Grupo' }}
                                        </h6>
                                        <h5 class="mb-0 fw-bold text-black mt-1">{{ $grupo->nombre }}</h5>
                                    </div>

                                    <div class="card-body pt-2">
                                        <div class="d-flex flex-row mb-3">
                                            <i class="ti ti-brand-days-counter text-black mt-1"></i>
                                            <div class="d-flex flex-column ms-2">
                                                <small class="text-muted">Día y hora de reunión:</small>
                                                <small class="fw-semibold text-black">
                                                    {{ $grupo->dia ? Helper::obtenerDiaDeLaSemana($grupo->dia, 'corto') : 'Día no indicado' }}{{ $grupo->hora ? ', ' . \Carbon\Carbon::parse($grupo->hora)->format('g:i a') : '' }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row mb-3">
                                            <i class="ti ti-map-pin text-black mt-1"></i>
                                            <div class="d-flex flex-column ms-2">
                                                <small class="text-muted">Dirección:</small>
                                                <small class="fw-semibold text-black">
                                                    {{ $grupo->direccion ?: 'Dirección no registrada' }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-1">Encargados:</small>
                                            @php
                                                $encargadosDirectos = $grupo->encargadosDirectos();
                                            @endphp
                                            @if ($encargadosDirectos->isNotEmpty())
                                                @foreach ($encargadosDirectos as $encargado)
                                                    <div class="d-flex flex-row align-items-center mb-1">
                                                        <i class="{{ $encargado->icono ?? 'ti ti-user' }} text-black me-1"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ $encargado->tipo_usuario }}"></i>
                                                        <small class="fw-semibold text-black">{{ $encargado->nombre }}</small>
                                                    </div>
                                                @endforeach
                                            @else
                                                <small class="fw-semibold text-muted">Sin encargados asignados</small>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-footer border-top-0 pt-0 pb-4">
                                        <button type="button"
                                            class="btnAbrirModalSolicitud btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light py-2"
                                            data-grupo-id="{{ $grupo->id }}"
                                            data-grupo-nombre="{{ $grupo->nombre }}">
                                            <i class="ti ti-send me-1"></i>
                                            Enviar solicitud
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Grupos evangelísticos de ejemplo para el prototipo cuando la sede no tiene grupos reales --}}
                        @php
                            $gruposEjemplo = [
                                [
                                    'nombre' => 'Grupo Evangelístico Luz y Vida',
                                    'tipo' => 'Grupo Evangelístico',
                                    'diaHora' => 'Miércoles, 7:00 pm',
                                    'direccion' => 'Calle 24 # 68-45, Apto 302',
                                    'encargados' => ['Carlos Andrés Gómez', 'Laura Sofía Martínez'],
                                ],
                                [
                                    'nombre' => 'Célula Esperanza Viva',
                                    'tipo' => 'Grupo Evangelístico',
                                    'diaHora' => 'Viernes, 7:30 pm',
                                    'direccion' => 'Carrera 70 # 48-12, Barrio Normandía',
                                    'encargados' => ['Juan Pablo Rojas'],
                                ],
                                [
                                    'nombre' => 'Casa de Paz Renuevo',
                                    'tipo' => 'Grupo Evangelístico',
                                    'diaHora' => 'Sábado, 5:00 pm',
                                    'direccion' => 'Av. Boyacá # 53-20, Salón Comunal',
                                    'encargados' => ['María Fernanda Díaz', 'Andrés Felipe Torres'],
                                ],
                            ];
                            $portadaDefaultGrupo = \Illuminate\Support\Facades\Storage::disk('global_media')->url('grupos/banner-default.jpg');
                        @endphp
                        @foreach ($gruposEjemplo as $ejemplo)
                            <div class="col-12 col-xl-4 col-md-6 grupo-card-item grupo-ejemplo-item d-none"
                                data-ejemplo="1"
                                data-nombre="{{ mb_strtolower($ejemplo['nombre']) }}"
                                data-direccion="{{ mb_strtolower($ejemplo['direccion']) }}">
                                <div class="card h-100 shadow-sm">
                                    <img class="card-img-top object-fit-cover" style="height: 140px;"
                                        src="{{ $portadaDefaultGrupo }}" alt="Portada {{ $ejemplo['nombre'] }}" />

                                    <div class="card-header pb-2">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <h6 class="mb-0 fw-semibold text-primary lh-sm">{{ $ejemplo['tipo'] }}</h6>
                                            <span class="badge bg-label-warning rounded-pill">Ejemplo</span>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-black mt-1">{{ $ejemplo['nombre'] }}</h5>
                                    </div>

                                    <div class="card-body pt-2">
                                        <div class="d-flex flex-row mb-3">
                                            <i class="ti ti-brand-days-counter text-black mt-1"></i>
                                            <div class="d-flex flex-column ms-2">
                                                <small class="text-muted">Día y hora de reunión:</small>
                                                <small class="fw-semibold text-black">{{ $ejemplo['diaHora'] }}</small>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-row mb-3">
                                            <i class="ti ti-map-pin text-black mt-1"></i>
                                            <div class="d-flex flex-column ms-2">
                                                <small class="text-muted">Dirección:</small>
                                                <small class="fw-semibold text-black">{{ $ejemplo['direccion'] }}</small>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-1">Encargados:</small>
                                            @foreach ($ejemplo['encargados'] as $nombreEncargado)
                                                <div class="d-flex flex-row align-items-center mb-1">
                                                    <i class="ti ti-user text-black me-1"></i>
                                                    <small class="fw-semibold text-black">{{ $nombreEncargado }}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="card-footer border-top-0 pt-0 pb-4">
                                        <button type="button"
                                            class="btnAbrirModalSolicitud btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light py-2"
                                            data-grupo-id=""
                                            data-grupo-nombre="{{ $ejemplo['nombre'] }}"
                                            data-grupo-tipo="{{ $ejemplo['tipo'] }}"
                                            data-grupo-dia-hora="{{ $ejemplo['diaHora'] }}"
                                            data-grupo-direccion="{{ $ejemplo['direccion'] }}">
                                            <i class="ti ti-send me-1"></i>
                                            Enviar solicitud
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="estadoVacioGrupos" class="col-12 d-none">
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="ti ti-users-group text-muted mb-2" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-semibold text-black mb-1">No encontramos grupos disponibles</h6>
                                <p class="text-muted small mb-0">No hay grupos activos que coincidan con la sede o búsqueda seleccionada.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
                    <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-between">
                        <button type="button" class="btn btn-label-secondary rounded-pill btn-outline-secondary px-7 py-2 prev-step">
                            <span class="align-middle">Volver</span>
                        </button>
                    </div>
                </div>
            </div>
            {{-- /PASO 2 --}}

        </div>
    </div>
</div>

{{-- Modal para confirmar envío y añadir mensaje adicional --}}
<div class="modal fade" id="modalEnviarSolicitudGrupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
            <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
                <p class="text-black fw-semibold mb-0">Confirmar solicitud de ingreso</p>
                <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x ti-sm"></i>
                </button>
            </div>

            <div class="modal-body px-5 py-4">
                <p class="text-black mb-3">
                    Estás a punto de enviar tu solicitud para pertenecer al grupo:
                    <strong id="modalNombreGrupo" class="text-primary"></strong>
                </p>

                <div class="mb-2">
                    <label for="mensajeAdicional" class="form-label fw-semibold">
                        Mensaje adicional <span class="text-muted fw-normal">(Opcional)</span>
                    </label>
                    <textarea id="mensajeAdicional" class="form-control" rows="4"
                        placeholder="Cuéntale al líder o encargado por qué te gustaría unirte o si tienes alguna pregunta..."></textarea>
                </div>
            </div>

            <div class="modal-footer border-top px-5 py-3">
                <button type="button" data-bs-dismiss="modal"
                    class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarEnvioSolicitud"
                    class="btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">
                    <i class="ti ti-check me-1"></i>
                    Enviar solicitud
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
    @php
        $sedesConCoordenadas = $sedes
            ->filter(fn($s) => $s->latitud && $s->longitud)
            ->values()
            ->map(fn($s) => [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'direccion' => $s->direccion,
                'latitud' => (float) $s->latitud,
                'longitud' => (float) $s->longitud,
            ]);
    @endphp
    <script type="module">
        $(document).ready(function() {
            const sedesConCoordenadas = @json($sedesConCoordenadas);

            let sedeSeleccionadaId = null;
            let sedeSeleccionadaNombre = '';
            let grupoSeleccionadoId = null;
            let mapaSedes = null;

            const $selectSede = $('#selectSede');

            function gestionarSelectSede() {
                const isMobile = window.innerWidth < 768;
                if (isMobile) {
                    if ($selectSede.hasClass('select2-hidden-accessible')) {
                        $selectSede.select2('destroy');
                    }
                    $selectSede.addClass('form-select');
                } else {
                    if (!$selectSede.hasClass('select2-hidden-accessible')) {
                        $selectSede.select2({
                            placeholder: 'Selecciona una sede',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                }
            }

            gestionarSelectSede();
            $(window).on('resize', gestionarSelectSede);

            function actualizarSedeSeleccionada(id, nombre) {
                sedeSeleccionadaId = id ? String(id) : null;
                sedeSeleccionadaNombre = nombre || '';

                $('#btnContinuarPaso2').prop('disabled', !sedeSeleccionadaId);

                if (sedeSeleccionadaId) {
                    $('#nombreSedeMapa').text(sedeSeleccionadaNombre);
                    $('#infoSedeMapa').removeClass('d-none').addClass('d-flex');
                    $('#nombreSedeBadge').text(sedeSeleccionadaNombre);
                    $('#badgeSedeSeleccionada').removeClass('d-none');
                } else {
                    $('#infoSedeMapa').addClass('d-none').removeClass('d-flex');
                    $('#badgeSedeSeleccionada').addClass('d-none');
                }
            }

            $selectSede.on('change', function() {
                const id = $(this).val();
                const nombre = $(this).find('option:selected').data('nombre') || '';
                actualizarSedeSeleccionada(id, nombre);
            });

            function inicializarMapaSedes() {
                if (mapaSedes !== null) {
                    setTimeout(() => mapaSedes.invalidateSize(), 150);
                    return;
                }

                mapaSedes = L.map('mapaSedes').setView([{{ $latitudInicial }}, {{ $longitudInicial }}], 12);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapaSedes);

                const bounds = [];

                sedesConCoordenadas.forEach((sede) => {
                    const marker = L.marker([sede.latitud, sede.longitud]).addTo(mapaSedes);
                    bounds.push([sede.latitud, sede.longitud]);

                    const popupBtn = document.createElement('div');
                    popupBtn.style.minWidth = '180px';
                    popupBtn.innerHTML = `
                        <strong class="d-block mb-1"></strong>
                        <small class="text-muted d-block mb-2"></small>
                        <button type="button" class="btn btn-xs btn-primary rounded-pill w-100 sede-popup-btn">
                            Seleccionar esta sede
                        </button>
                    `;
                    popupBtn.querySelector('strong').textContent = sede.nombre;
                    popupBtn.querySelector('small').textContent = sede.direccion ?? 'Sin dirección registrada';
                    popupBtn.querySelector('button').addEventListener('click', function() {
                        $selectSede.val(sede.id).trigger('change');
                        actualizarSedeSeleccionada(sede.id, sede.nombre);
                        marker.closePopup();
                    });

                    marker.bindPopup(popupBtn);
                });

                if (bounds.length > 0) {
                    mapaSedes.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
                }

                setTimeout(() => mapaSedes.invalidateSize(), 150);
            }

            // Switch entre Lista y Mapa
            $('#switchModoSede').on('change', function() {
                const usarMapa = $(this).is(':checked');

                if (usarMapa) {
                    $('#contenedorSelectSede').addClass('d-none');
                    $('#contenedorMapaSede').removeClass('d-none');
                    $('#labelModoLista').addClass('text-muted');
                    $('#labelModoMapa').removeClass('text-muted');
                    inicializarMapaSedes();
                } else {
                    $('#contenedorMapaSede').addClass('d-none');
                    $('#contenedorSelectSede').removeClass('d-none');
                    $('#labelModoMapa').addClass('text-muted');
                    $('#labelModoLista').removeClass('text-muted');
                }
            });

            // Inicializar el mapa por defecto al cargar la vista
            inicializarMapaSedes();

            let grupoSeleccionadoDatos = {};

            function filtrarGrupos() {
                const texto = ($('#buscarGrupo').val() || '').trim().toLowerCase();
                let realesEnSede = 0;
                let visibles = 0;

                // 1. Evaluar primero grupos evangelísticos reales en la sede seleccionada
                $('.grupo-card-item:not(.grupo-ejemplo-item)').each(function() {
                    const cardSedeId = String($(this).data('sede-id'));
                    const nombre = String($(this).data('nombre') || '');
                    const direccion = String($(this).data('direccion') || '');

                    const coincideSede = cardSedeId === sedeSeleccionadaId;
                    if (coincideSede) {
                        realesEnSede++;
                    }
                    const coincideTexto = !texto || nombre.includes(texto) || direccion.includes(texto);

                    if (coincideSede && coincideTexto) {
                        $(this).removeClass('d-none');
                        visibles++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });

                // 2. Si la sede no tiene grupos evangelísticos reales, mostrar los de ejemplo
                if (realesEnSede === 0) {
                    $('.grupo-ejemplo-item').each(function() {
                        const nombre = String($(this).data('nombre') || '');
                        const direccion = String($(this).data('direccion') || '');
                        const coincideTexto = !texto || nombre.includes(texto) || direccion.includes(texto);

                        if (coincideTexto) {
                            $(this).removeClass('d-none');
                            visibles++;
                        } else {
                            $(this).addClass('d-none');
                        }
                    });
                } else {
                    $('.grupo-ejemplo-item').addClass('d-none');
                }

                $('#contadorGrupos').text(visibles === 1 ? '1 grupo disponible' : `${visibles} grupos disponibles`);
                $('#estadoVacioGrupos').toggleClass('d-none', visibles > 0);
            }

            // Avanzar al Paso 2
            $('.next-step').on('click', function() {
                if (!sedeSeleccionadaId) return;

                $('#step-1').addClass('d-none');
                $('#step-2').removeClass('d-none');
                $('.prev-step').removeClass('d-none');

                filtrarGrupos();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Regresar al Paso 1
            $('.prev-step').on('click', function() {
                $('#step-2').addClass('d-none');
                $('#step-1').removeClass('d-none');
                $('.prev-step').addClass('d-none');

                if ($('#switchModoSede').is(':checked') && mapaSedes) {
                    setTimeout(() => mapaSedes.invalidateSize(), 150);
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            $('#buscarGrupo').on('input', filtrarGrupos);

            // Abrir modal de solicitud
            const modalElement = document.getElementById('modalEnviarSolicitudGrupo');
            const modalSolicitud = new bootstrap.Modal(modalElement);

            $(document).on('click', '.btnAbrirModalSolicitud', function() {
                grupoSeleccionadoId = $(this).data('grupo-id') || null;
                const grupoNombre = $(this).data('grupo-nombre');
                grupoSeleccionadoDatos = {
                    nombre: grupoNombre,
                    tipo: $(this).data('grupo-tipo') || '',
                    diaHora: $(this).data('grupo-dia-hora') || '',
                    direccion: $(this).data('grupo-direccion') || '',
                };

                $('#modalNombreGrupo').text(grupoNombre);
                $('#mensajeAdicional').val('');
                modalSolicitud.show();
            });

            // Confirmar y navegar a la vista de éxito
            $('#btnConfirmarEnvioSolicitud').on('click', function() {
                const urlBase = "{{ route('grupo.solicitudExitosa') }}";
                const params = new URLSearchParams();
                if (grupoSeleccionadoId) {
                    params.append('grupo_id', grupoSeleccionadoId);
                } else if (grupoSeleccionadoDatos.nombre) {
                    params.append('grupo_nombre', grupoSeleccionadoDatos.nombre);
                    params.append('grupo_tipo', grupoSeleccionadoDatos.tipo);
                    params.append('sede_nombre', sedeSeleccionadaNombre);
                    params.append('dia_hora', grupoSeleccionadoDatos.diaHora);
                    params.append('direccion', grupoSeleccionadoDatos.direccion);
                }
                const mensaje = ($('#mensajeAdicional').val() || '').trim();
                if (mensaje) {
                    params.append('mensaje', mensaje);
                }

                window.location.href = `${urlBase}?${params.toString()}`;
            });
        });
    </script>
@endsection
