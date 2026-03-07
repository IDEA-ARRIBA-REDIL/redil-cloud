@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Usuarios')

<!-- Page Styles -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

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
  document.addEventListener('DOMContentLoaded', function() {
    // Seleccionamos todos los elementos que se pueden colapsar
    const collapseElements = document.querySelectorAll('.collapse');

    collapseElements.forEach(function(collapseEl) {
      // Escuchamos el evento que Bootstrap dispara ANTES de empezar a MOSTRAR el contenido
      collapseEl.addEventListener('show.bs.collapse', function() {
        // Buscamos el botón que controla este div en específico
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          // Cambiamos el ícono a 'menos'
          icon.classList.remove('ti-plus');
          icon.classList.add('ti-minus');
        }
      });

      // Escuchamos el evento que Bootstrap dispara ANTES de empezar a OCULTAR el contenido
      collapseEl.addEventListener('hide.bs.collapse', function() {
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          // Cambiamos el ícono a 'más'
          icon.classList.remove('ti-minus');
          icon.classList.add('ti-plus');
        }
      });
    });
  });
</script>

<script>
  window.addEventListener('refrescar-lista', event => {
    // Opcional: un pequeño delay para que el usuario alcance a ver el mensaje de éxito.
    setTimeout(() => {
      location.reload();
    }, 1000); // 1 segundo de espera
  });

  document.addEventListener('DOMContentLoaded', function() {
    // --- PARTE 1: LÓGICA DESPUÉS DE RECARGAR ---

    // Buscamos si hay un ID de persona guardado para enfocar
    const idParaEnfocar = sessionStorage.getItem('enfocarPersonaId');

    if (idParaEnfocar) {
      // Buscamos la tarjeta y el botón de colapso correspondientes
      const tarjetaParaEnfocar = document.getElementById(`persona-card-${idParaEnfocar}`);
      const botonColapso = document.querySelector(`[data-bs-target="#cardBodyPersona${idParaEnfocar}"]`);

      if (tarjetaParaEnfocar && botonColapso) {
        // Hacemos scroll suave hasta la tarjeta
        tarjetaParaEnfocar.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });

        // Hacemos clic programáticamente en el botón para desplegar el contenido
        botonColapso.click();
      }

      // Limpiamos el ID del storage para que no se repita en la siguiente recarga
      sessionStorage.removeItem('enfocarPersonaId');
    }
  });

  // Escuchamos el nuevo evento 'refrescar-y-enfocar'
  window.addEventListener('refrescar-y-enfocar', event => {
    // Guardamos el ID de la persona en el sessionStorage del navegador
    sessionStorage.setItem('enfocarPersonaId', event.detail.personaId);

    // Opcional: un pequeño delay para que se vea el mensaje de éxito
    setTimeout(() => {
      location.reload();
    }, 1000);
  });
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
@endsection

