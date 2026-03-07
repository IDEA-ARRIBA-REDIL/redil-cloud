@php
$configData = Helper::appClasses();
use App\Models\Actividad;
use App\Models\TagGeneral;
@endphp

@extends('layouts/layoutMaster')


@section('title', 'Actividades')



<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

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

</style>
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
                title: '¡Acceso Restringido!'
                , text: 'Para acceder a esta actividad necesitas iniciar sesión'
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonText: 'Iniciar Sesión'
                , cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ///VER COMENTARIO 2 ARCHIVO WEB PASO 2 
                    $.ajax({

                        type: 'POST'
                        , data: {
                            intended_url: urlActual
                            , _token: '{{ csrf_token() }}'
                        }
                        , success: function(response) {
                            if (response.success) {
                                window.location.href = '';
                            } else {
                                Swal.fire({
                                    title: 'Error'
                                    , text: 'Hubo un problema al procesar tu solicitud en login'
                                    , icon: 'error'
                                });
                            }
                        }
                        , error: function(xhr, status, error) {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error'
                                , text: 'Ocurrió un error al procesar tu solicitud consulta de usuario'
                                , icon: 'error'
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
            dropdownParent: $('#addEventSidebar')
            , placeholder: "Filtrar por tipo actividad"
        });
    });

    ///confirmación cancelar actividad
    $('.confirmacionCancelar').on('click', function() {
        let nombre = $(this).data('nombre');
        let id = $(this).data('id');

        Swal.fire({
            title: "¿Estás seguro que deseas cancelar la actividad <b>" + nombre + "</b>?"
            , html: "Esta acción desaparecera la actividad del calendario para todos los usuarios."
            , icon: "warning"
            , showCancelButton: false
            , confirmButtonText: 'Si, Cancelar'
            , cancelButtonText: 'Atrás'
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
            title: "¿Estás seguro que deseas Activar la actividad <b>" + nombre + "</b>?"
            , html: "Esta acción listara la actividad del calendario para todos los usuarios."
            , icon: "warning"
            , showCancelButton: false
            , confirmButtonText: 'Si, Activar'
            , cancelButtonText: 'Atrás'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#activarActividad').attr('action', "/actividades/" + id + "/activar");
                $('#activarActividad').submit();
            }
        })
    });
    ///confirmación para duplicar actividad
    $('.confirmacionDuplicar').on('click', function() {
        let nombre = $(this).data('nombre');
        let id = $(this).data('id');

        Swal.fire({
            title: "¿Deseas duplicar la actividad '" + nombre + "'?"
            , text: "Se creará una copia inactiva con toda su configuración (categorías, precios, restricciones, etc.)."
            , icon: "question"
            , showCancelButton: true
            , confirmButtonText: 'Sí, Duplicar'
            , cancelButtonText: 'Cancelar'
            , customClass: {
                confirmButton: 'btn btn-primary'
                , cancelButton: 'btn btn-outline-danger ms-1'
            }
            , buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Apuntamos al nuevo formulario y lo enviamos
                $('#duplicarActividad').attr('action', "/actividades/" + id + "/duplicar");
                $('#duplicarActividad').submit();
            }
        });
    });

</script>
@endsection

@section('page-script')
@endsection




@section('content')

<h4 class="mb-1 fw-semibold text-primary">Listado </h4>
<p class="mb-4">Gestiona las actividades de tu congregación</p>

