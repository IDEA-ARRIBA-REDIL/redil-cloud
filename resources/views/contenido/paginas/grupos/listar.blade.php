@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Grupos')

<!-- Page -->
@section('page-style')
@vite([

'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
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


  $(document).ready(function() {
    $('.select2Reporte').select2({
      dropdownParent: $('#modalNuevoReporte')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalGeneradorExcel').on('scroll', function(event) {
    $(this).find(".select2Reporte").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });

</script>

<script type="module">
  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d"
  });
</script>

<script>

  $(".clearAllItems").click(function() {
    value = $(this).data('select');
    $('#' + value).val(null).trigger('change');
  });

  $(".selectAllItems").click(function() {
    value = $(this).data('select');
    $("#" + value + " > option").prop("selected", true);
    $("#" + value).trigger("change");
  });

  function darBajaAlta(grupoId, tipo)
  {
    Livewire.dispatch('abrirModalBajaAlta', { grupoId: grupoId, tipo: tipo });
  }

  function eliminacion(grupoId)
  {
    Livewire.dispatch('confirmarEliminacion', { grupoId: grupoId });
  }
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
  $(".btnModalNuevoReporte").click(function() {
    fechaAutomatica = $(this).data('fecha-automatica');
    grupoId = $(this).data('id');
    Livewire.dispatch('abrirModalNuevoReporte', { fechaAutomatica: fechaAutomatica, grupoId: grupoId });
  });
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron =/[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Grupos</h4>



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
                <a href="{{ route('grupo.lista', $indicador->url) }}">
                  <div class="h-100 card border rounded-3 shadow-sm">
                    <div class="card-body d-flex flex-row p-3">

                      <div class="card-icon me-1 ">
                      <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'. $indicador->imagen) : Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/'. $indicador->imagen) }}" alt="icono" class="me-2" width="50">
                      </div>

                      <div class="card-title mb-0 lh-sm">
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

  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('grupo.lista', $tipo) }}">
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
        <span class="text-black me-5">{{ $grupos->total() > 1 ? $grupos->total().' Grupos' : $grupos->total().' Grupo' }}</span>
        @if(isset($parametrosBusqueda->tagsBusqueda) && is_array($parametrosBusqueda->tagsBusqueda))
          @foreach($parametrosBusqueda->tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($parametrosBusqueda->bandera == 1)
            <a type="button" href="{{ route('grupo.lista', $tipo) }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>

    </div>
  </form>
  <!-- lista de grupos -->
  <div class="row g-4 mt-1">
    @foreach($grupos as $grupo)
    <div class="col-12 col-xl-4 col-md-6">

      <div class="card ">
        <img class="card-img-top object-fit-cover" style="height: 130px;"  src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/'.$grupo->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/grupos/default.png')}}" alt="Card imagen {{ $grupo->nombre }}" />
        <div class="card-header pb-2">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-start">
              <div class="me-2 mt-1">
                <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $grupo->tipoGrupo ? $grupo->tipoGrupo->nombre : 'No definido'}}</h5>
                <div class="client-info fw-semibold text-black">{{ $grupo->nombre }}</div>
              </div>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  @if($grupo->dado_baja == 0)
                    @if($rolActivo->hasPermissionTo('grupos.opcion_ver_perfil_grupo'))
                      <li><a class="dropdown-item" href="{{ route('grupo.perfil.estadisticasGrupo', $grupo)}}">Perfil</a></li>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_modificar_grupo'))
                      <li><a class="dropdown-item" href="{{ route('grupo.modificar', $grupo)}}">Modificar</a></li>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_anadir_lideres_grupo'))
                      <li><a class="dropdown-item" href="{{ route('grupo.gestionarEncargados', $grupo)}}">Gestionar encargados</a></li>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_anadir_integrantes_grupo'))
                      <li><a class="dropdown-item" href="{{ route('grupo.gestionarIntegrantes', $grupo)}}">Gestionar integrantes</a></li>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_georreferencia_grupo'))
                      <li><a class="dropdown-item" href="{{ route('grupo.georreferencia', $grupo)}}">Gestionar Georeferencia</a></li>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_excluir_grupo'))
                      <form id="excluirGrupo" method="POST" action="{{ route('grupo.excluir', ['grupo' => $grupo]) }}">
                        @csrf
                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('excluirGrupo').submit();" >Excluir grupo</a></li>
                      </form>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_ver_informes_evidencia'))
                    <li><a class="dropdown-item" href="{{ route('grupo.informeEvidencia.listar', $grupo)}}">Ver informes de evidencia</a></li>
                    @endif
                    
                    <hr class="dropdown-divider">
                    
                    @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                      <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'baja')">Dar de baja</a></li>
                    @endif

                    @if($rolActivo->hasPermissionTo('grupos.opcion_eliminar_grupo'))
                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="eliminacion('{{$grupo->id}}')">Eliminar</a></li>
                    @endif
                  @else
                    @if($rolActivo->hasPermissionTo('grupos.opcion_dar_de_baja_alta_grupo'))
                      <li><a class="dropdown-item" href="javascript:void(0);" onclick="darBajaAlta('{{$grupo->id}}', 'alta')">Dar de alta</a></li>
                    @endif
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">

          <div class="d-flex my-2 mb-5">
            @if( isset($grupo->ultimoReporteDelGrupo()->id) )
              <span class="badge rounded-pill bg-label-primary me-1"> <b>Último reporte:</b> {{ $grupo->ultimoReporteDelGrupo()->fecha }}</span>
              @if($grupo->alDia())
              <span class="badge rounded-pill bg-label-success">Al día </span>
              @endif
            @else
            <span class="badge rounded-pill bg-label-danger"> Nunca reportado</span>
            @endif
          </div>

          <div class="d-flex flex-row justify-content-between mb-2">
            <div class="d-flex flex-row">
              <i class="ti ti-brand-days-counter text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Día de reunión:</small>
                <small class="fw-semibold ms-1 text-black ">{{ $grupo->dia ? Helper::obtenerDiaDeLaSemana($grupo->dia, 'corto') : 'Día no indicado' }}, {{ Carbon\Carbon::parse($grupo->hora)->format(('g:i a')) }}</small>
              </div>
            </div>

            <div class="d-flex flex-row">
              <i class="ti ti-users-group text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">Integrantes</small>
                <small class="fw-semibold ms-1 text-black ">{{ $grupo->asistentes()->select('users.id')->count() }}</small>
              </div>
            </div>
          </div>

          <div class="d-flex flex-row justify-content-between mb-4">
            <div class="d-flex flex-row">
              <i class="ti ti-confetti text-black"></i>
              <div class="d-flex flex-column">
                <small class="text-black ms-1">{{ $configuracion->label_fecha_creacion_grupo ? $configuracion->label_fecha_creacion_grupo : 'Fecha de apertura'}}:</small>
                <small class="fw-semibold ms-1 text-black">{{ $grupo->fecha_apertura ? $grupo->fecha_apertura : 'No indicado' }}</small>
              </div>
            </div>
          </div>

          <div class="d-flex flex-column mb-3">
            <span class="fw-bold text-black">Encargados</span>
            @if($grupo->encargadosDirectos()->count() > 0)
              @foreach($grupo->encargadosDirectos() as $encargado)
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

        <div class="card-footer" style="background-color:#ededed!important">
          <div class="d-flex mt-3 ">

            <a href="{{ $grupo->dado_baja == 0 && $rolActivo->hasPermissionTo('grupos.opcion_ver_perfil_grupo') ? route('grupo.perfil.estadisticasGrupo', $grupo) : 'javascript:;' }}" class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light" {{ $grupo->dado_baja == 0 && $rolActivo->hasPermissionTo('grupos.opcion_ver_perfil_grupo') ? '' : 'disabled' }} >Ver más </a>
           
            @if($grupo->varificarProcesoReporte() == 'botonCrearDeshabilitado' || $grupo->varificarProcesoReporte() == 'botonEditarDeshabilitado')
              @if($grupo->varificarProcesoReporte() == 'botonCrearDeshabilitado')
              <button disabled class=" btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light" >Crear reporte </button>
              @elseif ($grupo->varificarProcesoReporte() == 'botonEditarDeshabilitado')
              <button disabled class=" btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">Editar reporte </button>
              @endif
            @else
              @if($grupo->varificarProcesoReporte() == 'botonCrearReporte' &&  $rolActivo->hasPermissionTo('reportes_grupos.subitem_nuevo_reporte_grupo'))
              <button data-id="{{ $grupo->id }}" data-fecha-automatica="{{ $grupo->verificaFechaAutomaticaReporte() }}" class="btnModalNuevoReporte btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light" >Crear reporte </button>
              @elseif ($grupo->varificarProcesoReporte() == 'botonEditarReporte' && $rolActivo->hasPermissionTo('reportes_grupos.opcion_actualizar_reporte_grupo'))
              <a href="{{ route('reporteGrupo.asistencia', $grupo->ultimoReporteDelGrupo()->id ) }}" class="btn btn-sm rounded-pill w-100 btn-primary waves-effect waves-light mx-1 py-1 fw-light">Editar reporte </a>
              @endif
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <!--/ lista de grupos -->

  <div class="row my-3">
    @if($grupos)
    <p> {{$grupos->lastItem()}} <b>de</b> {{$grupos->total()}} <b>grupos - Página</b> {{ $grupos->currentPage() }} </p>
    {!! $grupos->appends(request()->input())->links() !!}
    @endif
  </div>

  @livewire('Grupos.modal-baja-alta-grupo')

  <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('grupo.lista', $tipo) }}">
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

  <!-- offcanvas generador de excel  -->
  <form class="forms-sample" method="POST" action="{{ route('grupo.listadoFinalCsv') }}">
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

              <!-- Informacion principal -->
              <div class="col-12 mb-3">
                <label for="informacionPrincipal" class="form-label">Información principal <br>
                  (<a href="javascript:;" data-select="informacionPrincipal" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionPrincipal" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
                </label>
                <select id="informacionPrincipal" name="informacionPrincipal[]" class="select2GeneradorExcel form-select" multiple>
                  @foreach($camposInformeExcel->where('selector_id',5) as $campo)
                    @if($campo->nombre_campo_informe == "1")
                    <option value="{{$campo->id}}">{{$configuracion->label_campo_opcional1}}</option>
                    @elseif($campo->nombre_campo_bd == "dia_planeacion")
                    <option value="{{$campo->id}}">{{$configuracion->label_campo_dia_planeacion_grupo}}</option>
                    @elseif($campo->nombre_campo_bd == "hora_planeacion")
                    <option value="{{$campo->id}}">{{$configuracion->label_campo_hora_planeacion_grupo}}</option>
                    @elseif($campo->nombre_campo_bd == "dia")
                    <option value="{{$campo->id}}">{{$configuracion->label_campo_dia_reunion_grupo}}</option>
                    @elseif($campo->nombre_campo_bd == "hora")
                    <option value="{{$campo->id}}">{{$configuracion->label_campo_hora_reunion_grupo}}</option>
                    @else
                    <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                    @endif
                  @endforeach
                </select>
              </div>

              @if($configuracion->visible_seccion_campos_extra_grupo)
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

  @livewire('ReporteGrupos.modal-nuevo-reporte')

@endsection
