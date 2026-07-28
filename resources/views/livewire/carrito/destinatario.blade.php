@section('title', 'Destinatarios')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('page-style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .sede-item {
        cursor: pointer;
        transition: background-color 0.2s, border-color 0.2s;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 5px;
    }
    .sede-item:hover {
        background-color: #f8f9fa;
    }
    .sede-item.active {
        background-color: #e7f1ff;
        border: 1px solid #0d6efd !important;
    }
    /* Estilos adaptativos para contenedor y scroll vertical */
    #sidebar {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 320px);
        min-height: 420px;
        max-height: 620px;
    }
    #sidebar-list-container {
        flex: 1;
        overflow-y: auto;
        padding-bottom: 30px;
    }
    #mapsider {
        height: calc(100vh - 320px);
        min-height: 420px;
        max-height: 620px;
    }
    /* Ajuste para móviles */
    @media (max-width: 768px) {
        #sidebar {
            height: 320px;
            min-height: 320px;
            margin-bottom: 15px;
        }
        #mapsider {
            height: 350px !important;
            min-height: 350px !important;
        }
    }
</style>
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
])
@endsection

@section('page-script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('livewire:initialized', function() {
    const map = L.map('map').setView([{{ $centro['lat'] }}, {{ $centro['lng'] }}], 11);
    const markers = {};

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Configurar icono
    const tablerIcon = L.divIcon({
        className: 'custom-icon',
        html: '<i class="ti ti-map-pin text-danger fs-3"></i>',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    // Obtener sedes desde Livewire como JSON seguro
    const sedesData = @json($sedes);

    // Renderizar únicamente las sedes que posean latitud y longitud válidas en el mapa
    sedesData.forEach((sede) => {
        if (sede.latitud && sede.longitud) {
            const lat = parseFloat(sede.latitud);
            const lng = parseFloat(sede.longitud);

            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                const marker = L.marker([lat, lng], {
                    icon: tablerIcon
                }).bindPopup(`
                    <div class="leaflet-popup-content p-1">
                        <h6 class="mb-1 fw-bold">${sede.nombre}</h6>
                        <p class="small text-muted mb-2">${sede.direccion || ''}</p>
                        <button class="btn btn-xs btn-primary w-100" onclick="solicitarSeleccionSede(${sede.id})">
                            <i class="ti ti-check me-1"></i>Seleccionar Sede
                        </button>
                    </div>
                `).on('click', () => solicitarSeleccionSede(sede.id))
                .addTo(map);

                markers[sede.id] = marker;
            }
        }
    });

    // Función para centrar mapa si la sede posee coordenadas
    window.centrarSedeEnMapa = (id) => {
        const sede = sedesData.find(s => s.id == id);
        if (sede && sede.latitud && sede.longitud) {
            const lat = parseFloat(sede.latitud);
            const lng = parseFloat(sede.longitud);
            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                map.flyTo([lat, lng], 16, { duration: 1 });
                if (markers[id]) {
                    markers[id].openPopup();
                }
            }
        }
    };

    // Función principal con validación SweetAlert2 al cambiar de sede
    window.solicitarSeleccionSede = (targetId) => {
        const currentSelectedId = @this.get('sedeSeleccionadaId');

        // Si se selecciona la misma sede ya activa, solo centrar mapa si corresponde
        if (currentSelectedId && currentSelectedId == targetId) {
            centrarSedeEnMapa(targetId);
            return;
        }

        const targetSede = sedesData.find(s => s.id == targetId);
        if (!targetSede) return;

        // Si ya había una sede elegida y es diferente, pedir confirmación vía SweetAlert2
        if (currentSelectedId && currentSelectedId != targetId) {
            const currentSede = sedesData.find(s => s.id == currentSelectedId);
            const nombreActual = currentSede ? currentSede.nombre : 'otra sede';
            const nombreNuevo = targetSede.nombre;

            Swal.fire({
                title: '¿Cambiar sede de entrega?',
                html: `Actualmente tienes seleccionada la <strong>${nombreActual}</strong>.<br><br>¿Deseas cambiar tu sede de entrega a <strong>${nombreNuevo}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ti ti-check me-1"></i>Sí, cambiar',
                cancelButtonText: '<i class="ti ti-x me-1"></i>Mantener actual',
                customClass: {
                    confirmButton: 'btn btn-primary me-2 rounded-pill',
                    cancelButton: 'btn btn-label-secondary rounded-pill'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmarCambioSede(targetId);
                }
            });
        } else {
            // Si no había selección previa, asignar directamente
            confirmarCambioSede(targetId);
        }
    };

    function confirmarCambioSede(id) {
        @this.call('seleccionarSede', id);
        centrarSedeEnMapa(id);
    }

    // Al iniciar, centrar en la sede preseleccionada si existe
    const initialId = @this.get('sedeSeleccionadaId');
    if (initialId) {
        centrarSedeEnMapa(initialId);
    }

    setTimeout(() => map.invalidateSize(), 200);
});
</script>
@endsection

<div>
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
    <div class="col-3 text-start">
      <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </button>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Selecciona un destinatario</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <!-- Secciones -->
  <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2" style="padding-bottom: 120px;">
    <div class="step row " id="step-1">
      <div class="p-4 col-12">
        <div class="d-flex align-items-start p-2 mt-1">
          <div class="badge rounded rounded-circle bg-label-primary p-3 me-1 rounded">
            <i class="ti ti-shopping-cart ti-md"></i>
          </div>
          <div class="my-auto ms-1 ">
            <small class="text-muted">Paso {{$contador}} de {{$totalSecciones}} </small>
            <h6 class="mb-0">Carrito </h6>
          </div>
        </div>
        <div class="progress mx-2">
          <div id="progress-bar" class="progress-bar" role="progressbar"
            style="width: {{($contador / $totalSecciones) * 100}}%;"
            aria-valuenow="{{($contador/ $totalSecciones) * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
    </div>

  @include('layouts.status-msn')
  <div class="row mt-4">
    <div id="sidebar" class="col-12 col-md-3 card p-0">
        <div class="p-3 bg-light border-bottom">
            <h5 class="mb-0">Listado de Sedes</h5>
        </div>
        <div id="sidebar-list-container" class="p-3">
            @foreach($sedes as $sede)
            @php
                $sedeId = is_array($sede) ? $sede['id'] : $sede->id;
                $sedeNombre = is_array($sede) ? $sede['nombre'] : $sede->nombre;
                $sedeBarrio = is_array($sede) ? ($sede['barrio'] ?? $sede['direccion'] ?? '') : ($sede->barrio->nombre ?? $sede->barrio_auxiliar ?? $sede->direccion ?? '');
                $lat = is_array($sede) ? ($sede['latitud'] ?? null) : ($sede->latitud ?? null);
                $lng = is_array($sede) ? ($sede['longitud'] ?? null) : ($sede->longitud ?? null);
                $tieneCoordenadas = !empty($lat) && !empty($lng) && (float)$lat != 0 && (float)$lng != 0;
            @endphp
            <div class="sede-item border-bottom {{ $sedeSeleccionadaId == $sedeId ? 'active' : '' }}"
                 onclick="solicitarSeleccionSede({{ $sedeId }})">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-map-pin me-2 {{ $tieneCoordenadas ? 'text-danger' : 'text-muted' }}"></i>
                        <div>
                            <h6 class="mb-1 fw-semibold">{{ $sedeNombre }}</h6>
                            <p class="mb-0 small text-muted">{{ $sedeBarrio }}</p>
                        </div>
                    </div>
                    @if(!$tieneCoordenadas)
                        <span class="badge bg-label-warning text-black fs-tiny" title="Esta sede no dispone de mapa">Sin GPS</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div id="mapsider" class="col-12 col-md-9">
        <div id="map" wire:ignore class="border-0 shadow-sm w-100 h-100 rounded"></div>
    </div>

  </div>

  <div class="w-100 fixed-bottom py-3 px-6 px-sm-0 border-top shadow-sm" style="background-color: #FFF; z-index: 1040;">
        <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 d-flex justify-content-between align-items-center">
            <a class="btn btn-outline-secondary rounded-pill px-5">
                <i class="ti ti-arrow-left me-1"></i> Anterior
            </a>
            <button wire:click="procesarPago" class="btn btn-primary rounded-pill px-5">
                Pagar <i class="ti ti-arrow-right ms-1"></i>
            </button>
        </div>
  </div>
</div>