@section('content')
<h4 class="mb-1 fw-semibold text-primary">Consolidación</h4>

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
            <a href="{{ route('consolidacion.lista', $indicador->url) }}">
              <div class="card border rounded-3 shadow-sm" style="border-bottom: 10px solid {{ $indicador->color}} !important; ">
                <div class="card-body d-flex flex-row p-3">

                  <div class="card-icon me-1">
                  </div>

                  <div class="card-title mb-0">
                    <p class="text-black mb-0" style="font-size: .8125rem">{{ $indicador->nombre }}</p>
                    <h5 class="mb-0 me-2">{{ $indicador->cantidad }} </h5>
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

  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('consolidacion.lista', $tipo) }}">
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
            <a type="button" href="{{ route('consolidacion.lista', $tipo) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
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
      <div class="col equal-height-col col-12 col-md-6" id="persona-card-{{ $persona->id }}">
        <div class="card rounded-3 shadow">
          <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
            <div class="flex-fill row">
              <div class=" d-flex justify-content-between align-items-center">

                <div class="d-flex flex-column">
                  <h5 class="fw-semibold ms-1 text-black m-0">
                    {{ $persona->nombre(3) }}
                  </h5>             
                  <small class="text-black"><i>Ultima actualización:</i> {{ $persona->ultimaActividadTarea()->pivot->updated_at ?? 'Sin actividad' }} </small>
                </div>

                <span class="badge rounded-pill fw-light me-1" style="background-color: {{ $persona->tipoUsuario->color }}">
                  <i class="{{ $persona->tipoUsuario->icono }} fs-6"></i> <span class="text-white"> {{ $persona->tipoUsuario->nombre }}</span>
                </span>
              </div>
            </div>

            <div class="">
              <div class="ms-auto">
                <div class="dropdown zindex-2 p-1 float-end">
                  <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                  
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);"
                        x-data
                        @click="$dispatch('crearTarea', { personaId: {{ $persona->id }} })">
                        Agregar tarea
                      </a>
                    </li>
                    @if($rolActivo->hasPermissionTo('consejeria.opcion_agendar_cita'))
                    <li><a class="dropdown-item" href="{{ route('consejeria.nuevaCita', $persona) }}">Agendar cita</a></li>         
                    @endif      

                    @can('verPerfilUsuarioPolitica', [$persona, 'principal'])
                    <li><a class="dropdown-item" href="{{ route('usuario.perfil', $persona) }}">Ver perfil</a></li>
                    @endcan

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

          </div>

          <div class="card-body">
            <div class="row mt-4">
              <div class="col-6 d-flex flex-column">
                <small class="text-black">¿Por donde ingreso?</small>
                <small class="fw-semibold text-black ">{{ $persona->tipoVinculacion->nombre ?? 'No especificado'}}</small>
              </div>

              <div class="col-6 d-flex flex-column">
                <small class="text-black">Estado civil</small>
                <small class="fw-semibold text-black ">{{ $persona->estadoCivil->nombre ?? 'No especificado'}}</small>
              </div>

              <div class="col-12">
                <hr class="my-3 border-1">
              </div>

              <div class="col-6 d-flex flex-column mt-1">
                <small class="text-black">Correo</small>
                <small class="fw-semibold text-black ">{{ $persona->email ?? 'No especificado'}}</small>
              </div>

              @php
              $telefonos = collect([
              $persona->telefono_fijo,
              $persona->telefono_movil,
              $persona->telefono_otro,
              ])->filter();

              $textoTelefonos = $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'No indicados';
              @endphp

              <div class="col-6 d-flex flex-column mt-1">
                <small class="text-black">Teléfono</small>
                <small class="fw-semibold text-black ">{{ $telefonos->isNotEmpty() ? $textoTelefonos : 'No especificado'}}</small>
              </div>

              <div class="col-6 d-flex flex-column mt-1">
                <small class="text-black">{{ $persona->tipoIdentificacion ? $persona->tipoIdentificacion->abreviatura : 'ID'}}</small>
                <small class="fw-semibold text-black ">{{ $persona->identificacion ? $persona->identificacion : 'No indicado'}}</small>
              </div>

              <div class="col-6 d-flex flex-column mt-1">
                <small class="text-black">Edad</small>
                <small class="fw-semibold text-black ">{{ $persona->edad() > 1 ?  $persona->edad().' años' : $persona->edad().' año'}}</small>
              </div>

            </div>

            <div class="collapse" id="cardBodyPersona{{ $persona->id }}">
              <div class="col-12">
                <hr class="my-3 border-1">
              </div>
              <h6 class="fw-bold text-black px-4">Tareas</h6>
              @php
              $tareasAsignadas = $persona->tareasConsolidacion;
              $tareasAsignadasIds = $tareasAsignadas->pluck('id');
              $tareasDefaultFaltantes = $tareasDefault->whereNotIn('id', $tareasAsignadasIds);
              $listaCompletaDeTareas = $tareasAsignadas->merge($tareasDefaultFaltantes)->sortBy('orden');
              @endphp

              @forelse ($listaCompletaDeTareas as $tarea)
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  @if (isset($tarea->pivot))
                  @php
                  $tareaAsignada = $tarea;
                  $estadoActual = $tareaAsignada->pivot->estado;
                  @endphp
                  <div class="d-flex flex-column">
                    <small class="text-black">{{ $tarea->nombre }}</small>
                    
                    <div class="btn-group">
                      <button type="button" class="btn btn-{{ $estadoActual->color ?? 'secondary' }} rounded-pill btn-xs dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ $estadoActual->nombre ?? 'N/A' }} {{ $tareaAsignada->pivot->fecha }}
                      </button>
                      <ul class="dropdown-menu">
                        @foreach ($estados as $estadoOpcion)
                        @if ($estadoOpcion->id !== $estadoActual->id)
                        <li>
                          <a class="dropdown-item" href="javascript:void(0);"
                            x-data
                            @click="$dispatch('actualizarEstado', {
                              tareaAsignadaId: {{ $tareaAsignada->pivot->id }},
                              nuevoEstadoId: {{ $estadoOpcion->id }}
                            })">
                            {{ $estadoOpcion->nombre }}
                          </a>
                        </li>
                        @endif
                        @endforeach
                      </ul>
                    </div>
                  </div>
                  @else
                  <div class="d-flex flex-column">
                    <small class="text-black">{{ $tarea->nombre }}</small>
                    <div class="btn-group ">
                      <button type="button" class="btn btn-secondary rounded-pill btn-xs dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        Sin asignar
                      </button>
                      <ul class="dropdown-menu">
                        @foreach ($estados as $estadoOpcion)
                        <li>
                          <a class="dropdown-item" href="javascript:void(0);"
                            x-data
                            @click="$dispatch('crearTareaDefault', {
                              personaId: {{ $persona->id }},
                              tareaId: {{ $tarea->id }},
                              nuevoEstadoId: {{ $estadoOpcion->id }}
                            })">
                            Asignar como: {{ $estadoOpcion->nombre }}
                          </a>
                        </li>
                        @endforeach
                      </ul>
                    </div>

                  </div>
                  @endif
                </div>

                <div class="d-flex align-items-center justify-content-star">
                  @if (isset($tarea->pivot))
                  <a href="javascript:void(0);"
                    class="btn rounded-pill btn-icon btn-outline-secondary waves-effect border-0"
                    x-data
                    @click="$dispatch('editarTarea', { tareaAsignadaId: {{ $tarea->pivot->id }} })">
                    <i class="ti ti-pencil "></i>
                  </a>

                  <a href="javascript:void(0);" class="btn rounded-pill btn-icon btn-outline-secondary waves-effect border-0" x-data @click="$dispatch('ver-historial-tarea', { tareaAsignadaId: {{ $tarea->pivot->id }} })">
                    <i class="ti ti-history"></i>
                  </a>

                  @if (!$tarea->default)
                  <a href="javascript:void(0);"
                    class="btn rounded-pill btn-icon btn-outline-danger waves-effect border-0"
                    x-data
                    @click="$dispatch('confirmarEliminacionTarea', { id: {{ $tarea->pivot->id }} })">
                    <i class="ti ti-trash text-danger"></i>
                  </a>
                  @else
                  <a href="javascript:void(0);"
                    class="btn rounded-pill btn-icon btn-outline-danger waves-effect disabled border-0"
                    data-bs-toggle="tooltip"
                    title="Las tareas por defecto no se pueden eliminar">
                    <i class="ti ti-trash"></i>
                  </a>
                  @endif


                  @else
                  <a href="javascript:void(0);" class="btn rounded-pill btn-icon btn-outline-secondary waves-effect border-0 disabled"><i class="ti ti-pencil"></i></a>
                  <a href="javascript:void(0);" class="btn rounded-pill btn-icon btn-outline-secondary waves-effect border-0 disabled"><i class="ti ti-history"></i></a>
                  <a href="javascript:void(0);" class="btn rounded-pill btn-icon btn-outline-danger waves-effect border-0 disabled"><i class="ti ti-trash"></i></a>
                  @endif
                </div>
              </div>
              @if (!$loop->last)
              <hr class="my-2"> @endif
              @empty
              <p class="text-black">No hay tareas por defecto configuradas.</p>
              @endforelse
            </div>
          </div>

          <div class="card-footer border-top p-1">
            <div class="d-flex justify-content-center">
              <button type="button"
                class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                data-bs-toggle="collapse"
                data-bs-target="#cardBodyPersona{{ $persona->id }}">
                <span class="ti ti-plus"></span>
              </button>
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

    <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('consolidacion.lista', $tipo) }}">
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

            </div>
        </div>
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
            <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
        </div>
    </div>
  </form>

  @livewire('Consolidacion.gestionar-tareas')
   @livewire('Usuarios.modal-baja-alta')


@endsection
