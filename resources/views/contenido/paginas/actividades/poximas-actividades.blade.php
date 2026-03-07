@php
    $configData = Helper::appClasses();
   
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Actividades')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<!-- Page -->
@section('page-style')


@section('vendor-style')
    <style>
        .color-picker-container {
            width: 100px;
            /* Ajusta este valor al tamaño que necesites */

        }

        .pickr .pcr-button {
            height: 38px !important;
            width: 40px !important;
            border: solid 1px #3e3e3e;
        }
    </style>


    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
     'resources/assets/vendor/libs/pickr/pickr-themes.scss', 
     'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 
   
  ])


@endsection


@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 
    'resources/assets/vendor/libs/pickr/pickr.js', 
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
 
  
      ])

@endsection


@section('page-script')
<script>

        ///confirmación cancelar actividad
        $('.confirmacionCancelar').on('click', function() {
            let nombre = $(this).data('nombre');
            let id = $(this).data('id');

            Swal.fire({
                title: "¿Estás seguro que deseas cancelar la actividad <b>" + nombre + "</b>?",
                html: "Esta acción desaparecera la actividad del calendario para todos los usuarios.",
                icon: "warning",
                showCancelButton: false,
                confirmButtonText: 'Si, Cancelar',
                cancelButtonText: 'Atrás'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#activarActividad').attr('action', "/actividades/" + id + "/cancelar");
                    $('#activarActividad').submit();
                }
            })
        });

        ///confirmación para activar actividad
        $('.confirmacionActivar').on('click', function() {
            let nombre = $(this).data('nombre');
            let id = $(this).data('id');

            Swal.fire({
                title: "¿Estás seguro que deseas Activar la actividad <b>" + nombre + "</b>?",
                html: "Esta acción listara la actividad del calendario para todos los usuarios.",
                icon: "warning",
                showCancelButton: false,
                confirmButtonText: 'Si, Activar',
                cancelButtonText: 'Atrás'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#activarActividad').attr('action', "/actividades/" + id + "/activar");
                    $('#activarActividad').submit();
                }
            })
        });
</script>

@endsection


@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Listado </h4>
    <p class="mb-4">Gestiona las actividades de tu congregación</p>

    <form class="forms-sample" method="GET" action="{{ route('actividades.listado') }}">
        <div class="col-12 offset-md-2 col-md-8 d-flex">
          <div class="input-group">
            <input id="buscar" name="buscar" type="text" value="{{$parametrosBusqueda->buscar}}" class="form-control" placeholder="Busqueda..." aria-label="Recipient's username" aria-describedby="button-addon2">
            <button class="btn btn-outline-primary px-2 px-md-3" type="submit" id="button-addon2"><i class="ti ti-search"></i></button>
            @if($parametrosBusqueda->bandera == 1)
            <a href="{{ route('actividades.listado') }}" class="btn btn-outline-danger" type="submit"><i class="ti ti-x"></i></a>
            @endif         
          </div>
          <!-- Button trigger modal -->
        </div>
      </form>

    <!-- Upcoming Webinar -->
    <div class="row">
    @foreach($actividades as $actividad)
    <div class="col-md-4 col-xxl-4 mb-6">
        <div class="card h-100">
        
        <div class="card-body">
            @if(isset($actividad->banner->id))
            <div style="background-position: center !important;background-size: cover !important;min-height: 165px;background-image: url('{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/banner-actividad/'.$actividad->banner->nombre)  : $configuracion->ruta_almacenamiento.'/img/banner-actividad/'.$actividad->banner->nombre }}') !important" class="bg-label-primary rounded text-center">
        
            </div>
            @else
            <div class="bg-label-primary rounded text-center">
                <img class="img-fluid " src="{{ asset('assets/img/illustrations/girl-with-laptop.png') }}" alt="Card girl image" width="140" />
            </div>
            @endif
            {{$usuario}}
            @if(isset($usuario->id))
           <?php 
                $tiposCargo=$usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id',$actividad->id)->get();
           ?>
           
            <div class="dropdown float-end mt-3">
                <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button" id="assignmentProgress" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="ti ti-dots-vertical ti-md text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="assignmentProgress">
                @if($rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))
                  <a class="dropdown-item" href="{{route('actividades.actualizar', $actividad)}}">Modificar</a>
                @endif
                    @if($tiposCargo->contains('pestana_general', true))                 
                    <a class="dropdown-item" href="{{route('actividades.actualizar', $actividad)}}"> Actualizar</a>
                    @endif
                    @if($tiposCargo->contains('pestana_categorias', true))                 
                        <a class="dropdown-item" href="{{route('actividades.categorias', $actividad)}}"> Categorias</a>
                    @endif
                    @if($tiposCargo->contains('pestana_abonos', true))                 
                    <a class="dropdown-item" href="{{route('actividades.abonos', $actividad)}}"> Abonos</a>
                    @endif
                    @if($tiposCargo->contains('pestana_formulario', true))                 
                    <a class="dropdown-item" href="{{route('actividades.formulario', $actividad)}}"> Formulario</a>
                    @endif
                
                    @if($tiposCargo->contains('pestana_general', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))
                        @if($actividad->activa == true)    
                        <a data-id="{{$actividad->id}}" data-nombre="{{$actividad->nombre}}" class="dropdown-item confirmacionCancelar" href="javascript:void(0);"> Inactivar</a>
                        @else
                        <a  data-id="{{$actividad->id}}" data-nombre="{{$actividad->nombre}}" class="dropdown-item confirmacionActivar" href="javascript:void(0);"> Activar</a>
                        @endif
                    @endif 
                
                </div>
            </div>
            @endif
            <div class="mt-3">
                <h5 class="mb-2 mt-3">{{$actividad->nombre}} @if($actividad->activa == false) <span style="font-size:14px"><b>(Inactiva)</b></span> @endif</h5>
                <p class="small">{{$actividad->descripcion}}.</p>
            </div>
            <div class="row mt-4 mb-4 g-3">
                <div class="col-6">
                    <div class="d-flex">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-calendar-event ti-28px"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-0 text-nowrap"><b>{{$actividad->fecha_inicio}}</b></h6>
                        <small>Fecha inicio</small>
                    </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-calendar-event ti-28px"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-0 text-nowrap"><b>{{$actividad->fecha_finalizacion}}</b></h6>
                        <small>Fecha fin</small>
                    </div>
                    </div>
                </div>
            </div>
          
        </div>
        <div class="card-footer d-flex">
            <a  class='btn btn-primary w-90 waves-effect waves-light' href="{{route('actividades.perfil', $actividad)}}" > Ver más</a>
        </div>
     
         </div>
    </div>    
    @endforeach
    
    </div>
    <div class="row my-3">
        @if($actividades)
        <p> {{$actividades->lastItem()}} <b>de</b> {{$actividades->total()}} <b>actividades - Página</b> {{ $actividades->currentPage() }} </p>
        {!! $actividades->appends(request()->input())->links() !!}
        @endif
      </div>
    <!--/ Upcoming Webinar -->
    <form id="cancelarActividad" method="POST" action="">
        @csrf
    </form>

    <form id="activarActividad" method="POST" action="">
        @csrf
    </form>
@endsection
