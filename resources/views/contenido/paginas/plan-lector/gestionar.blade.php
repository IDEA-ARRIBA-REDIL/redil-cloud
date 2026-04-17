@extends('layouts/layoutMaster')

@section('title', 'Gestionar Planes Lectores')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script>
  $(function () {
    // Select2 Initialization
    $('.select2').select2({
      dropdownParent: $('#modalBusquedaAvanzada')
    });

    // Fix select2 scroll inside modal problem
    $('#modalBusquedaAvanzada').on('scroll', function(event) {
      $(this).find(".select2").each(function() {
        $(this).select2({
          dropdownParent: $(this).parent()
        });
      });
    });

    // SweetAlert2 para eliminar
    $('.delete-record').on('click', function (e) {
      e.preventDefault();
      var id = $(this).data('id');
      var form = $('#form-eliminar-' + id);
      Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          form.submit();
        }
      });
    });

    // Búsqueda inteligente (Estilo sedes.listar)
    const $buscarInput = $('#buscar_plan');
    const $btnBorrar = $('#borrarBusquedaPorPalabra');
    const $form = $('#filter-form');
    let timeoutId;
    const delay = 1000;

    $buscarInput.on('input', function() {
        clearTimeout(timeoutId);

        if (this.value.length >= 3) {
          timeoutId = setTimeout(() => {
              $form.submit();
          }, delay);
        } else if(this.value.length == 0) {
          $form.submit();
        }
    });

    $btnBorrar.on('click', function() {
      $buscarInput.val("");
      $form.submit();
    });

    // Tag remover logic
    $('.remove-tag').on('click', function() {
      const field = $(this).data('field');
      const fieldAux = $(this).data('field2');
      const value = $(this).data('value');

      const input = $form.find('[id="' + field + '"]');

      if (input.length && input.hasClass('select2')) {
        let currentValues = input.val();
        if (Array.isArray(currentValues)) {
            const newValue = currentValues.filter(v => v != value);
            input.val(newValue).trigger('change');
        } else {
            input.val(null).trigger('change');
        }
      } else if (input.length && input.prop('tagName') === 'SELECT' && input.prop('multiple')) {
        let selectDOM = input[0];
        let currentValues = Array.from(selectDOM.selectedOptions).map(option => option.value);
        const newValue = currentValues.filter(v => v != value);
        for (let i = 0; i < selectDOM.options.length; i++) {
            selectDOM.options[i].selected = newValue.includes(selectDOM.options[i].value);
        }
        input.trigger('change');
      } else if (input.length && input.prop('tagName') === 'SELECT') {
        input.val('');
      } else if (input.length) {
        input.val('');
        if(fieldAux) {
          $form.find('[id="' + fieldAux + '"]').val('');
        }
      }
      $form.submit();
    });
  });
</script>
@endsection

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
  <h4 class="mb-0 fw-semibold text-primary">Planes lectores</h4>
  @if($rolActivo->hasPermissionTo('planes_lectores.subitem_nuevo_plan_lector'))
    <a href="{{ route('planes-lectores.crear') }}" class="btn btn-primary rounded-pill">
      <i class="ti ti-plus me-1"></i> Nuevo plan lector
    </a>
  @endif
</div>

