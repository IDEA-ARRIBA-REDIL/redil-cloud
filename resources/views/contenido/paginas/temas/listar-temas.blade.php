@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Temas')

<!-- Page -->
@section('vendor-style')

@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
])

@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection


@section('page-script')
<script>
    $(document).ready(function() {
      $('.select2').select2({
        dropdownParent: $('#modalBusquedaAvanzada')
      });
    });

    // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
    $('#modalBusquedaAvanzada').on('scroll', function(event) {
      $(this).find(".select2").each(function() {
        $(this).select2({
          dropdownParent: $(this).parent()
        });
      });
    });


    $(".clearAllItems").click(function() {
        value = $(this).data('select');
        $('#' + value).val(null).trigger('change');
      });

      $(".selectAllItems").click(function() {
        value = $(this).data('select');
        $("#" + value + " > option").prop("selected", true);
        $("#" + value).trigger("change");
      });

      ///confirmación para eliminar tema
      $('.confirmacionEliminar').on('click', function () {
    let nombre = $(this).data('nombre');
    let id = $(this).data('id');

    Swal.fire({
      title: "¿Estás seguro que deseas eliminar el tema <b>"+nombre+"</b>?",
      html: "Esta acción no es reversible.",
      icon: "warning",
      showCancelButton: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#eliminarTema').attr('action',"/tema/"+id+"/eliminar");
        $('#eliminarTema').submit();
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

<script>
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {
      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;

      const form = document.getElementById('busquedaAvanzada');
      const input = form.querySelector('[id="' + field + '"]');

      if (input && $(input).hasClass('select2')) {
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
  <h4 class=" mb-1 fw-semibold text-primary">Temas</h4>

  @include('layouts.status-msn')




  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('tema.lista') }}">
    <div class="row mt-5">
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" class="form-control" placeholder="Busqueda..." aria-describedby="btnBusqueda">
          @if($buscar)
          <span id="borrarBusquedaPorPalabra" class="input-group-text"><i class="ti ti-x"></i></span>
          @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          @endif
        </div>
      </div>
      <div class="col-3 col-md-8 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada"><i class="ti ti-filter"></i> <span class="d-none d-md-block">Filtros</span></button>
      </div>

      <div class="filter-tags py-3">
        <span class="text-black me-5">{{ $temas->total() > 1 ? $temas->total().' Temas' : $temas->total().' Tema' }}</span>
        @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
          @foreach($tagsBusqueda as $tag)
            <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
              <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
            </button>
          @endforeach
          @if($bandera == 1)
            <a type="button" href="{{ route('tema.lista') }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
              <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
            </a>
          @endif
        @endif
      </div>


    </div>
  </form>


  <!-- Lista de temas -->
  <div class="row g-6 mt-1 mb-5">
  @foreach($temas as $tema)
    <div class="col-md-6 col-lg-4">
      <div class="card">
        <img class="card-img-top" src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/temas/'.$tema->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/temas/default.png')}}" alt="Card imagen {{ $tema->titulo }}" />

        <div class="card-header">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-start">
              <div class="me-2 mt-1">
                <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $tema->titulo }}</h5>
              </div>
            </div>
            <div class="ms-auto">
              <div class="dropdown zindex-2 p-1 float-end">
                <button type="button" class="btn dropdown-toggle hide-arrow btn btn-sm waves-effect text-black border p-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  @if($rolActivo->hasPermissionTo('temas.ver_tema'))
                  <li>
                    <a class="dropdown-item" href="{{route('tema.ver', $tema)}}">
                      <span class="me-2">Ver</span>
                    </a>
                  </li>
                  @endif
                  @if($rolActivo->hasPermissionTo('temas.editar_tema'))
                  <li>
                    <a class="dropdown-item" href="{{route('tema.actualizar', $tema)}}">
                      <span class="me-2">Editar</span>
                    </a>
                  </li>
                  @endif
                  @if($rolActivo->hasPermissionTo('temas.eliminar_tema'))
                  <hr class="dropdown-divider">
                    <li>
                    <a data-id="{{$tema->id}}"  data-nombre="{{$tema->titulo}}" class="dropdown-item text-danger waves-effect confirmacionEliminar" >
                      <span class="me-2">Eliminar</span>
                    </a>
                  </li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex flex-column">
            <h6 class="mb-0 lh-sm"> Categorias: </h6>
            <div class="d-flex my-2">
              @if($tema->categorias->count()> 0)
                @foreach($tema->categorias as $categoria)
                  <span class="badge rounded-pill bg-label-primary fw-light">{{$categoria->nombre}}</span>
                @endforeach
              @else
                <span class="badge rounded-pill bg-label-info fw-light">Sin categoria </span>
              @endif
            </div>
          </div>
        </div>
        @if($rolActivo->hasPermissionTo('temas.ver_tema'))
        <div class="card-footer" style="background-color:#ededed!important">
          <div class="d-flex mt-5 ">
          <a href="{{route('tema.ver', $tema)}}" class="btn rounded-pill w-100 btn-primary waves-effect waves-light px-10 py-1 fw-light">Ver más </a>
          </div>
        </div>
        @endif
      </div>
    </div>
  @endforeach
  </div>
  <!-- Lista de temas -->

  <div class="row my-3 text-black">
    @if($temas)
    <p> {{$temas->lastItem()}} <b>de</b> {{$temas->total()}} <b>temas - Página</b> {{ $temas->currentPage() }} </p>
    {!! $temas->appends(request()->input())->links() !!}
    @endif
  </div>

  <form id="eliminarTema" method="POST" action="">
  @csrf
  </form>

  <!-- offcanvas busqueda avanzada -->
  <form id="busquedaAvanzada" class="forms-sample" method="GET" action="{{ route('tema.lista') }}">
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
              <input id="buscar" name="buscar" type="text" value="{{$buscar}}" class="form-control" placeholder="Buscar por nombre, email, identificación">
            </div>

            <!-- Por categoria -->
            <div class="col-12 mb-3">
              <label for="categorias" class="form-label">Por categoria</label>
              <select id="categorias" name="categorias[]" class="select2 form-select" multiple>
                @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ $categoriasSeleccionadas && in_array($categoria->id,$categoriasSeleccionadas) ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                @endforeach
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

@endsection
