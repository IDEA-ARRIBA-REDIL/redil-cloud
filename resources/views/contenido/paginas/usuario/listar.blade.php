@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Usuarios')

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


<!-- Page -->
@section('page-script')

<script type="module">

  const swiperContainer = document.querySelector('#swiper-with-pagination-cards');
  const swiper = new Swiper(swiperContainer, {
    slidesPerView: "auto",
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });

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

  $(document).ready(function() {
    $('.select2GeneradorExcel').select2({
      dropdownParent: $('#modalGeneradorExcel')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalGeneradorExcel').on('scroll', function(event) {
    $(this).find(".select2GeneradorExcel").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });

  $("#filtroFechasPasosCrecimiento1").flatpickr({
    mode: "range",
    dateFormat: "Y-m-d",
    defaultDate: ["{{ $parametrosBusqueda->filtroFechaIniPaso1 ? $parametrosBusqueda->filtroFechaIniPaso1 : ''}}", "{{ $parametrosBusqueda->filtroFechaFinPaso1 ? $parametrosBusqueda->filtroFechaFinPaso1 : ''}}"],
    locale: {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Оct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febreo', 'Мarzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      },
    },
    onChange: function(dates) {
      if (dates.length == 2) {
        var _this = this;
        var dateArr = dates.map(function(date) {
          return _this.formatDate(date, 'Y-m-d');
        });
        $('#filtroFechaIniPaso1').val(dateArr[0]);
        $('#filtroFechaFinPaso1').val(dateArr[1]);
        // interact with selected dates here
      }
    },
    onReady: function(dateObj, dateStr, instance) {
      var $cal = $(instance.calendarContainer);
      if ($cal.find('.flatpickr-clear').length < 1) {
        $cal.append('<button type="button" class="btn btn-sm btn-outline-primary flatpickr-clear mb-2">Borrar</button>');
        $cal.find('.flatpickr-clear').on('click', function() {
          instance.clear();
          $('#filtroFechaIniPaso1').val('');
          $('#filtroFechaFinPaso1').val('');
          instance.close();
        });
      }
    }
  });

  $("#filtroFechasPasosCrecimiento2").flatpickr({
    mode: "range",
    dateFormat: "Y-m-d",
    defaultDate: ["{{ $parametrosBusqueda->filtroFechaIniPaso2 ? $parametrosBusqueda->filtroFechaIniPaso2 : ''}}", "{{ $parametrosBusqueda->filtroFechaFinPaso2 ? $parametrosBusqueda->filtroFechaFinPaso2 : ''}}"],
    locale: {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Оct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febreo', 'Мarzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      },
    },
    onChange: function(dates) {
      if (dates.length == 2) {
        var _this = this;
        var dateArr = dates.map(function(date) {
          return _this.formatDate(date, 'Y-m-d');
        });
        $('#filtroFechaIniPaso2').val(dateArr[0]);
        $('#filtroFechaFinPaso2').val(dateArr[1]);
        // interact with selected dates here
      }
    },
    onReady: function(dateObj, dateStr, instance) {
      var $cal = $(instance.calendarContainer);
      if ($cal.find('.flatpickr-clear').length < 1) {
        $cal.append('<button type="button" class="btn btn-sm btn-outline-primary flatpickr-clear mb-2">Borrar</button>');
        $cal.find('.flatpickr-clear').on('click', function() {
          instance.clear();
          $('#filtroFechaIniPaso2').val('');
          $('#filtroFechaFinPaso2').val('');
          instance.close();
        });
      }
    }
  });

  $(".clearAllItems").click(function() {
    var selectId = $(this).data('select');
    $('#' + selectId).val(null).trigger('change');
  });

  $(".selectAllItems").click(function() {
    var selectId = $(this).data('select');
    $('#' + selectId).select2('destroy').find('option').prop('selected', 'selected').end().select2();
  });
</script>

<script>
  function darBajaAlta(usuarioId, tipo)
  {
    Livewire.dispatch('abrirModalBajaAlta', { usuarioId: usuarioId, tipo: tipo });
  }

  function comprobarSiTieneRegistros(usuarioId)
  {
    Livewire.dispatch('comprobarSiTieneRegistros', { usuarioId: usuarioId });
  }

  function eliminacionForzada(usuarioId)
  {
    Livewire.dispatch('confirmarEliminacion', { usuarioId: usuarioId });
  }

