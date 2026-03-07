@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sedes')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

@section('page-script')
<script type="module">
  $('.confirmacionEliminar').on('click', function ()
  {
    let nombre = $(this).data('nombre');
    let id = $(this).data('id');

    Swal.fire({
      title: "¿Estás seguro que deseas eliminar a <b>"+nombre+"</b>?",
      html: "Esta acción no es reversible.",
      icon: "warning",
      showCancelButton: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#eliminarSede').attr('action',"/sede/"+id+"/eliminar");
        $('#eliminarSede').submit();
      }
    })
  });
</script>

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
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Sedes </h4>

@include('layouts.status-msn')


  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('sede.lista') }}">
    <div class="row mt-5">
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input id="buscar" name="buscar" type="text" value="{{$buscar}}" class="form-control" placeholder="Busqueda..." aria-describedby="btnBusqueda">

          @if($buscar)
          <span id="borrarBusquedaPorPalabra" class="input-group-text"><i class="ti ti-x"></i></span>
          @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          @endif
        </div>
      </div>
      <div class="col-3 col-md-8 d-flex justify-content-end">
      </div>
      @if($sedes)
      <span class="text-center py-3">{{ $sedes->total() > 1 ? $sedes->total().' Sedes' : $sedes->total().' Sede' }}  {!! $buscar ? '(Con busqueda <b>"'.$buscar.'"</b>)' : '' !!}</span>
      @endif
    </div>
  </form>

  <!-- lista de sedes -->
  <div class="row g-4 mt-1">
  @foreach($sedes as $sede)
    <div class="col-12 col-xl-4 col-md-6">

      <div class="card ">
        <img class="card-img-top object-fit-cover" style="height: 130px;"  src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/sedes/banners/'.$sede->foto) : Storage::url($configuracion->ruta_almacenamiento.'/img/sedes/banners/'.$sede->portada) }}" alt="Card imagen {{ $sede->nombre }}" />
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-start">
              <div class="me-2 mt-1">
                <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $sede->tipo ? $sede->tipo->nombre : 'No definido'}}</h5>
                <div class="client-info fw-semibold text-black">{{ $sede->nombre }}</div>
              </div>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  @if($rolActivo->hasPermissionTo('sedes.opcion_ver_perfil_sede'))
                    <li><a class="dropdown-item" href="{{ route('sede.perfil', $sede)}}">Perfil</a></li>
                  @endif
                  @if($rolActivo->hasPermissionTo('sedes.opcion_modificar_sede'))
                    <li><a class="dropdown-item" href="{{ route('sede.modificar', $sede)}}">Modificar</a></li>
                  @endif
                  <hr class="dropdown-divider">
                  @if($rolActivo->hasPermissionTo('sedes.opcion_eliminar_sede'))
                    <li><a class="dropdown-item confirmacionEliminar text-danger" data-nombre="{{ $sede->nombre }}" data-id="{{ $sede->id }}" href="javascript:;">Eliminar</a></li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">

          <div class="d-flex flex-column mb-3">
            <span class="fw-bold text-black">Personas</span>

            <div class="d-flex flex-row">
              <i class="ti ti-users text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Todas:</small>
                <small class="fw-semibold ms-1 text-black ">{{ $sede->usuarios()->select('id')->count() }}</small>
              </div>
            </div>

            <div class="d-flex flex-row">
              <i class="ti ti-user-x text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Inactivos en grupos</small>
                <small class="fw-semibold ms-1 text-black">{{ $sede->usuariosInactivosGrupos() }}</small>
              </div>
            </div>

            <div class="d-flex flex-row">
              <i class="ti ti-building-church text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Inactivos en reunión</small>
                <small class="fw-semibold ms-1 text-black"> {{ $sede->usuariosInactivosReuniones() }}</small>
              </div>
            </div>
          </div>

          <div class="d-flex flex-column mb-3">
            <span class="fw-bold text-black">Grupos</span>

            <div class="d-flex flex-row">
              <i class="ti ti-atom-2 text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Todos:</small>
                <small class="fw-semibold ms-1 text-black ">{{ $sede->grupos()->select('id')->count() }}</small>
              </div>
            </div>

            <div class="d-flex flex-row">
              <i class="ti ti-exclamation-circle text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Sin actividad</small>
                <small class="fw-semibold ms-1 text-black">{{ $sede->gruposNoReportados() }}</small>
              </div>
            </div>
          </div>

          <div class="d-flex flex-column mb-2">
            <span class="fw-bold text-black">Encargados</span>
            @if($sede->encargados()->count() > 0)
              @foreach($sede->encargados() as $encargado)
              <div class="d-flex flex-row">
                  <i class="{{ $encargado->icono }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $encargado->tipo_usuario }}"></i>
                <div class="d-flex flex-column">
                  <small class="fw-semibold ms-1 text-black">{{ $encargado->nombre }}</small>
                </div>
              </div>
              @endforeach
            @else
             <div class="d-flex flex-row">
                <div class="d-flex flex-column">
                  <small class="fw-semibold text-black">Sin encargados</small>
                </div>
              </div>
            @endif
          </div>

        </div>
      </div>
    </div>
    @endforeach
  </div>
  <!--/ lista de sedes -->

  <div class="row my-3">
    @if($sedes)
    {!! $sedes->appends(request()->input())->links() !!}
    @endif
  </div>

  <form id="eliminarSede" method="POST" action="">
    @csrf
  </form>


@endsection