<!-- Filtros -->
<form id="filter-form" action="{{ route('planes-lectores.gestionar') }}" method="GET" class="mb-4">
  <div class="row mt-5 g-3 align-items-center">
    <div class="col-9 col-md-4">
      <div class="input-group input-group-merge bg-white">
        <input type="text" id="buscar_plan" name="buscar" class="form-control" placeholder="Busqueda..." value="{{ $buscar ?? '' }}">
        @if($buscar)
          <span id="borrarBusquedaPorPalabra" class="input-group-text cursor-pointer"><i class="ti ti-x"></i></span>
        @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
        @endif
      </div>
    </div>
    
    <div class="col-3 col-md-8 d-flex justify-content-end">
      <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-5 me-1" data-bs-toggle="offcanvas" data-bs-target="#modalBusquedaAvanzada">
        <i class="ti ti-filter"></i> <span class="d-none d-md-block">Filtros</span>
      </button>
    </div>

    <!-- Panel de Tags Activos -->
    <div class="filter-tags py-3 col-12 d-flex flex-wrap align-items-center gap-2">
      <span class="text-black me-3">{{ $planes->total() > 1 ? $planes->total().' Planes Lectores' : $planes->total().' Plan Lector' }}</span>
      @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
        @foreach($tagsBusqueda as $tag)
          <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
            <span class="align-middle">{{ $tag->label }}<i class="ti ti-x ms-1"></i> </span>
          </button>
        @endforeach
        @if($bandera == 1)
          <a href="{{ route('planes-lectores.gestionar') }}" class="btn btn-xs rounded-pill btn-secondary ps-2 pe-1">
            <span class="align-middle">Quitar todos los filtros <i class="ti ti-x ms-1"></i> </span>
          </a>
        @endif
      @endif
    </div>
  </div>

  <!-- Offcanvas Búsqueda Avanzada -->
  <div class="offcanvas offcanvas-end event-sidebar modalSelect2" tabindex="-1" id="modalBusquedaAvanzada" aria-labelledby="modalBusquedaAvanzadaLabel">
      <div class="offcanvas-header my-1 px-8">
          <h4 class="offcanvas-title fw-bold text-primary" id="modalBusquedaAvanzadaLabel">
            Filtros
          </h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body pt-6 px-8">
        <div class="row">

          <div class="col-12 mb-3">
            <label for="buscar_plan_offcanvas" class="form-label">Por palabra</label>
            <input id="buscar_plan_offcanvas" name="buscar_offcanvas" type="text" value="{{$buscar}}" class="form-control" placeholder="Buscar por título..." oninput="document.getElementById('buscar_plan').value = this.value">
          </div>

          <!-- Por categoria -->
          <div class="col-12 mb-3">
            <label for="categorias" class="form-label">Por categoria</label>
            <select id="categorias" name="categorias[]" class="select2 form-select" multiple>
              @foreach($categorias as $categoria)
              <option value="{{ $categoria->id }}" {{ in_array($categoria->id, $categoriasSeleccionadas) ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
              @endforeach
            </select> 
          </div>

          <!-- Por Estado -->
          <div class="col-12 mb-3">
            <label for="estado" class="form-label">Por Estado</label>
            <select id="estado" name="estado" class="form-select">
              <option value="" {{ (isset($estado) && $estado === '') || !isset($estado) ? 'selected' : '' }}>Todos</option>
              <option value="1" {{ isset($estado) && $estado === '1' ? 'selected' : '' }}>Activos</option>
              <option value="0" {{ isset($estado) && $estado === '0' ? 'selected' : '' }}>Inactivos</option>
            </select>
          </div>

        </div>
      </div>
      <div class="offcanvas-footer p-5 border-top border-2 px-8">
          <button type="submit" class="btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Filtrar</button>
          <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
      </div>
  </div>
</form>

@include('layouts.status-msn')

<!-- Listado de Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 g-4 mb-4">
  @forelse($planes as $plan)
  <div class="col">
    <div class="card h-100 shadow-sm border-0 overflow-hidden position-relative" style="border-radius: 15px;">
      
      <!-- Imagen -->
      <div class="card-img-top position-relative overflow-hidden" style="width: 100%; height: 0; padding-bottom: 75%; background-color: #f8f9fa;">
        @php
          $tieneImagen = $configuracion->version == 1 && $plan->imagen_url;
          $urlImagen = $tieneImagen 
              ? Storage::url($configuracion->ruta_almacenamiento.'/img/planes_lectores/'.basename($plan->imagen_url)) 
              : null;
        @endphp
        @if($tieneImagen)
          <img src="{{ $urlImagen }}" 
               alt="Imagen del plan lector" 
               class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; object-position: center;">
        @else
          @php
            $gradients = [
                'linear-gradient(135deg, #7367f0 0%, #a8a1f3 100%)', // Original Purple
                'linear-gradient(135deg, #28c76f 0%, #81ebb1 100%)', // Green
                'linear-gradient(135deg, #ea5455 0%, #feb692 100%)', // Red/Orange
                'linear-gradient(135deg, #00cfe8 0%, #7367f0 100%)', // Blue/Purple
                'linear-gradient(135deg, #ff9f43 0%, #ffc085 100%)', // Orange
                'linear-gradient(135deg, #4b4b4b 0%, #282828 100%)', // Dark
                'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)', // Deep Blue
                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', // Indigo
            ];
            $gradientIndex = $plan->id % count($gradients);
            $selectedGradient = $gradients[$gradientIndex];
          @endphp
          <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: {{ $selectedGradient }};">
            <i class="ti ti-book" style="font-size: 3rem;"></i>
          </div>
        @endif 
      </div>

      <!-- Contenido Footer -->
      <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div class="flex-fill d-flex flex-column">
            <div class="d-flex align-items-center justify-content-start gap-3 text-muted mb-2">
              @if($plan->estado)
              <span class="badge bg-label-success">Activo</span>
              @else
              <span class="badge bg-label-secondary">Inactivo</span>
              @endif

              @if($plan->calificacion)
              <div class="d-flex align-items-center gap-1 text-black">
                <i class="ti ti-star ti-sm text-warning"></i>
                <span style="font-size: 0.8rem;">{{ $plan->calificacion }}</span>
              </div>
              @endif
            </div>  

            <div class="mb-2">                            
              @if($plan->autor)
                <div class="">
                  <small class="text-black"><i class="ti ti-user me-1"></i> {{ $plan->autor->nombre(3) }}</small>
                </div>
              @endif
            </div> 

            <h5 class="card-title text-black mb-1 fw-semibold">{{ $plan->titulo }}</h5>       
            

            <!-- Categorías -->
            <div class="d-flex flex-wrap gap-1 mb-2 mt-auto">
              @forelse($plan->categorias as $categoria)
                <span class="badge rounded-pill bg-label-primary fw-light" style="font-size: 0.7rem;">{{ $categoria->nombre }}</span>
              @empty
                <span class="badge rounded-pill bg-label-info fw-light" style="font-size: 0.7rem;">Sin categoría</span>
              @endforelse
            </div>
          </div>

          <div class="dropdown zindex-2 p-1">
            <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
            <ul class="dropdown-menu dropdown-menu-end">


              @if($rolActivo->hasPermissionTo('planes_lectores.opcion_modificar_plan_lector'))
                <a class="dropdown-item" href="{{ route('planes-lectores.contenido', $plan->id) }}">
                  Contenido
                </a>
                <a class="dropdown-item" href="{{ route('planes-lectores.editar', $plan->id) }}">
                  Editar
                </a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('form-estado-{{ $plan->id }}').submit();">
                  {{ $plan->estado ? 'Desactivar' : 'Activar' }}
                </a>
                <form id="form-estado-{{ $plan->id }}" action="{{ route('planes-lectores.cambiar-estado', $plan->id) }}" method="POST" style="display: none;">
                  @csrf
                  @method('PATCH')
                </form>
              @endif

              @if($rolActivo->hasPermissionTo('planes_lectores.opcion_eliminar_plan_lector'))
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger delete-record" href="javascript:void(0);" 
                   data-id="{{ $plan->id }}" data-name="{{ $plan->titulo }}">
                  Eliminar
                </a>
                <form id="form-eliminar-{{ $plan->id }}" action="{{ route('planes-lectores.eliminar', $plan->id) }}" method="POST" style="display: none;">
                  @csrf
                  @method('DELETE')
                </form>
              @endif
            </ul>
          </div>
        </div>
        
    
      </div>
    </div>
  </div>
  @empty
  <div class="col-12 w-100 mt-5">
    <div class="text-center py-5">
      <i class="ti ti-book-off ti-lg text-black mb-3 d-block" style="font-size: 4rem;"></i>
      <h5 class="text-black">No se encontraron planes lectores.</h5>
    </div>
  </div>
  @endforelse
</div>

<!-- Paginación -->
<div class="d-flex justify-content-center mt-5">
  {{ $planes->appends(request()->input())->links() }}
</div>

@endsection