</script>

<script>
    const buscarInput = document.getElementById('buscar');
    const filtroBuscarInput = document.getElementById('filtroBuscar');
    const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
    const formularioBuscar = document.getElementById('busquedaAvanzada');
    let timeoutId;
    const delay = 1000; // Tiempo en milisegundos después de dejar de escribir para enviar el formulario

    buscarInput.addEventListener('input', function() {
        clearTimeout(timeoutId); // Limpiar cualquier timeout anterior

        if (this.value.length >= 3) {
          filtroBuscarInput.value = this.value;
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
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {
      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;

      const form = document.getElementById('busquedaAvanzada');
      const input = form.querySelector('[id="' + field + '"]');

      if (input && $(input).hasClass('select2BusquedaAvanzada')) {
        // Si es un Select2, usa el método 'val' de Select2 para eliminar la opción
        let currentValues = $(input).val();
        if (Array.isArray(currentValues)) {
            // Si es un select múltiple
            const newValue = currentValues.filter(v => v != value);
            $(input).val(newValue).trigger('change');
        } else {
            // Si es un select simple
            $(input).val(null).trigger('change');
        }
      } else if (input && input.tagName === 'SELECT' && input.multiple) {
        // Si es un select múltiple nativo (poco probable con Select2, pero por si acaso)
        let currentValues = Array.from(input.selectedOptions).map(option => option.value);
        const newValue = currentValues.filter(v => v != value);
        for (let i = 0; i < input.options.length; i++) {
            input.options[i].selected = newValue.includes(input.options[i].value);
        }
        $(input).trigger('change'); // Dispara el evento change para otras posibles escuchas*/
      } else if (input && input.tagName === 'SELECT') {
        // Si es un select simple nativo
        input.value = '';
      } else if (input) {
        // Si es un input normal
        input.value = '';
        if(fieldAux)
        {
          const inputAux = form.querySelector('[id="' + fieldAux + '"]');
          inputAux.value = '';
        }
      }

      form.submit();
    });
  });
</script>

@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Personas</h4>

