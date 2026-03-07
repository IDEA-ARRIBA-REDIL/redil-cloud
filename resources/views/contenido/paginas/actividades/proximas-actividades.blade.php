@php
    $configData = Helper::appClasses();
    use App\Models\Actividad;
    use App\Models\TagGeneral;

@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
@extends('layouts/blankLayout')

@section('title', 'Actividades')
<!-- laravel CRUD token -->

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

        body {
            background: #fff !important;
            overflow-x: hidden;
        }

        .pickr .pcr-button {
            height: 38px !important;
            width: 40px !important;
            border: solid 1px #3e3e3e;
        }

        .descripcion-corta {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 15px;
            line-height: 24px;
            color: #3B3B3B;
        }

        .offcanvas-backdrop {
            background-color: #000 !important;
        }

        @media(max-width:750px)
        {
            .btn-filtros{
                width:126px;padding:16px 10px;margin-top:20px
            }
        }

        @media(min-width:800px)
        {
            .btn-filtros{
                width:126px;padding:16px 10px;margin-top:-20px
            }
        }
    </style>


    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection


@section('page-script')
    <script>
        /// INICIO SCRIPT PARA RUTAS PUBLICAS CON REDIRECT INTERNO
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.validar-sesion').on('click', function(e) {
            e.preventDefault();

            const requiereSesion = $(this).data('sesion');
            /// AQUI EL REDIRECT ES LA DIRECCION ACTUAL PERO ACA PUEDE IR A DONDE QUEREMOS QUE REDIRIJA
            const urlActual = window.location.href;
            /// ESTA CONDICION APLICA SOLO PARA ESTA VISTA
            if (requiereSesion === 1) {
                Swal.fire({
                    title: '¡Acceso restringido!',
                    text: 'Para acceder a esta actividad necesitas iniciar sesión',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Iniciar sesión',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        ///VER COMENTARIO 2 ARCHIVO WEB PASO 2
                        $.ajax({
                            url: '{{ route('save.redirect') }}',
                            type: 'POST',
                            data: {
                                intended_url: urlActual,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    window.location.href = '{{ route('login') }}';
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Hubo un problema al procesar tu solicitud en login',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Ocurrió un error al procesar tu solicitud consulta de usuario',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            } else {
                // Si no requiere sesión, redirige directamente
                window.location.href = $(this).attr('href');
            }
        });
        ///

        $(document).ready(function() {
            $('.select2').select2({
                dropdownParent: $('#addEventSidebar'),
                placeholder: "Filtrar por tipo actividad"
            });
        });

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
                title: "¿Estás seguro que deseas activar la actividad <b>" + nombre + "</b>?",
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

@section('page-script')
@endsection
@auth
    @include('layouts/sections/navbar/navbar')
@else
    @include('layouts/sections/navbar/navbar-front')
    @endif


@section('content')
    <div  style="margin-top:100px;"  class=" container-xxl">
    <!-- Upcoming Webinar -->
    <div class="col-sm-12 col-lg-12 col-md-12 ">
        <h4 class="mb-1 fw-semibold text-primary">Proximas actividades</h4>
            <form id="formulario" class="forms-sample" method="GET" action="{{ route('actividades.proximas') }}">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-md-6 mb-2">
                        <div class="input-group">
                            <input id="buscar" name="buscar" type="text" value="{{ $parametrosBusqueda->buscar }}" class="form-control" placeholder="Buscar evento..." aria-label="Recipient's username" aria-describedby="button-addon2">
                            <button class="btn btn-outline-gray px-2 px-md-3" type="submit" id="button-addon2"><i class="ti ti-search"></i></button>
                                @if ($parametrosBusqueda->bandera == 1)
                                <a href="{{ route('actividades.listado') }}" class="btn btn-outline-danger" type="submit"><i class="ti ti-x"></i></a>
                                @endif
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 col-md-6 text-end mb-2">
                        <button  type="button"  class="btn btn-filtros btn-meddium btn-outline-secondary fw-semibold"
                        id="btnFiltro" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
                        Filtros <i class="ti ti-filter"></i>
                    </button>
                    </div>


                </div>

              </form>
              <hr>
                <div  class="row">
                @foreach ($actividades as $actividad)

                <div class="mt-5 col-md-4 col-lg-4 col-xxl-4">
                    <div class="card boxShadow  p-0  h-100">
                        <div class="card-header p-0 m-0">
                            @if (isset($actividad->banner->id))
                            <div style="background-position: center !important;background-size: cover !important;min-height: 165px;background-image: url('{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre) : $configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre }}') !important" class="bg-label-primary rounded text-center">

                            </div>
                            @else
                            <div class="rounded-top text-center">
                                <img class="img-fluid p-0 rounded-top " src="{{ asset('assets/img/illustrations/actividad.png') }}" alt="Card girl image"  />
                            </div>
                            @endif


                        </div>
                        <div class="card-body">

                            <div class="mt-3">
                                <h5 class="mb-5 mt-5 text-uppercase fw-semibold">{{ $actividad->nombre }} @if ($actividad->activa == false) <span style="font-size:14px"><b>(Inactiva)</b></span> @endif</h5>
                                @php
                                 $tagsActividad=Actividad::find($actividad->id)->tags()->pluck('tag_id')->toArray();
                                 $tags=TagGeneral::whereIn('id',$tagsActividad)->get();
                                @endphp

                                @foreach ($tags as $tag)
                                 <span class=" px-3 py-1 rounded-pill text-bg-info">{{ $tag->nombre }}</span>
                                @endforeach

                                <div  style="width: 90%;" class="small mt-3 descripcion-corta">{!! $actividad->descripcion !!}.</div>
                            </div>
                            <div class="row mt-4 mb-4 g-3">
                                <div class="col-6 p-0">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar m-e1" style="margin-top: -2px;">
                                            <span style="color: #d3d2d2;"><i style="font-size:25px" class="ti ti-calendar-week"></i></span>
                                        </div>
                                        <div style="margin-left:-12px">
                                            <h6 class="mb-0 text-nowrap"><b>{{ $actividad->fecha_inicio }}</b></h6>
                                            <small>Fecha inicio</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar m-e1" style="margin-top: -2px;">
                                            <span style="color: #d3d2d2;"><i style="font-size:25px" class="ti ti-calendar-week"></i></span>
                                        </div>
                                        <div style="margin-left:-12px">
                                            <h6 class="mb-0 text-nowrap"><b>{{ $actividad->fecha_finalizacion }}</b></h6>
                                            <small>Fecha fin</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div style="text-align: right;" class="card-footer">
                            @auth
                                {{-- Si está autenticado, enlace directo sin validación --}}
                                <a href="{{ route('actividades.perfil', $actividad) }}" class="btn btn-primary rounded-pill w-90 waves-effect waves-light">
                                    Ver más
                                </a>
                            @else
                                {{-- Si no está autenticado, enlace con validación --}}
                                <a
                                    data-id='{{ $actividad->id }}'
                                    data-sesion='{{ $actividad->tipo->requiere_inicio_sesion ? '1' : '0' }}'
                                    class='validar-sesion btn btn-primary rounded-pill w-90 waves-effect waves-light'
                                    href="{{ route('actividades.perfil', $actividad) }}"
                                >
                                    Ver más
                                </a> @endauth
        </div>
    </div>
    </div>

    @endforeach
    </div>
    <div class="row my-3 mt-5">
        @if ($actividades)
            <p> {{ $actividades->lastItem() }} <b>de</b> {{ $actividades->total() }} <b>actividades - Página</b>
                {{ $actividades->currentPage() }} </p>
            {!! $actividades->appends(request()->input())->links() !!}
        @endif
    </div>

    <!-- FullCalendar Offcanvas -->
    <form id="formularioOffset" class="forms-sample" method="GET" action="{{ route('actividades.proximas') }}">
        <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar"
            aria-labelledby="addEventSidebarLabel">

            <div class="offcanvas-header my-1">
                <h4 class="offcanvas-title fw-bold text-primary" id="addEventSidebarLabel">Filtros Actividad</h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">

                <div class="mb-3">
                    <label class="form-label" for="tipo_grupo">Tipo de actividad</label>
                    <select id="tiposActividad" name="tiposActividad[]" class="select2  form-select" multiple>
                        @foreach ($tiposActividad as $tipo)
                            <option value="{{ $tipo->id }}"
                                {{ $tiposActividadSeleccionadas && in_array($tipo->id, $tiposActividadSeleccionadas) ? 'selected' : '' }}>
                                {{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="tipo_grupo">Tags de referencia</label>
                    <select id="tags" name="tags[]" class="select2  form-select" multiple>
                        @foreach ($tagsGenerales as $tagGeneral)

                            <option
                                @if ($tagsFiltro) {{ in_array($tagGeneral->id, $tagsFiltro) ? 'selected' : '' }} @endif
                                value="{{ $tagGeneral->id }}">{{ $tagGeneral->nombre }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            <div class="offcanvas-footer p-5  border-top border-2 px-8">
                <button type="submit"
                    class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Aplicar
                    Filtros</button>
                <button type="button" data-bs-dismiss="offcanvas"
                    class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
            </div>

        </div>
    </form>


    </div>

    <!--/ Upcoming Webinar -->
    <form id="cancelarActividad" method="POST" action="">
        @csrf
    </form>

    <form id="activarActividad" method="POST" action="">
        @csrf
    </form>

    </div>
@endsection
