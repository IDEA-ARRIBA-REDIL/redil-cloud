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
        transition: background-color 0.2s;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 5px;
    }
    .sede-item:hover {
        background-color: #f8f9fa;
    }
    .sede-item.active {
        background-color: #e7f1ff;
        border: 1px solid #0d6efd;
    }
    /* Estilos para el scroll vertical del listado */
    #sidebar-list-container {
        height: 700px;
        overflow-y: auto;
    }
    /* Ajuste para móviles: altura automática */
    @media (max-width: 768px) {
        #sidebar-list-container {
            height: 300px;
            margin-bottom: 20px;
        }
        #mapsider {
            height: 400px !important;
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
    const markers = [];

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

    // Función de centrado
    window.centrarEnSede = (lat, lng, id) => {
        // Solo mover mapa si hay coordenadas válidas
        if (lat && lng) {
            map.flyTo([lat, lng], 16, { duration: 1 });
            const marker = markers.find(m =>
                m.getLatLng().lat.toFixed(6) === Number(lat).toFixed(6) &&
                m.getLatLng().lng.toFixed(6) === Number(lng).toFixed(6)
            );
            if (marker) marker.openPopup();
        }

        // Llamar al método Livewire siempre
        @this.call('seleccionarSede', id);
    };

    // Obtener sedes desde Livewire como JSON seguro
    const sedesData = @json($sedes);

    // Iterar sobre los datos en JS
    sedesData.forEach((sede, index) => {
        if (sede.latitud && sede.longitud) {
            const lat = parseFloat(sede.latitud);
            const lng = parseFloat(sede.longitud);

            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = L.marker([lat, lng], {
                    icon: tablerIcon
                }).bindPopup(`
                    <div class="leaflet-popup-content">
                        <h6>${sede.nombre}</h6>
                        <p class="small">${sede.direccion || ''}</p>
                        <button class="btn btn-sm btn-primary" onclick="centrarEnSede(${lat}, ${lng}, ${sede.id})">Seleccionar</button>
                    </div>
                `).on('click', () => centrarEnSede(lat, lng, sede.id))
                .addTo(map);

                markers.push(marker);
            }
        }
    });

    setTimeout(() => map.invalidateSize(), 100);
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
  <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
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
            <div class="sede-item border-bottom {{ $sedeSeleccionadaId == $sede->id ? 'active' : '' }}"
                 wire:click="seleccionarSede({{ $sede->id }})"
                 onclick="centrarEnSede({{ $sede->latitud ?? 'null' }}, {{ $sede->longitud ?? 'null' }}, {{ $sede->id }})">
                <div class="d-flex align-items-center">
                    <i class="ti ti-map-pin me-2 text-danger"></i>
                    <div>
                        <h6 class="mb-1">{{ $sede->nombre }}</h6>
                        <p class="mb-1 small text-muted">{{ $sede->barrio }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div  id="mapsider" style="height: 700px;" class="col-12 col-md-9">
        <div id="map" wire:ignore class="border-0 shadow-sm w-100 h-100 m-10"></div>
    </div>

  </div>

  <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #FFF">
        <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
            <a style="float:left;width:200px"
                class=" w-40 btn  ms-5 me-5 btn-outline-secondary rounded-pill btn-next">
                Anterior
            </a>
            <button style="float:right;width:200px" wire:click="procesarPago"
                class=" mt-3 me-5 rounded-pill btn btn-primary btn-next">
                Pagar
            </button>
        </div>
  </div>
  <br><br>
</div>