<div class="row">
    <!-- Upcoming Webinar -->
    <div class="col-sm-12 col-lg-12 col-md-12 ">

        <form id="formulario" class="forms-sample" method="GET" action="{{ route('actividades.listado') }}">
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
                    <button type="button" style="width:126px;padding:16px 10px;margin-top:-20px" class="btn btn-meddium btn-outline-secondary fw-semibold" id="btnFiltro" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
                        Filtros <i class="ti ti-filter"></i>
                    </button>
                </div>


            </div>

        </form>
        <hr>
        <div class="row equal-height-row g-4 mt-1">
            @foreach ($actividades as $actividad)
            <div class="col equal-height-col col-lg-4 col-md-4 col-sm-6 col-12 col-xxl-4">
                <div class="card border rounded p-0  h-100">
                    <div class="card-header p-0 m-0">
                        @if (isset($actividad->banner->id))
                        <div style="background-position: center !important;background-size: contain !important;min-height: 165px;background-image: url('{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre) : $configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $actividad->banner->nombre }}') !important" class="bg-label-primary rounded text-center">

                        </div>
                        @else
                        <div class="rounded-top text-center">
                            <img class="img-fluid p-0 rounded-top " src="{{ asset('assets/img/illustrations/actividad.png') }}" alt="Card girl image" />
                        </div>
                        @endif


                    </div>

                    <div class="card-body">
                        @if (isset($usuario->id))
                        <?php
                                    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();
                                    ?>
                        @if (count($tiposCargo) > 0 || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))
                        <div class="dropdown float-end mt-3">
                            <button class="btn  btn-text-secondary  border p-1 me-n1" type="button" id="assignmentProgress" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical ti-md text-muted"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="assignmentProgress">
                                @if ($rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))

                                @endif

                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.actualizar', $actividad) }}">
                                    Actualizar</a>

                                @if ($tiposCargo->contains('pestana_categorias', true))
                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.categorias', $actividad) }}">
                                    Categorias</a>
                                @endif
                                @if ($tiposCargo->contains('pestana_abonos', true))
                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.abonos', $actividad) }}"> Abonos</a>
                                @endif
                                @if ($tiposCargo->contains('pestana_formulario', true))
                                @if(!$actividad->tipo->es_escuelas)
                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.formulario', $actividad) }}">
                                    Formulario</a>
                                @endif
                                @endif
                                @if ($rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))
                                <a target="_blank" data-id="{{ $actividad->id }}" data-nombre="{{ $actividad->nombre }}" class="dropdown-item confirmacionDuplicar" href="javascript:void(0);">Duplicar
                                </a>
                                @endif
                                @if($actividad->tipo->tipo_escuelas == false )
                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.categorias', $actividad) }}">Categorias</a>

                                @else
                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.categoriasEscuelas', $actividad) }}">Categorias Escuelas</a>

                                @endif
                                <a target="_blank" class="dropdown-item" href="{{ route('actividades.abonos', $actividad) }}">Abonos</a>                           
                                <a target="_blank" class=" dropdown-item" href="{{ route('actividades.formularioActividad', $actividad) }}">Gestionar formulario</a>                            
                                <a target="_blank" class=" dropdown-item" href="{{ route('actividades.encargadosActividad', $actividad) }}">Encargados</a>
                                <a target="_blank" class=" dropdown-item" href="{{ route('actividades.asistenciasActividad', $actividad) }}">Gestionar asistencias</a>
                                <a target="_blank" class=" dropdown-item" href="{{ route('actividades.multimedia', $actividad) }}">Multimedia</a>
                              
                                <a target="_blank" class=" dropdown-item" href="{{ route('actividades.dashboardFormularios', $actividad) }}">
                                Gestión  de inscripciones</a>

                                @if (
                                $tiposCargo->contains('pestana_general', true) ||
                                $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))
                                @if ($actividad->activa == true)
                                <a data-id="{{ $actividad->id }}" data-nombre="{{ $actividad->nombre }}" class="dropdown-item confirmacionCancelar" href="javascript:void(0);"> Inactivar</a>
                                @else
                                <a data-id="{{ $actividad->id }}" data-nombre="{{ $actividad->nombre }}" class="dropdown-item confirmacionActivar" href="javascript:void(0);"> Activar</a>
                                @endif
                                @endif

                            </div>
                        </div>
                        @endif
                        @endif
                        <div class="mt-3">
                            <h5 class="mb-5 mt-5 text-uppercase fw-semibold">{{ $actividad->nombre }} 
                               
                            </h5>
                             @if ($actividad->activa == false)
                                <span style="font-size:14px" class="badge btn-danger "><b>Inactiva</b></span>
                                @endif
                            @php
                            $tagsActividad = Actividad::find($actividad->id)
                            ->tags()
                            ->pluck('tag_id')
                            ->toArray();
                            $tags = TagGeneral::whereIn('id', $tagsActividad)->get();
                            @endphp

                            @foreach ($tags as $tag)
                            <span class=" px-3 py-1 rounded-pill text-bg-info">{{ $tag->nombre }}</span>
                            @endforeach

                            <div style="width: 90%;" class="small mt-3 descripcion-corta">{!! $actividad->descripcion !!}.
                            </div>
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
                                        <h6 class="mb-0 text-nowrap"><b>{{ $actividad->fecha_finalizacion }}</b>
                                        </h6>
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
                        <a data-id='{{ $actividad->id }}' data-sesion='{{ $actividad->tipo->requiere_inicio_sesion ? '1' : '0' }}' class='validar-sesion btn btn-primary rounded-pill w-90 waves-effect waves-light' href="{{ route('actividades.perfil', $actividad) }}">
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
        <form id="formularioOffset" class="forms-sample" method="GET" action="{{ route('actividades.listado') }}">
            <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="addEventSidebarLabel">

                <div class="offcanvas-header my-1">
                    <h4 class="offcanvas-title fw-bold text-primary" id="addEventSidebarLabel">Filtros Actividad</h4>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body pt-0">

                    <div class="mb-3">
                        <label class="form-label" for="tipo_grupo">Tipo de actividad</label>
                        <select id="tiposActividad" name="tiposActividad[]" class="select2  form-select" multiple>
                            @foreach ($tiposActividad as $tipo)
                            <option value="{{ $tipo->id }}" {{ $tiposActividadSeleccionadas && in_array($tipo->id, $tiposActividadSeleccionadas) ? 'selected' : '' }}>
                                {{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tipo_grupo">Tags de referencia</label>
                        <select id="tags" name="tags[]" class="select2  form-select" multiple>
                            @foreach ($tagsGenerales as $tagGeneral)
                            <option @if ($tagsFiltro) {{ in_array($tagGeneral->id, $tagsFiltro) ? 'selected' : '' }} @endif value="{{ $tagGeneral->id }}">{{ $tagGeneral->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="offcanvas-footer p-5  border-top border-2 px-8">
                    <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Aplicar
                        Filtros</button>
                    <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
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

    <form id="duplicarActividad" method="POST" action="">
        @csrf
    </form>

</div>
@endsection
