@php
$configData = Helper::appClasses();
$isFooter = ($isFooter ?? false);
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Grupos')

<!-- Page -->
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

<script>
    const buscarInput = document.getElementById('buscar');
    const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
    const formularioBuscar = document.getElementById('formBuscar');
    let timeoutId;
    const delay = 1000; // Tiempo en milisegundos después de dejar de escribir para enviar el formulario

    buscarInput.addEventListener('input', function() {
        clearTimeout(timeoutId); // Limpiar cualquier timeout anterior

        if (this.value.length >= 3) {
          timeoutId = setTimeout(() => {
              formularioBuscar.submit();
          }, delay);
        }else if(this.value.length == 0)
        {
          formularioBuscar.submit();
        }
    });

    btnBorrarBusquedaPorPalabra.addEventListener('click', function() {
      buscarInput.value = "";
      formularioBuscar.submit();
    });
</script>

<script>
 var map = L.map('map').setView(['{{$latitudInicial}}', '{{$longitudInicial}}'], 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '<a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);


  var marcador;
// Hacer la función asignarAsistente globalmente accesible
window.asignarAsistente = function(grupoId, idUsuario) {
    Livewire.dispatch('asignar-al-grupo', { grupoId: grupoId, idUsuario: idUsuario });
  };


  // función que crea los pines en el mapa
  function crearMarcadores(){

    @foreach($grupos as $grupo)
      var lat = "{{$grupo->latitud}}";
      var lng = "{{$grupo->longitud}}";
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

        var verPerfil = '';
        @if($rolActivo->hasPermissionTo('grupos.opcion_ver_perfil_grupo'))
        var verPerfil = '<br><a href="/grupo/{{$grupo->id}}/perfil/estadisticas-grupo" target="_blank"> <b> <i class="ti ti-users-group"></i> Ver perfil  </b> </a>';
        @endif



        L.marker(['{{$grupo->latitud}}', '{{$grupo->longitud}}'], {
          icon: IconoGrupo,
          title: nombreGrupo,
          alt: nombreGrupo
        })
        .bindPopup( "<b>Nombre:</b> {{$grupo->nombre}} <br> <b>Latitud:</b> "+lat+"<br> <b>Longitud:</b> "+lng + ' <br><br><a href="https://www.google.com/maps/@?api=1&map_action=pano&viewpoint='+lat+'%2C'+lng+'" target="_blank"> <b> <i class="ti ti-brand-google-maps"></i> Ver en google  </b> </a> '+verPerfil )

        .addTo(map);
      @endif
    @endforeach
  }

  function crearCoverturas(){
    @foreach($grupos as $grupo)

      lat = "{{$grupo->latitud}}";
      lng = "{{$grupo->longitud}}";

      L.circle([lat, lng], {
        color: '{{$grupo->tipoGrupo->color}}',
        fillColor: '{{$grupo->tipoGrupo->color}}',
        fillOpacity: 0.5,
        radius: '{{$grupo->tipoGrupo->metros_cobertura}}'
      }).addTo(map);
    @endforeach
  }

  function reiniciarMapa()
  {
    map.remove();
    map = L.map('map').setView(['{{$latitudInicial}}', '{{$longitudInicial}}'], 10);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '<a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
  }


  $(document).ready(function() {
    crearMarcadores();

    $('#verCovertura').click(function() {
      if(this.checked){
        reiniciarMapa();
        crearCoverturas();
      }else{
        reiniciarMapa();
        crearMarcadores();
      }
    });
  });

</script>

<script type="module">
  $(document).ready(function() {
    $('.select2BusquedaAvanzada').select2({
      dropdownParent: $('#modalBusquedaAvanzada')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalBusquedaAvanzada').on('scroll', function(event) {
    $(this).find(".select2BusquedaAvanzada").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });

</script>

@endsection

@section('content')

  <h4 class="mb-1 fw-semibold text-primary">Mapa de grupos</h4>
  <p class="mb-4 text-black">Aquí podras visualizar los grupos en el mapa</p>


  @include('layouts.status-msn')

  <div class="row">
    <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('grupo.mapaDeGrupos') }}">
      <div class="row mt-5">
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input id="buscar" name="buscar" type="text" value="{{$parametrosBusqueda->buscar}}" class="form-control" placeholder="Busqueda..." aria-describedby="btnBusqueda">
          @if($parametrosBusqueda->buscar)
          <span id="borrarBusquedaPorPalabra" class="input-group-text"><i class="ti ti-x"></i></span>
          @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          @endif
        </div>
      </div>

      <div class="col-3 col-md-8 d-flex justify-content-end">
          @if($parametrosBusqueda->bandera == 1)
          <a type="button" href="{{ route('grupo.mapaDeGrupos') }}" class="btn btn-outline-danger waves-effect px-2 px-md-5 me-1"><span class="d-none d-md-block fw-semibold">Limpiar filtros</span> <i class="ti ti-filter-off ms-1"></i></a>
          @else
          <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5  me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i> </button>
          @endif
        </div>
        @if($grupos)
        <span class="text-center py-3">{{ $grupos->count() > 1 ? $grupos->count().' Grupos' : $grupos->count().' Grupo' }} {!! $parametrosBusqueda->textoBusqueda ? '('.$parametrosBusqueda->textoBusqueda.')' : '' !!}</span>
        @endif
      </div>

    </form>

    <div class="col-12">
      <div class=" small">¿Ver covertura?
        <label class="switch switch-lg">
          <input id="verCovertura" name="verCovertura" type="checkbox" class="switch-input" />
          <span class="switch-toggle-slider">
            <span class="switch-on">No</span>
            <span class="switch-off">Si</span>
          </span>
          <span class="switch-label"></span>
        </label>
      </div>
    </div>
  </div>


  <div id="map" class="border-0 shadow-sm w-100 h-75 my-5">
  </div>


  <!-- offcanvas busqueda avanzada -->
  <form class="forms-sample" method="GET" action="{{ route('grupo.mapaDeGrupos') }}">
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
              Filtros
            </h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
          <div class="row">


              <div class="col-12 mb-3">
                <label for="nameBasic" class="form-label">Por palabra</label>
                <input id="buscar" name="buscar" type="text" value="{{$parametrosBusqueda->buscar}}" class="form-control" placeholder="Buscar por nombre, email, identificación">
              </div>

              <!-- Por tipo de grupo -->
              <div class="col-12 mb-3">
                <label for="filtroPorTipoDeGrupo" class="form-label">Fitrar por tipo de grupo </label>
                <select id="filtroPorTipoDeGrupo" name="filtroPorTipoDeGrupo[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($tiposDeGrupo as $tipoGrupo)
                  <option value="{{ $tipoGrupo->id }}" {{ $parametrosBusqueda->filtroPorTipoDeGrupo && in_array($tipoGrupo->id,$parametrosBusqueda->filtroPorTipoDeGrupo) ? 'selected' : '' }}>{{ $tipoGrupo->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <!-- Por tipo de grupo -->

              @livewire('Grupos.grupos-para-busqueda',[
              'id' => 'filtroGrupo',
              'class' => 'col-12 mb-3',
              'label' => 'Filtrar a partir del grupo',
              'conDadosDeBaja' => 'no',
              'grupoSeleccionadoId' => $parametrosBusqueda->filtroGrupo
              ])

              <!-- Por sede -->
              <div class="col-12 mb-3">
                <label for="filtroPorSedes" class="form-label">Fitrar por sedes </label>
                <select id="filtroPorSedes" name="filtroPorSedes[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($sedes  as $sede)
                  <option value="{{ $sede->id }}" {{ $parametrosBusqueda->filtroPorSedes && in_array($sede->id,$parametrosBusqueda->filtroPorSedes) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <!-- Por sede -->

              <!-- Por tipos de vivienda -->
              <div class="col-12 mb-3">
                <label for="filtroPorTiposDeViviendas" class="form-label">Fitrar por tipos de vivienda </label>
                <select id="filtroPorTiposDeViviendas" name="filtroPorTiposDeViviendas[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($tiposDeViviendas  as $tipoDeVivienda)
                  <option value="{{ $tipoDeVivienda->id }}" {{ $parametrosBusqueda->filtroPorTiposDeViviendas && in_array($tipoDeVivienda->id,$parametrosBusqueda->filtroPorTiposDeViviendas) ? 'selected' : '' }}>{{ $tipoDeVivienda->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <!-- Por tipos de vivienda -->


          </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

@endsection
