@php
$configData = Helper::appClasses();
$isFooter = ($isFooter ?? false);
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Destinatarios')

<!-- Page -->
@section('vendor-style')
@vite([


'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('page-style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
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
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([4.60971, -74.08175], 11);
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
    window.centrarEnSede = (lat, lng) => {
        map.flyTo([lat, lng], 16, { duration: 1 });
        markers.find(m => 
            m.getLatLng().lat.toFixed(6) === lat.toFixed(6) && 
            m.getLatLng().lng.toFixed(6) === lng.toFixed(6)
        )?.openPopup();
    };

    // Añadir marcadores con nombres únicos
    @foreach($sedes as $index => $sede)
    const marker{{ $index }} = L.marker([{{ $sede->latitud }}, {{ $sede->longitud }}], {
        icon: tablerIcon
    }).bindPopup(`
        <div class="leaflet-popup-content">
            <h6>{{ $sede->nombre }}</h6>
            <p class="small">{{ $sede->direccion }}</p>
        </div>
    `).on('click', () => centrarEnSede({{ $sede->latitud }}, {{ $sede->longitud }}))
    .addTo(map);
    
    markers.push(marker{{ $index }});
    @endforeach

    setTimeout(() => map.invalidateSize(), 100);
});
</script>
@endsection


@section('content')

  <h4 class="mb-1">Destinatarios</h4>
  <p class="mb-4">Aquí podras visualizar los grupos en el mapa.</p>

  @include('layouts.status-msn')
  <div class="row">
    <div id="sidebar" class="col-12 col-md-3">
        <div class="p-3 bg-light border-bottom">
            <h5 class="mb-0">Listado de Sedes</h5>
        </div>
        <div class="p-3">
            @foreach($sedes as $sede)
            <div class="sede-item" 
                 onclick="centrarEnSede({{ $sede->latitud }}, {{ $sede->longitud }}, '{{ $sede->nombre }}')">
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
        <div id="map" class="border-0 shadow-sm w-100 h-75 m-10"></div>
    </div>
    
  </div>


  <br><br>



@endsection
