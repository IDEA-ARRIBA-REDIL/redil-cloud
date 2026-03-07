@php
$configData = Helper::appClasses();
$isFooter = ($isFooter ?? false);
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Geo asignación')

@section('vendor-style')
@vite([

'resources/assets/vendor/libs/select2/select2.scss',
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
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
])
@endsection

@section('page-script')
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>

<script type="module">
  // Inicialización del mapa
  var map = L.map('map').setView(['{{$latitudInicial}}', '{{$longitudInicial}}'], 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '<a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  // Hacer la función asignarAsistente globalmente accesible
  window.asignarAsistente = function(grupoId, idUsuario) {
    Livewire.dispatch('asignar-al-grupo', { grupoId: grupoId, idUsuario: idUsuario });
  };

  @foreach($grupos as $grupo)
    @if($grupo->latitud != null && $grupo->longitud != null && $grupo->tipoGrupo->visible_mapa_asignacion == true)
      var IconoGrupo = L.icon({
        iconUrl: "{{$configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/pines-mapa/'.$grupo->tipoGrupo->geo_icono) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/default-m.png' }}",
        iconSize: [35, 45],
        iconAnchor: [27, 27],
        popupAnchor: [0, -14]
      });

      var nombreGrupo = "{{$grupo->nombre}}";
      var grupoId = "{{$grupo->id}}";
      var tipoGrupo = "{{$grupo->tipoGrupo->nombre}}";
      var direccionGrupo = "{{$grupo->direccion}}" != "" ? ` ubicado en la dirección '{{$grupo->direccion}}'` : "";
      var nombreUsuario = "{{$usuario->nombre(3)}}";
      var idUsuario = "{{$usuario->id}}";

      var DatosCelula = [{
        "info": `Agregar a <b>'${nombreUsuario}'</b> al Grupo <b>${tipoGrupo} '${nombreGrupo}'${direccionGrupo}</b><br><br> <b><i class='ti ti-circle-plus'></i>Clic aquí para agregar </b>`,
        "url": "#"
      }];

      L.marker(['{{$grupo->latitud}}', '{{$grupo->longitud}}'], {
        icon: IconoGrupo,
        title: nombreGrupo,
        alt: nombreGrupo
      })
      .bindPopup(`<a href="#" onclick="asignarAsistente(${grupoId}, ${idUsuario}); return false;">${DatosCelula[0].info}</a>`)
      .addTo(map);
    @endif
  @endforeach
</script>

<script type="module">
  window.addEventListener('verEnElMapa', event => {
    map.flyTo(new L.LatLng(event.detail.latitud, event.detail.longitud), event.detail.zoom);
  });

  window.addEventListener('msn', event => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });
</script>
@endsection


@section('content')

  <h4 class="mb-1 fw-semibold text-primary">eo asignación</h4>
  <p class="mb-4 text-black">Aquí podrás gestionar a <b>{{$usuario->nombre(3)}}</b> a un grupo.</p>

  @include('layouts.status-msn')

  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-10 p-1 border-1">
        <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">
          @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
            <li class="nav-item flex-fill">
              <a id="tap-principal" href="{{ route('usuario.modificar', [$formulario, $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal">
                <i class='ti-xs ti ti-user-check me-2'></i> Datos principales
              </a>
            </li>
          @endcan

          @can('informacionCongregacionalPolitica', $usuario)
            <li class="nav-item flex-fill">
              <a id="tap-info-congregacional" href="{{ route('usuario.informacionCongregacional', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="info-congregacional">
                <i class='ti-xs ti ti-building-church me-2'></i> Información congregacional
              </a>
            </li>
          @endif

          @can('geoasignacionUsuarioPolitica', $usuario)
            <li class="nav-item flex-fill">
              <a id="tap-geoasignacion" href="{{ route('usuario.geoAsignacion', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="geoasignacion">
                <i class='ti-xs ti ti-map-pin-2 me-2'></i>Geo asignación
              </a>
            </li>
          @endif

          @can('relacionesFamiliaresUsuarioPolitica', $usuario)
            <li class="nav-item flex-fill">
              <a id="tap-familia" href="{{ route('usuario.relacionesFamiliares', ['formulario' => $formulario, 'usuario' => $usuario]) }}" class="nav-link p-3 waves-effect waves-light" data-tap="familia">
                <i class='ti-xs ti ti-home-heart me-2'></i>Relaciones familiares
              </a>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->



  @livewire('Usuarios.mapa-geo-asignacion')

  <div id="map" class="border-0 shadow-sm w-100 h-75 mb-4"></div>

@endsection
