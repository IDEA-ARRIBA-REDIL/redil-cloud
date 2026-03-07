@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Informes')

<!-- Page -->
@section('page-style')
@vite([

'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection


@section('page-script')
<script type="module">

  $(document).ready(function() {
    $('.select2').select2({
    });
  });
</script>

<script>
    // Se ejecuta cuando el documento está listo
    $(document).ready(function() {
        // Escucha el evento 'change' en el select inicializado con Select2
        $('#tipoInformeId').on('change', function() {
            // Cuando cambia, encuentra el formulario más cercano y envíalo.
            $(this).closest('form').submit();
        });
    });
</script>

<script>
    // Aseguramos que el script se ejecuta después de que el DOM esté cargado
    document.addEventListener('DOMContentLoaded', function () {

      // Buscamos todos los botones con la clase 'gestionarRoles'
      const botonesGestionar = document.querySelectorAll('.gestionarRoles');

      // Añadimos un listener a cada botón
      botonesGestionar.forEach(button => {
          button.addEventListener('click', function () {
              // Obtenemos el ID del informe desde el atributo data-id
              const informeId = this.getAttribute('data-id');

              // Usamos Livewire.dispatch para enviar el evento al componente del modal
              // El primer parámetro es el nombre del evento que definimos con #[On]
              // El segundo es un objeto con los datos que queremos enviar
              Livewire.dispatch('abrirModalRoles', { informeId: informeId });
          });
      });

    });
</script>
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Informes</h4>


  @include('layouts.status-msn')
  @livewire('Informes.modal-gestionar-roles-informe')

  <hr>

  <form id="formBuscar" class="forms-sample p-5" method="GET" action="{{ route('informe.lista') }}">
    <div class="row mt-5">
        <div class="col-4 mb-3">
          <label class="text-black">Filtrar por tipo</label>
          <select id="tipoInformeId" name="tipoInformeId" class="select2 form-select">
              <option value="">Todos</option>
              @foreach($tiposInformes as $tipo)
                  <option value="{{ $tipo->id }}" {{ $tipo->id == $tipoInforme ? 'selected' : '' }}>
                      {{ $tipo->nombre }}
                  </option>
              @endforeach
          </select>
        </div>

        <div class="col-3 col-md-8 ">

          @if( $rolActivo->hasPermissionTo('informes.privilegio_configurar_semanas'))
          <div class=" d-flex justify-content-end">
            <a href="{{ route('informe.configuracionSemanas') }}" type="button" class="d-flex justify-content-end btn btn-outline-secondary waves-effect px-2 px-md-5  me-1"><span class="d-none d-md-block fw-semibold">Configurar semanas</span><i class="ti ti-calendar-week ms-1"></i> </a>
          </div>
          @endif
        </div>
    </div>
  </form>


   <!-- lista de reportes -->
   <div class="p-5">
    @foreach($informes as $informe)
    <div class="d-flex border-bottom py-2">

      <div class="flex-fill row g-1 g-md-3 d-none">

        <div class="col-12 col-md-1">
          <button type="button" class="gestionarRoles btn btn-sm rounded-pill btn-outline-primary waves-effect mb-1" data-id="{{$informe->id}}">
              <i class="ti ti-user-cog"></i>
          </button>
          <a href="{{ route('informe.cambiarEstado', $informe->id) }}"  class="btn btn-sm rounded-pill {{ $informe->activo ? 'btn-outline-secondary' : 'btn-outline-primary' }}  waves-effect mb-1" ><i class="ti {{ $informe->activo ? 'ti-eye-off' : 'ti-eye' }}"></i></a>
        </div>

        <div class="col-12 col-md-3">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Informe:</small>
              <small class="fw-semibold ms-1 text-black ">{{ $informe->nombre }}</small>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-1">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Tipo:</small>
              <small class="fw-semibold ms-1 text-black ">{{ $informe->tipo_informe_id ? $informe->tipoInforme->nombre : 'No indicado' }}</small>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-1">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Estado:</small>
              <small class="fw-semibold ms-1 text-black ">{{ $informe->activo ? 'Activo' : 'Inactivo' }}</small>
            </div>
          </div>
        </div>

        <div class="d-none col-12 d-md-block col-md-6">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Descripción:</small>
              <small class="fw-semibold ms-1 text-black ">{{ $informe->descripcion ? $informe->descripcion : 'Sin descripción' }}</small>
            </div>
          </div>
        </div>

      </div>

      <div class="flex-fill row g-1 g-md-3">

        <div class="col-12 col-md-3">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="fw-semibold text-black ms-1">Informe
                <a href="{{ route('informe.cambiarEstado', $informe->id) }}"  class="py-0 btn btn-sm rounded-pill {{ $informe->activo ? 'btn-text-secondary' : 'text-primary' }}  waves-effect mb-1" >
                  <i class="ti {{ $informe->activo ? 'ti-eye-off' : 'ti-eye' }}"></i>
                </a>
              </small>
              <small class="ms-1 ">{{ $informe->nombre }}</small>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-1">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Tipo:</small>
              <small class="fw-semibold ms-1 text-black ">{{ $informe->tipo_informe_id ? $informe->tipoInforme->nombre : 'No indicado' }}</small>
            </div>
          </div>
        </div>

        <div class="d-none col-12 d-md-block col-md-6">
          <div class="d-flex flex-row">
            <div class="d-flex flex-column">
              <small class="text-black ms-1">Descripción:</small>
              <small class="fw-semibold ms-1 text-black ">{{ $informe->descripcion ? $informe->descripcion : 'Sin descripción' }}</small>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-2">
          <div class="d-flex flex-row align-items-center justify-content-center h-100 text-center">
            <span class="badge rounded-pill fw-light {{ $informe->activo ? 'text-bg-primary' : 'text-bg-secondary' }}">
              <span class="text-white"> {{ $informe->activo ? 'Activo' : 'Inactivo' }}</span>
            </span>
          </div>
        </div>

      </div>

      <div class="my-auto d-flex">
        @if($rolActivo->hasPermissionTo('informes.privilegio_administrar_informes'))
        <button type="button" class="gestionarRoles btn btn-sm rounded-pill btn-outline-primary waves-effect" data-id="{{$informe->id}}">
          <i class="ti ti-user-cog"></i>
        </button>
        @endif
        <a @if($informe->add_id_a_la_url==TRUE) href="{{ route($informe->link, $informe->id) }}" @else href="{{ route($informe->link) }}" @endif class="my-auto btn btn-sm ms-1 rounded-pill btn-outline-secondary waves-effect">{{$informe->nombre_boton}} </a>
      </div>

    </div>
    @endforeach
  </div>
  <!--/ lista de reportes -->


  <div class="row my-3">
    @if($informes)
    <p> {{$informes->lastItem()}} <b>de</b> {{$informes->total()}} <b>informes - Página</b> {{ $informes->currentPage() }} </p>
    {!! $informes->appends(request()->input())->links() !!}
    @endif
  </div>


@endsection