@include('layouts.status-msn')

  <div class="row pt-5">
    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
      <div class="swiper-wrapper">
        <!-- Cards with few info -->
        @foreach( $indicadoresGenerales->chunk(4) as $chunk )
        <div class="swiper-slide">
          <div class="row equal-height-row  g-2">
            @foreach($chunk as $indicador )
            <div class="col equal-height-col col-lg-3 col-12">
              <a href="{{ route('usuario.lista', $indicador->url) }}">
                <div class="card border rounded-3 shadow-sm">
                  <div class="card-body d-flex flex-row p-3">

                    <div class="card-icon me-1">
                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/'. $indicador->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/'. $indicador->imagen) }}" alt="icono" class="me-2" width="50">
                    </div>

                    <div class="card-title mb-0">
                      <p class="text-black mb-0" style="font-size: .8125rem">{{ $indicador->nombre }}</p>
                      <h5 class="mb-0 me-2">{{ $indicador->cantidad }}</h5>
                    </div>

                  </div>
                </div>
              </a>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
        <!--/ Cards with few info -->
      </div>
      <div class="d-flex mt-10">
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>

  <hr>

  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('usuario.lista', $tipo) }}">
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
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5  me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><span class="d-none d-md-block fw-semibold">Filtros</span><i class="ti ti-filter ms-1"></i> </button>
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-3" data-bs-toggle="offcanvas" data-bs-target="#modalGeneradorExcel"><span class="d-none d-md-block fw-semibold">Descargar excel</span><i class="ti ti-file-download ms-1"></i> </button>
      </div>

      <div class="filter-tags py-3">
        <span class="text-black me-5">{{ $personas->total() > 1 ? $personas->total().' Personas' : $personas->total().' Persona' }}</span>
        @if(isset($parametrosBusqueda->tagsBusqueda) && is_array($parametrosBusqueda->tagsBusqueda))
          @foreach($parametrosBusqueda->tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($parametrosBusqueda->bandera == 1)
            <a type="button" href="{{ route('usuario.lista', $tipo) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>

    </div>
  </form>

  <!-- Listado de persona -->
  <div class="row equal-height-row g-4 mt-1">
    @if(count($personas)>0)
    @foreach($personas as $persona)
    <div class="col equal-height-col col-lg-4 col-md-4 col-sm-6 col-12">
        <!-- esta linea es para igualar en altura todas las col e igualar la altura de las cards -->
      <div  class="h-100 card border rounded">
        <img class="card-img-top object-fit-cover" style="height: 100px;" src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/banner-usuario/'.$persona->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/banner-usuario/'.$persona->portada)  }}" alt="portada {{$persona->primer_nombre}}" />

        <div class="card-body">

          <div class="user-profile-header d-flex flex-row text-start mb-2 ">
            <div class="flex-grow-1 mt-n5 mx-auto text-start">
              @if($persona->foto == "default-m.png" || $persona->foto == "default-f.png")
              <div class="avatar avatar-xl">
                <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
              </div>
              @else
              <div class="avatar avatar-xl">
                <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$persona->foto }}" alt="{{ $persona->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
              </div>
              @endif
            </div>
          </div>

          <div class="d-flex justify-content-between mb-5">
            <div class="d-flex align-items-start">
              <div class="me-2 mt-1">
                <h5 class="mb-1 fw-semibold text-black lh-sm"> {{ $persona->nombre(3) }} </h5>
                <div class="client-info text-black">
                  <b>Edad:</b> {{ $persona->edad() > 1 ?  $persona->edad().' años' : $persona->edad().' año'}}
                </div>
              </div>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">

                  @can('verPerfilUsuarioPolitica', [$persona, 'principal'])
                    <li><a class="dropdown-item" href="{{ route('usuario.perfil', $persona) }}">Ver perfil</a></li>
                  @endcan

                  @if($rolActivo->hasPermissionTo('consejeria.opcion_agendar_cita'))
                  <li><a class="dropdown-item" href="{{ route('consejeria.nuevaCita', $persona) }}">Agendar cita</a></li>               
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_dar_de_alta_asistente'))
                    @if($persona->trashed())
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$persona->id}}', 'alta')">Dar de alta</a></li>
                    @endif
                  @endif

                  <!-- opcion modificar  -->
                  @if($persona->esta_aprobado==TRUE)
                    @foreach( auth()->user()->formularios(2, $persona->edad()) as $formulario)
                      @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
                        <li><a class="dropdown-item" href="{{ route('usuario.modificar', [$formulario, $persona]) }}">{{$formulario->label}}</a></li>
                      @endcan
                    @endforeach
                  @elseif ($persona->esta_aprobado==FALSE)
                    @if($rolActivo->hasPermissionTo('personas.privilegio_modificar_asistentes_desaprobados'))
                      @foreach( auth()->user()->formularios(2, $persona->edad()) as $formulario)
                        @can('modificarUsuarioPolitica', [App\Models\User::class, $formulario])
                          <li><a class="dropdown-item" href="{{ route('usuario.modificar', [$formulario, $persona]) }}">{{$formulario->label}}</a></li>
                        @endcan
                      @endforeach
                    @endif
                  @endif
                  <!-- / opcion modificar  -->

                  @can('informacionCongregacionalPolitica', $persona)
                  <li><a class="dropdown-item" href="{{ route('usuario.informacionCongregacional', ['formulario' => 0 ,'usuario' => $persona]) }}">Info. congregacional</a></li>
                  @endcan

                  @can('relacionesFamiliaresUsuarioPolitica', $persona)
                  <li><a class="dropdown-item" href="{{ route('usuario.relacionesFamiliares', ['formulario' => 0 , 'usuario' => $persona]) }}">Relaciones familiares</a></li>
                  @endcan

                  @can('geoasignacionUsuarioPolitica', $persona)
                  <li><a class="dropdown-item" href="{{ route('usuario.geoAsignacion', ['formulario' => 0 ,'usuario' => $persona]) }}">Geo asignación</a></li>
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_cambiar_contrasena_asistente'))
                  <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalCambioContrasena" onclick="event.preventDefault(); document.getElementById('formCambioContrasena').setAttribute('action', 'usuarios/{{$persona->id}}/cambiar-contrasena');">Cambiar contraseña</a></li>

                  <form method="POST" id="cambiarContraseñaDefault_{{$persona->id}}" action="{{ route('usuario.cambiarContrasenaDefault',  ['usuario' => $persona ]) }}">
                    @csrf
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('cambiarContraseñaDefault_{{$persona->id}}').submit();">Cambiar contraseña default</a></li>
                  </form>
                  @endif

                  @if($rolActivo->hasPermissionTo('personas.opcion_descargar_qr'))
                  <li><a class="dropdown-item" href="{{ route('usuario.descargarCodigoQr', $persona) }}">Código QR</a></li>
                  @endif

                  <hr class="dropdown-divider">
                  @if($rolActivo->hasPermissionTo('personas.opcion_dar_de_baja_asistente'))
                    @if($persona->trashed()!=TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="darBajaAlta('{{$persona->id}}', 'baja')">Dar de baja</a></li>
                    @endif
                  @endif
                  @if($rolActivo->hasPermissionTo('personas.opcion_eliminar_asistente'))
                    @if($persona->trashed()!=TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="comprobarSiTieneRegistros('{{$persona->id}}')">Eliminar</a></li>
                    @endif
                  @endif
                  @if($rolActivo->hasPermissionTo('personas.eliminar_asistentes_forzadamente'))
                    @if($persona->trashed()==TRUE)
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacionForzada('{{$persona->id}}')">Eliminación forzada </a></li>
                    @endif
                  @endif

                </ul>
              </div>
            </div>
          </div>


          <div class="d-flex my-2 mb-5">
            <span class="badge rounded-pill px-6 fw-light " style="background-color: {{ $persona->tipoUsuario->color }}">
              <i class="{{ $persona->tipoUsuario->icono }} fs-6"></i> <span class="text-white"> {{ $persona->tipoUsuario->nombre }}</span>
            </span>
          </div>

          <div class="d-flex flex-column mb-3">
            @if($tipo=="dados-de-baja")
                <div class="d-flex flex-row">
                  <i class="ti ti-user-off text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Dado de baja por: </small>
                    <small class="fw-semibold ms-1 text-black ">{{ $persona->ultimoReporteDadoBaja() ? $persona->ultimoReporteDadoBaja()->tipo->nombre : 'No definido' }}</small>
                  </div>
                </div>
            @else
              @if(isset($persona->tipoUsuario->id))
                <!-- Actividad en grupo -->
                <div class="d-flex flex-row  mb-3">
                  <i class="ti ti-users-group text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Actividad en grupos:</small>
                    @if($persona->tipoUsuario->seguimiento_actividad_grupo==FALSE)
                    <small class="fw-semibold ms-1 text-black">Sin seguimiento</small>
                    @else
                      @if($persona->estadoActividadGrupos())
                      <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Última actividad el {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                      @else
                      <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_grupo}}">Inactivo desde {{ Carbon\Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d') }}</small>
                      @endif
                    @endif
                  </div>
                </div>

                <!-- Actividad en reuniones -->
                <div class="d-flex flex-row  mb-3">
                  <i class="ti ti-building-church text-black"></i>
                  <div class="d-flex flex-column">
                    <small class="text-black ms-1">Actividad en reuniones:</small>
                    @if($persona->tipoUsuario->seguimiento_actividad_reunion==FALSE)
                    <small class="fw-semibold ms-1 text-black">Sin seguimiento</small>
                    @else
                      @if($persona->estadoActividadReuniones())
                      <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_reunion}}">Última actividad el  {{ Carbon\Carbon::parse($persona->ultimo_reporte_reunion)->format('Y-m-d') }}</small>
                      @else
                      <small class="fw-semibold ms-1 text-black" data-bs-toggle="tooltip" data-bs-placement="top" title="Último reporte {{$persona->ultimo_reporte_reunion}}">Inactivo desde {{ Carbon\Carbon::parse($persona->ultimo_reporte_reunion)->format('Y-m-d') }}</small>
                      @endif
                    @endif
                  </div>
                </div>
              @endif
            @endif

            <!-- Servicios prestados en grupos  -->
            @if($persona->ultimoTipoServicioGrupo())
            <div class="d-flex flex-row  mb-3">
              <i class="ti ti-circle-check text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Servicios prestados en grupos:</small>
                <small class="fw-semibold ms-1 text-black">{{ $persona->ultimoTipoServicioGrupo()->nombre }}</small>
              </div>
            </div>
            @endif
          </div>

          <div class="d-flex flex-column mb-2">
            <span class="fw-bold text-black">Encargados</span>
            @if($persona->encargadosDirectos()->count() > 0)
              @foreach($persona->encargadosDirectos() as $encargado)
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
    @else
    <div class="mt-5 mb-5 py-5">
      <center>
      <i class="ti ti-user ti-xl"></i>
      <p>La busqueda no arrojo ningun resultado.</p>
      </center>
    </div>
    @endif
  </div>
  <!--/ Listado de persona -->

  <div class="row my-3">
    @if($personas)
    <p> {{$personas->lastItem()}} <b>de</b> {{$personas->total()}} <b>personas - Página</b> {{ $personas->currentPage() }} </p>
    {!! $personas->appends(request()->input())->links() !!}
    @endif
  </div>

  <!-- Modal cambio de contraseña -->
  <div class="modal fade" id="modalCambioContrasena" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <form id="formCambioContrasena" class="forms-sample" method="POST" action="">
        @csrf
        <div class="modal-content">
          <div class="modal-header d-flex flex-column">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <div class="text-center mb-4">
              <h3 class="role-title mb-2"><i class="ti ti-password ti-lg"></i> Cambio de contraseña</h3>
              <p class="text-muted">La contraseña debe contener como mínimo 5 caracteres, una letra minúscula y un número.</p>
            </div>

            <div class="row">

              <!-- Nueva Contrasena -->
              <div class="col-12 mb-3">
                <label class="form-label" for="nueva_contrasena">Nueva contraseña</label>
                <input id="nueva_contrasena" name="password" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como minimo 5 caracteres alfanumericos, es decir, debe contener como minimo letras y numeros.  "/>
              </div>

              <!-- Confirmar Contrasena -->
              <div class="col-12 mb-3">
                <label class="form-label" for="confirmar_contrasena">Confirmar contraseña</label>
                <input id="confirmar_contrasena" name="password_confirmation" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como minimo 5 caracteres alfanumericos, es decir, debe contener como minimo letras y numeros.  "/>
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary"><i class="ti ti-donwload ml-3"></i> Guardar </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('usuario.lista', $tipo) }}">
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
                <label for="buscar" class="form-label">Por palabra</label>
                <input id="filtroBuscar" name="buscar" type="text" value="{{$parametrosBusqueda->buscar}}" class="form-control" placeholder="Buscar por nombre, email, identificación">
              </div>

              <!-- Por sexo -->
              <div class="col-12 mb-3">
                <label for="filtroPorSexo" class="form-label">Fitrar por sexo</label>
                <select id="filtroPorSexo" name="filtroPorSexo" class="select2BusquedaAvanzada form-select">
                  <option value="0" {{ $parametrosBusqueda->filtroPorSexo == 0 ? 'selected' : '' }}>Hombres</option>
                  <option value="1" {{ $parametrosBusqueda->filtroPorSexo == 1 ? 'selected' : '' }}>Mujeres</option>
                  <option value="" {{ !is_numeric($parametrosBusqueda->filtroPorSexo) ? 'selected' : '' }}>Todos</option>
                </select>
              </div>

              <!-- Por tipo de usuario -->
              <div class="col-12 mb-3">
                <label for="filtroPorTipoDeUsuario" class="form-label">Fitrar por tipo de usuario </label>
                <select id="filtroPorTipoDeUsuario" name="filtroPorTipoDeUsuario[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($tiposUsuarios as $tipoUsuario)
                  <option value="{{ $tipoUsuario->id }}" {{ $parametrosBusqueda->filtroPorTipoDeUsuario && in_array($tipoUsuario->id,$parametrosBusqueda->filtroPorTipoDeUsuario) ? 'selected' : '' }}>{{ $tipoUsuario->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por edades -->
              <div class="col-12 mb-3">
                <label for="filtroPorRangoEdad" class="form-label">Fitrar por tipo rango de edad</label>
                <select id="filtroPorRangoEdad" name="filtroPorRangoEdad[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($rangosEdad as $rangoEdad)
                  <option value="{{ $rangoEdad->id }}" {{ $parametrosBusqueda->filtroPorRangoEdad && in_array($rangoEdad->id,$parametrosBusqueda->filtroPorRangoEdad) ? 'selected' : '' }}>{{ $rangoEdad->nombre.' ('.$rangoEdad->edad_minima.'-'.$rangoEdad->edad_maxima.')' }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por estados civiles -->
              <div class="col-12 mb-3">
                <label for="filtroPorEstadosCiviles" class="form-label">Fitrar por estados civiles</label>
                <select id="filtroPorEstadosCiviles" name="filtroPorEstadosCiviles[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($estadosCiviles as $estadoCivil)
                  <option value="{{ $estadoCivil->id }}" {{ $parametrosBusqueda->filtroPorEstadosCiviles && in_array($estadoCivil->id,$parametrosBusqueda->filtroPorEstadosCiviles) ? 'selected' : '' }}>{{ $estadoCivil->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por tipo de vinculacion -->
              <div class="col-12 mb-3">
                <label for="filtroPorTiposVinculaciones" class="form-label">Fitrar por tipo de vinculación</label>
                <select id="filtroPorTiposVinculaciones" name="filtroPorTiposVinculaciones[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($tiposVinculaciones as $tipoVinculacion)
                  <option value="{{ $tipoVinculacion->id }}" {{ $parametrosBusqueda->filtroPorTiposVinculaciones && in_array($tipoVinculacion->id,$parametrosBusqueda->filtroPorTiposVinculaciones) ? 'selected' : '' }}>{{ $tipoVinculacion->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por ocupacion -->
              <div class="col-12 mb-3">
                <label for="filtroPorOcupacion" class="form-label">Fitrar por ocupación</label>
                <select id="filtroPorOcupacion" name="filtroPorOcupacion[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($ocupaciones as $ocupacion)
                  <option value="{{ $ocupacion->id }}" {{ $parametrosBusqueda->filtroPorOcupacion && in_array($ocupacion->id,$parametrosBusqueda->filtroPorOcupacion) ? 'selected' : '' }}>{{ $ocupacion->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por profesion -->
              <div class="col-12 mb-3">
                <label for="filtroPorProfesion" class="form-label">Fitrar por profesión</label>
                <select id="filtroPorProfesion" name="filtroPorProfesion[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($profesiones as $profesion)
                  <option value="{{ $profesion->id }}" {{ $parametrosBusqueda->filtroPorProfesion && in_array($profesion->id,$parametrosBusqueda->filtroPorProfesion) ? 'selected' : '' }}>{{ $profesion->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por nivel academico -->
              <div class="col-12 mb-3">
                <label for="filtroPorNivelAcademico" class="form-label">Fitrar por nivel académico</label>
                <select id="filtroPorNivelAcademico" name="filtroPorNivelAcademico[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($nivelesAcademicos as $nivelAcademico)
                  <option value="{{ $nivelAcademico->id }}" {{ $parametrosBusqueda->filtroPorNivelAcademico && in_array($nivelAcademico->id,$parametrosBusqueda->filtroPorNivelAcademico) ? 'selected' : '' }}>{{ $nivelAcademico->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por estado nivel académico -->
              <div class="col-12 mb-3">
                <label for="filtroPorEstadoNivelAcademico" class="form-label">Fitrar estado académico</label>
                <select id="filtroPorEstadoNivelAcademico" name="filtroPorEstadoNivelAcademico" class="select2BusquedaAvanzada form-select">
                  @foreach($estadosNivelAcademico as $estadoNivelAcademico)
                  <option value="{{ $estadoNivelAcademico->id }}" {{ $estadoNivelAcademico->id == $parametrosBusqueda->filtroPorEstadoNivelAcademico ? 'selected' : '' }}>{{ $estadoNivelAcademico->nombre }}</option>
                  @endforeach
                  <option value="" {{ !is_numeric($parametrosBusqueda->filtroPorEstadoNivelAcademico) ? 'selected' : '' }}>Todos</option>
                </select>
              </div>

              <div class="divider text-start my-2">
                <div class="divider-text fw-bold">PASOS DE CRECIMIENTO</div>
              </div>

              <!-- Por paso crecimiento 1 -->
              <div class="col-12 mb-3">
                <label for="filtroPorPasosCrecimiento1" class="form-label">Pasos de crecimiento</label>
                <select id="filtroPorPasosCrecimiento1" name="filtroPorPasosCrecimiento1[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($pasosCrecimiento as $pasoCrecimiento)
                  <option value="{{ $pasoCrecimiento->id }}" {{ $parametrosBusqueda->filtroPorPasosCrecimiento1 && in_array($pasoCrecimiento->id,$parametrosBusqueda->filtroPorPasosCrecimiento1) ? 'selected' : '' }}>{{ $pasoCrecimiento->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por estado paso 1-->
              <div class="col-12 mb-3">
                <label for="filtroEstadoPasos1" class="form-label">Estado</label>
                <select id="filtroEstadoPasos1" name="filtroEstadoPasos1" class="select2BusquedaAvanzada form-select">
                  @foreach($estadosPasosDeCrecimiento as $estadoPaso)
                  <option value="{{ $estadoPaso->id }}" {{ ($parametrosBusqueda->filtroEstadoPasos1 == $estadoPaso->id || $estadoPaso->default ) ? 'selected' : '' }}>{{ $estadoPaso->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- fecha paso 1 -->
              <div class="col-12 mb-3">
                <label for="filtroFechasPasosCrecimiento1" class="form-label">Rango de fechas</label>
                <input id="filtroFechasPasosCrecimiento1" name="filtroFechasPasosCrecimiento1" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
                <input type="text" id="filtroFechaIniPaso1" name="filtroFechaIniPaso1" value="{{ $parametrosBusqueda->filtroFechaIniPaso1 }}" class="form-control d-none" placeholder="">
                <input type="text" id="filtroFechaFinPaso1" name="filtroFechaFinPaso1" value="{{ $parametrosBusqueda->filtroFechaFinPaso1 }}" class="form-control d-none" placeholder="">
              </div>

              <!-- Por paso crecimiento 1 -->
              <div class="col-12 mb-3">
                <label for="filtroPorPasosCrecimiento2" class="form-label">Pasos de crecimiento</label>
                <select id="filtroPorPasosCrecimiento2" name="filtroPorPasosCrecimiento2[]" class="select2BusquedaAvanzada form-select" multiple>
                  @foreach($pasosCrecimiento as $pasoCrecimiento)
                  <option value="{{ $pasoCrecimiento->id }}" {{ $parametrosBusqueda->filtroPorPasosCrecimiento2 && in_array($pasoCrecimiento->id,$parametrosBusqueda->filtroPorPasosCrecimiento2) ? 'selected' : '' }}>{{ $pasoCrecimiento->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Por estado paso 1-->
              <div class="col-12 mb-3">
                <label for="filtroEstadoPasos2" class="form-label">Estado</label>
                <select id="filtroEstadoPasos2" name="filtroEstadoPasos2" class="select2BusquedaAvanzada form-select">
                  @foreach($estadosPasosDeCrecimiento as $estadoPaso)
                  <option value="{{ $estadoPaso->id }}" {{ ($parametrosBusqueda->filtroEstadoPasos2 == $estadoPaso->id || $estadoPaso->default ) ? 'selected' : '' }}>{{ $estadoPaso->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- fecha paso 1 -->
              <div class="col-12 mb-3">
                <label for="filtroFechasPasosCrecimiento2" class="form-label">Rango de fechas</label>
                <input id="filtroFechasPasosCrecimiento2" name="filtroFechasPasosCrecimiento2" type="text" class="form-control" placeholder="YYYY-MM-DD a YYYY-MM-DD" />
                <input type="text" id="filtroFechaIniPaso2" name="filtroFechaIniPaso2" value="{{ $parametrosBusqueda->filtroFechaIniPaso2 }}" class="form-control d-none" placeholder="">
                <input type="text" id="filtroFechaFinPaso2" name="filtroFechaFinPaso2" value="{{ $parametrosBusqueda->filtroFechaFinPaso2 }}" class="form-control d-none" placeholder="">
              </div>

              <div class="divider text-start my-2">
                <div class="divider-text fw-bold">GRUPOS Y REUNIONES</div>
              </div>

              @livewire('Grupos.grupos-para-busqueda',[
              'id' => 'filtroGrupo',
              'class' => 'col-12 mb-3',
              'label' => 'Filtrar a partir del grupo',
              'conDadosDeBaja' => 'no',
              'grupoSeleccionadoId' => $parametrosBusqueda->filtroGrupo,
              'estiloSeleccion' => 'pequeno'
              ])

              <!-- Por tipo ministerio -->
              <div class="col-12 mb-3">
                <label for="filtroTipoMinisterio" class="form-label">Fitrar por tipo ministerio</label>
                <select id="filtroTipoMinisterio" name="filtroTipoMinisterio" class="select2BusquedaAvanzada form-select">
                  <option value="0" {{ !$parametrosBusqueda->filtroTipoMinisterio || $parametrosBusqueda->filtroTipoMinisterio == 0 ? 'selected' : '' }}>Ministerio completo</option>
                  <option value="1" {{ $parametrosBusqueda->filtroTipoMinisterio == 1 ? 'selected' : '' }}>Ministerio directo</option>
                </select>
              </div>

              <div class="divider text-start my-2">
                <div class="divider-text fw-bold">INACTIVIDAD</div>
              </div>

              <div class="col-12 mb-3">
                <label for="filtroCantidadDiasInactividadGrupos" class="form-label">Días inactividad grupos</label>
                <input type="number" id="filtroCantidadDiasInactividadGrupos" name="filtroCantidadDiasInactividadGrupos" value="{{ old('filtroCantidadDiasInactividadGrupos', $parametrosBusqueda->filtroCantidadDiasInactividadGrupos) }}" class="form-control">
              </div>

              <div class="col-12 mb-3">
                <label for="filtroCantidadDiasInactividadReuniones" class="form-label">Días inactividad reunión</label>
                <input type="number" id="filtroCantidadDiasInactividadReuniones" name="filtroCantidadDiasInactividadReuniones" value="{{ old('filtroCantidadDiasInactividadReuniones', $parametrosBusqueda->filtroCantidadDiasInactividadReuniones) }}" class="form-control">
              </div>

            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  <!-- offcanvas generador de excel  -->
  <form class="forms-sample" method="POST" action="{{ route('usuario.listadoFinalCsv') }}">
    @csrf
    <div class="offcanvas offcanvas-end event-sidebar modalSelect2"  tabindex="-1" id="modalGeneradorExcel" aria-labelledby="modalGeneradorExcelLabel">
        <div class="offcanvas-header my-1 px-8">
            <h4 class="offcanvas-title fw-bold text-primary" id="modalGeneradorExcelLabel">
              Exportar a excel
            </h4>

            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-6 px-8">
           <div class="mb-4">
              <span class="text-black ti-14px mb-4">Selecciona los campos que deseas exportar en el archivo excel.</span>
            </div>

             <div class="row">
              <textarea id="parametros-busqueda-excel" name="parametrosBusqueda" class="d-none">{{json_encode($parametrosBusqueda)}}</textarea>

              <!-- Informacion personal -->
              <div class="col-12 mb-3">
                <label for="informacionPersonal" class="form-label">Información personal <br>
                  (<a href="javascript:;" data-select="informacionPersonal" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionPersonal" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                </label>
                <select id="informacionPersonal" name="informacionPersonal[]" class="select2GeneradorExcel form-select" multiple>
                  @foreach($camposInformeExcel->where('selector_id',1) as $campo)
                  <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Informacion ministerial -->
              <div class="col-12 mb-3">
                <label for="informacionMinisterial" class="form-label">Información ministerial <br>
                  (<a href="javascript:;" data-select="informacionMinisterial" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionMinisterial" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                </label>
                <select id="informacionMinisterial" name="informacionMinisterial[]" class="select2GeneradorExcel form-select" multiple>
                  @foreach($pasosCrecimiento as $pasoCrecimiento)
                  <option value="{{ $pasoCrecimiento->id }}">{{ $pasoCrecimiento->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Informacion congregacional -->
              <div class="col-12 mb-3">
                <label for="informacionCongregacional" class="form-label">Información congregacional <br>
                  (<a href="javascript:;" data-select="informacionCongregacional" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCongregacional" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                </label>
                <select id="informacionCongregacional" name="informacionCongregacional[]" class="select2GeneradorExcel form-select" multiple>
                  @foreach($camposInformeExcel->where('selector_id',2) as $campo)
                  <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                  @endforeach
                </select>
              </div>

              @if($configuracion->visible_seccion_campos_extra)
              <!-- Informacion congregacional -->
              <div class="col-12 mb-3">
                <label for="informacionCamposExtras" class="form-label">Información {{$configuracion->label_seccion_campos_extra}} <br>
                  (<a href="javascript:;" data-select="informacionCamposExtras" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCamposExtras" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                </label>
                <select id="informacionCamposExtras" name="informacionCamposExtras[]" class="select2GeneradorExcel form-select" multiple>
                  @foreach($camposExtras as $campo)
                  <option value="{{ $campo->id }}">{{ $campo->nombre }}</option>
                  @endforeach
                </select>
              </div>
              @endif

            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Exportar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  @livewire('Usuarios.modal-baja-alta')

@endsection
